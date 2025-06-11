<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Non authentifié"]);
    exit;
}

$account_id = intval($_SESSION['account_id']);
$conn = getConnection();
$stmt = $conn->prepare("SELECT logo FROM accounts WHERE id = ?");
$stmt->execute([$account_id]);
$logo = $stmt->fetchColumn();

echo json_encode(["logo" => $logo]);
?>
