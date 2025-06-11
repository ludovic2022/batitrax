<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Vérification authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Accès refusé";
    exit;
}

$account_id = intval($_SESSION['account_id']);
$conn = getConnection();
$stmt = $conn->prepare("SELECT logo FROM accounts WHERE id = ?");
$stmt->execute([$account_id]);
$logo = $stmt->fetchColumn();

if ($logo) {
    $file = __DIR__ . '/../' . $logo;
    if (file_exists($file)) unlink($file);
}

$stmt = $conn->prepare("UPDATE accounts SET logo = NULL WHERE id = ?");
$stmt->execute([$account_id]);

$_SESSION['success'] = "Logo supprimé";
header('Location: ../Batitrax/dashboard.php?view=personnalisation');
exit;
?>
