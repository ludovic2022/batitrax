<?php
session_start();
require_once 'config.php';
$conn = getConnection();
$action = $_REQUEST['action'] ?? '';

if ($action === 'create_project') {
    // Only admin or superadmin
    $userStmt = $conn->prepare("SELECT role, account_id FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $usr = $userStmt->fetch(PDO::FETCH_ASSOC);
    $account_id = $usr['role'] === 'admin' ? $usr['account_id'] : $_POST['account_id'];
    $name = $_POST['name'];
    $emoji = $_POST['emoji'] ?? null;
    $address = $_POST['address'] ?? null;
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;
    $manager_id = $_POST['manager_id'] ?? null;
    $stmt = $conn->prepare("
        INSERT INTO projects (account_id, name, emoji, address, lat, lng, manager_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$account_id, $name, $emoji, $address, $lat, $lng, $manager_id]);
    header('Location: ../Batitrax/dashboard.php');
    exit;
}
// existing other actions...
?>