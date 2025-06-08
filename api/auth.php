<?php
session_start();
require_once 'config.php';
$action = $_GET['action'] ?? '';
$conn = getConnection();
if ($action == 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        header('Location: ../Batitrax/dashboard.php');
        exit;
    } else {
        header('Location: ../Batitrax/index.php?error=Identifiants+invalides');
        exit;
    }
} elseif ($action == 'logout') {
    session_destroy();
    header('Location: ../Batitrax/index.php');
    exit;
} else {
    http_response_code(400);
    echo "Invalid action.";
}
?>