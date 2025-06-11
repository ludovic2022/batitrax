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

// Méthode POST uniquement
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Batitrax/dashboard.php?view=personnalisation');
    exit;
}

$account_id = intval($_SESSION['account_id']);
if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "Fichier manquant ou erreur d'upload";
    header('Location: ../Batitrax/dashboard.php?view=personnalisation');
    exit;
}

$file = $_FILES['logo'];
$allowed_types = ['image/png', 'image/jpeg'];
$max_size = 100 * 1024;

$type = mime_content_type($file['tmp_name']);
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($type, $allowed_types) || !in_array($ext, ['png', 'jpg', 'jpeg'])) {
    $_SESSION['error'] = "Format invalide (PNG/JPG requis)";
    header('Location: ../Batitrax/dashboard.php?view=personnalisation');
    exit;
}
if ($file['size'] > $max_size) {
    $_SESSION['error'] = "Fichier trop volumineux (max 100ko)";
    header('Location: ../Batitrax/dashboard.php?view=personnalisation');
    exit;
}

$upload_dir = __DIR__ . '/../uploads/logos/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$filename = 'logo_' . $account_id . '.' . $ext;
$filepath = $upload_dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    $_SESSION['error'] = "Erreur lors de l'upload";
    header('Location: ../Batitrax/dashboard.php?view=personnalisation');
    exit;
}

$relative_path = 'uploads/logos/' . $filename;

// Mise à jour en base
$conn = getConnection();
$stmt = $conn->prepare("UPDATE accounts SET logo = ? WHERE id = ?");
$stmt->execute([$relative_path, $account_id]);

$_SESSION['success'] = "Logo mis à jour";
header('Location: ../Batitrax/dashboard.php?view=personnalisation');
exit;
?>
