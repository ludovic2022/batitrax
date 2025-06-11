<?php
// Batitrax/inc/header.php
require_once __DIR__ . '/../../api/config.php';
session_start();

// Redirection vers login si non authentifié
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Reconstruction de l'utilisateur
$user = [
    'id'         => $_SESSION['user_id'],
    'email'      => $_SESSION['email'],
    'role'       => $_SESSION['role'],
    'account_id' => $_SESSION['account_id'],
];

// Récupération de la couleur de thème
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT theme_color FROM accounts WHERE id = ?");
$stmt->execute([$user['account_id']]);
$themeColor = $stmt->fetchColumn() ?: '#ffffff';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? '') ?></title>
    <style>
        :root { --theme-color: <?= htmlspecialchars($themeColor) ?>; }
        .btn {
            background-color: var(--theme-color);
            border: none;
            color: #fff;
        }
    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<?php include __DIR__ . '/menu.php'; ?>
