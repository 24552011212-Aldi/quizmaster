<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Helper: parse HTML and count tags (excluding wrappers)
function get_tag_counts($html) {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $counts = [];
    $exclude = ['html','body','head'];
    $nodes = $doc->getElementsByTagName('*');
    foreach ($nodes as $node) {
        $tag = strtolower($node->nodeName);
        if (in_array($tag, $exclude)) continue;
        $counts[$tag] = ($counts[$tag] ?? 0) + 1;
    }
    return $counts;
}

// Helper: check tag has at least one element with non-empty text
function has_non_empty_text($doc, $tag) {
    $elements = $doc->getElementsByTagName($tag);
    foreach ($elements as $el) {
        $text = trim($el->textContent ?? '');
        if ($text !== '') return true;
    }
    return false;
}

// Structural validation: require all tags present in expected_output
function validate_structure($userHtml, $expectedHtml, &$details) {
    // If no expected sample is provided, do not auto-pass
    if (empty(trim($expectedHtml))) {
        $details = ['No expected structure defined for this lesson'];
        return false;
    }
    $userDoc = new DOMDocument();
    libxml_use_internal_errors(true);
    $userDoc->loadHTML($userHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $expectedCounts = get_tag_counts($expectedHtml);
    $userCounts = get_tag_counts($userHtml);

    $missing = [];
    foreach ($expectedCounts as $tag => $count) {
        $userCount = $userCounts[$tag] ?? 0;
        if ($userCount < $count) {
            $missing[] = "$tag (need $count, got $userCount)";
        }
    }

    $needsText = [];
    foreach (array_keys($expectedCounts) as $tag) {
        // Require non-empty text for common content tags
        if (in_array($tag, ['h1','h2','h3','h4','h5','h6','p','span','li'])) {
            if (!has_non_empty_text($userDoc, $tag)) {
                $needsText[] = "$tag needs non-empty text";
            }
        }
    }

    $details = [];
    if (!empty($missing)) $details[] = 'Missing tags: ' . implode(', ', $missing);
    if (!empty($needsText)) $details[] = 'Text required: ' . implode(', ', $needsText);

    return empty($missing) && empty($needsText);
}

// Validasi generic berbasis string (berlaku untuk semua bahasa)
function apply_generic_rules($code, $rules, &$details, &$appliedChecks) {
    if (!empty($rules['required_contains']) && is_array($rules['required_contains'])) {
        $appliedChecks++;
        $missing = [];
        foreach ($rules['required_contains'] as $str) {
            if (stripos($code, $str) === false) {
                $missing[] = $str;
            }
        }
        if (!empty($missing)) {
            $details[] = 'Required text not found: ' . implode(', ', $missing);
        }
    }

    if (!empty($rules['required_code']) && is_array($rules['required_code'])) {
        $appliedChecks++;
        $missing = [];
        foreach ($rules['required_code'] as $keyword) {
            // Case-insensitive search for keyword (word boundary)
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
            if (!preg_match($pattern, $code)) {
                $missing[] = $keyword;
            }
        }
        if (!empty($missing)) {
            $details[] = 'Missing required code: ' . implode(', ', $missing);
        }
    }

    if (!empty($rules['forbidden_contains']) && is_array($rules['forbidden_contains'])) {
        $appliedChecks++;
        $found = [];
        foreach ($rules['forbidden_contains'] as $str) {
            if (stripos($code, $str) !== false) {
                $found[] = $str;
            }
        }
        if (!empty($found)) {
            $details[] = 'Forbidden text present: ' . implode(', ', $found);
        }
    }

    if (!empty($rules['required_regex']) && is_array($rules['required_regex'])) {
        $appliedChecks++;
        $missing = [];
        foreach ($rules['required_regex'] as $pattern) {
            if (@preg_match($pattern, $code) !== 1) {
                $missing[] = $pattern;
            }
        }
        if (!empty($missing)) {
            $details[] = 'Patterns not matched: ' . implode(', ', $missing);
        }
    }

    if (!empty($rules['forbidden_regex']) && is_array($rules['forbidden_regex'])) {
        $appliedChecks++;
        $bad = [];
        foreach ($rules['forbidden_regex'] as $pattern) {
            if (@preg_match($pattern, $code) === 1) {
                $bad[] = $pattern;
            }
        }
        if (!empty($bad)) {
            $details[] = 'Forbidden patterns matched: ' . implode(', ', $bad);
        }
    }

    $length = strlen($code);
    $lines = max(1, substr_count($code, "\n") + 1);

    if (isset($rules['min_length'])) {
        $appliedChecks++;
        if ($length < intval($rules['min_length'])) {
            $details[] = 'Code too short. Min length: ' . intval($rules['min_length']);
        }
    }
    if (isset($rules['max_length'])) {
        $appliedChecks++;
        if ($length > intval($rules['max_length'])) {
            $details[] = 'Code too long. Max length: ' . intval($rules['max_length']);
        }
    }
    if (isset($rules['min_lines'])) {
        $appliedChecks++;
        if ($lines < intval($rules['min_lines'])) {
            $details[] = 'Too few lines. Min lines: ' . intval($rules['min_lines']);
        }
    }
    if (isset($rules['max_lines'])) {
        $appliedChecks++;
        if ($lines > intval($rules['max_lines'])) {
            $details[] = 'Too many lines. Max lines: ' . intval($rules['max_lines']);
        }
    }
}

// Language-aware validation entry point
function validate_with_rules($userCode, $rules, &$details) {
    $details = [];
    $language = strtolower($rules['language'] ?? 'html');
    $appliedChecks = 0;

    // Always run generic checks first
    apply_generic_rules($userCode, $rules, $details, $appliedChecks);

    if ($language === 'html') {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($userCode, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $userCounts = get_tag_counts($userCode);

        if (!empty($rules['required_tags']) && is_array($rules['required_tags'])) {
            $appliedChecks++;
            $missing = [];
            foreach ($rules['required_tags'] as $tag) {
                $count = $userCounts[$tag] ?? 0;
                if ($count === 0) {
                    $missing[] = $tag;
                }
            }
            if (!empty($missing)) {
                $details[] = 'Missing required tags: ' . implode(', ', $missing);
            }
        }

        if (!empty($rules['min_counts']) && is_array($rules['min_counts'])) {
            $appliedChecks++;
            $short = [];
            foreach ($rules['min_counts'] as $tag => $min) {
                $count = $userCounts[$tag] ?? 0;
                if ($count < intval($min)) {
                    $short[] = "$tag (need $min, got $count)";
                }
            }
            if (!empty($short)) {
                $details[] = 'Not enough elements: ' . implode(', ', $short);
            }
        }

        if (!empty($rules['require_non_empty_text_tags']) && is_array($rules['require_non_empty_text_tags'])) {
            $appliedChecks++;
            $empties = [];
            foreach ($rules['require_non_empty_text_tags'] as $tag) {
                if (!has_non_empty_text($doc, $tag)) {
                    $empties[] = $tag;
                }
            }
            if (!empty($empties)) {
                $details[] = 'Tags need text: ' . implode(', ', $empties);
            }
        }
    }

    if ($appliedChecks === 0) {
        $details[] = 'No validation rules configured for this lesson. Please add constraints to validation_rules.';
    }

    // For non-HTML languages, if only generic checks were applied and all passed, mark as correct
    if ($language !== 'html' && $appliedChecks > 0 && empty($details)) {
        return true;
    }

    return $appliedChecks > 0 && empty($details);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lesson_id']) && isset($_POST['code'])) {
    $lessonId = intval($_POST['lesson_id']);
    $userCode = $_POST['code'];
    $userId = $_SESSION['user_id'];

    // Get lesson row (includes optional validation_rules JSON)
    $stmt = $conn->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $lessonId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Lesson not found']);
        exit();
    }

    $lesson = $result->fetch_assoc();
    $expectedOutput = $lesson['expected_output'];
    $rulesJson = $lesson['validation_rules'] ?? null;

    $details = [];
    $isCorrect = false;
    if (!empty($rulesJson)) {
        $rules = json_decode($rulesJson, true);
        if (is_array($rules)) {
            $isCorrect = validate_with_rules($userCode, $rules, $details);
        } else {
            $details[] = 'Validation rules JSON invalid. Please fix validation_rules in database.';
        }
    } else if (!empty(trim($expectedOutput))) {
        // No rules provided, use expected_output as structure template
        $isCorrect = validate_structure($userCode, $expectedOutput, $details);
    } else {
        $details[] = 'No validation_rules or expected_output configured for this lesson.';
    }

    if ($isCorrect) {
        // Save progress to database (upsert)
        $checkStmt = $conn->prepare("SELECT id FROM progress WHERE user_id = ? AND lesson_id = ?");
        $checkStmt->bind_param("ii", $userId, $lessonId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            $insertStmt = $conn->prepare("INSERT INTO progress (user_id, lesson_id, completed, completed_at) VALUES (?, ?, 1, NOW())");
            $insertStmt->bind_param("ii", $userId, $lessonId);
            $insertStmt->execute();
            $insertStmt->close();
        } else {
            $updateStmt = $conn->prepare("UPDATE progress SET completed = 1, completed_at = NOW() WHERE user_id = ? AND lesson_id = ?");
            $updateStmt->bind_param("ii", $userId, $lessonId);
            $updateStmt->execute();
            $updateStmt->close();
        }

        $checkStmt->close();
    }

    echo json_encode([
        'success' => true,
        'correct' => $isCorrect,
        'message' => $isCorrect ? 'Correct! Well done!' : 'Not quite right. Try again!',
        'details' => $isCorrect ? [] : $details
    ]);

    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
}

mysqli_close($conn);
?>
