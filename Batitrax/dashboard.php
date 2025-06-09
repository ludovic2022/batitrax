<?php
define('BATITRAX', true);
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once '../api/config.php';
$conn = getConnection();

// Récupération user
$stmt = $conn->prepare("SELECT id, role, account_id, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vue par URL ou par défaut
$view = $_GET['view'] 
      ?? ($user['role']==='superadmin' ? 'accounts' : 'projects');
$selectedProject = $_GET['project_id']  ?? null;
$viewAccount     = $_GET['view_account'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Batitrax – Dashboard</title>
  <link rel="stylesheet" href="/Batitrax/assets/style.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
<header>
  <h2>Batitrax – <?=htmlspecialchars($user['email'])?> (<?=htmlspecialchars($user['role'])?>)</h2>
  <a class="logout" href="../api/auth.php?action=logout">Déconnexion</a>
</header>
<div class="container">
  <?php include __DIR__ . '/inc/menu.php'; ?>
  <main class="content">
    <?php 
      include __DIR__ . '/inc/view_accounts.php';
      include __DIR__ . '/inc/view_projects.php';
      include __DIR__ . '/inc/view_users.php';
      include __DIR__ . '/inc/view_invoices.php';
      if ($user['role'] === 'admin') {
        include __DIR__ . '/inc/view_chat.php';
      }
    ?>
  </main>
</div>

<script>
// Optional: maintain active class on click without reload
document.querySelectorAll('.menu a').forEach(a=>{
  a.addEventListener('click', e=>{
    // no preventDefault: let the link navigate and reload
  });
});
</script>
</body>
</html>
