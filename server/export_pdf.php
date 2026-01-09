<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config/koneksi.php';
include_once 'auth_check.php';
checkLogin();
if (!isAdmin()) {
    echo "Akses ditolak. Hanya admin yang dapat mengekspor data.";
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

$query = mysqli_query($conn, "SELECT u.username, IFNULL(SUM(l.skor_akhir),0) as total_skor, COUNT(l.id) as total_kuis, MAX(l.tanggal_main) as terakhir_main
    FROM users u
    LEFT JOIN leaderboard l ON u.id = l.user_id
    WHERE u.role = 'player'
    GROUP BY u.id
    ORDER BY total_skor DESC");

$html = '<h2 style="text-align:center;">Data Player CodeMaster</h2>';
$html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
$html .= '<thead><tr style="background:#f1f5f9;"><th>Rank</th><th>Username</th><th>Total Skor</th><th>Total Kuis</th><th>Terakhir Main</th></tr></thead><tbody>';
$rank = 1;
while ($data = mysqli_fetch_assoc($query)) {
    $html .= '<tr>';
    $html .= '<td>' . $rank . '</td>';
    $html .= '<td>' . htmlspecialchars($data['username']) . '</td>';
    $html .= '<td>' . $data['total_skor'] . '</td>';
    $html .= '<td>' . $data['total_kuis'] . '</td>';
    $html .= '<td>' . ($data['terakhir_main'] ?? '-') . '</td>';
    $html .= '</tr>';
    $rank++;
}
$html .= '</tbody></table>';

$mpdf = new \Mpdf\Mpdf();
$mpdf->SetTitle('Data Player CodeMaster');
$mpdf->WriteHTML($html);
$mpdf->Output('Data_Player.pdf', 'D');
exit();
