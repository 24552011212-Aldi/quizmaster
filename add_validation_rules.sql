-- Add JSON validation rules column and seed sample rules (idempotent)

-- Ensure column type is JSON
ALTER TABLE lessons
  MODIFY COLUMN validation_rules JSON NULL;

-- Shooting Star: requires one <h2> and one <p> with non-empty text
UPDATE lessons
SET validation_rules = JSON_OBJECT(
  'required_tags', JSON_ARRAY('h2','p'),
  'min_counts', JSON_OBJECT('h2', 1, 'p', 1),
  'require_non_empty_text_tags', JSON_ARRAY('h2','p')
)
WHERE title = 'Shooting Star';

-- Hello World: requires one <h1> with non-empty text
UPDATE lessons
SET validation_rules = JSON_OBJECT(
  'required_tags', JSON_ARRAY('h1'),
  'min_counts', JSON_OBJECT('h1', 1),
  'require_non_empty_text_tags', JSON_ARRAY('h1')
)
WHERE title = 'Hello World';

-- Profile Card: requires one <h3> and one <p> with non-empty text
UPDATE lessons
SET validation_rules = JSON_OBJECT(
  'required_tags', JSON_ARRAY('h3','p'),
  'min_counts', JSON_OBJECT('h3', 1, 'p', 1),
  'require_non_empty_text_tags', JSON_ARRAY('h3','p')
)
WHERE title = 'Profile Card';
