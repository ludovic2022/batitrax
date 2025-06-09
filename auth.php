<?php
session_start();
require_once 'config.php';
$action = $_GET['action'] ?? '';
$conn = getConnection();

if ($action === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT id,email,password,role,account_id FROM users WHERE email=?");
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u && password_verify($password,$u['password'])) {
        $_SESSION['user_id']    = $u['id'];
        $_SESSION['email']      = $u['email'];
        $_SESSION['role']       = $u['role'];
        $_SESSION['account_id'] = $u['account_id'];
        header('Location: ../Batitrax/dashboard.php');
        exit;
    }
    header('Location: ../Batitrax/index.php?error=Identifiants+invalides');
    exit;
} elseif ($action === 'logout') {
    session_destroy();
    header('Location: ../Batitrax/index.php');
    exit;
} else {
    http_response_code(400);
    echo "Action invalide.";
}?>