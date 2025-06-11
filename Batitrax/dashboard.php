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
$view = $_GET['view'] ?? ($user['role']==='superadmin' ? 'accounts' : 'projects');
$selectedProject = $_GET['project_id'] ?? null;
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Batitrax</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" />
    <script src="assets/js/projects.js"></script>
</head>
<body>
<?php include __DIR__ . '/inc/create_project_modal.php'; ?>
  <div class="container">
    <?php include __DIR__ . '/inc/menu.php'; ?>

    <div class="page-wrapper">
      <header>
        <?php
        // Chargement du logo si disponible
        $logo = null;
        if (!empty($user['account_id'])) {
            $stmtLogo = $conn->prepare("SELECT logo FROM accounts WHERE id = ?");
            $stmtLogo->execute([$user['account_id']]);
            $logo = $stmtLogo->fetchColumn();
        }
        ?>
        <?php if ($logo): ?>
          <img src="/<?= htmlspecialchars($logo) ?>" alt="Logo" style="max-height:60px;max-width:120px;">
        <?php else: ?>
          <h2>Batitrax – <?= htmlspecialchars($user['email']) ?> (<?= htmlspecialchars($user['role']) ?>)</h2>
        <?php endif; ?>
        <a class="logout" href="../api/auth.php?action=logout">Déconnexion</a>
      </header>
  <div class="content-layout">
    <div class="projects-col">
      <?php include __DIR__ . '/inc/sidebar_projects.php'; ?>
    </div>
    <main class="content">
        <?php
          include __DIR__ . '/inc/view_accounts.php';
          include __DIR__ . '/inc/view_projects.php';
          include __DIR__ . '/inc/view_create_project.php';
          include __DIR__ . '/inc/view_users.php';
          include __DIR__ . '/inc/view_invoices.php';
          if ($user['role'] === 'admin') {
            include __DIR__ . '/inc/view_chat.php';
          }
          if ($view === 'personnalisation' && $user['role'] === 'admin') {
            include __DIR__ . '/inc/view_personnalisation.php';
          }
        ?>
      </main>
  </div>
    </div>
  </div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
  var params = new URLSearchParams(window.location.search);
  if (params.get('view') === 'projects' && params.get('openModal') === '1') {
    var modal = document.getElementById('createProjectModal');
    if (modal && typeof resetCreate === 'function') {
      resetCreate();
      modal.style.display = 'flex';
      setTimeout(initMap, 50);
    }
  }
});
</script>

</body>
</html>
