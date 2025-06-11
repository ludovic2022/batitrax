<?php if(!defined('BATITRAX')) exit; ?>
<?php
// Affichage uniquement pour la vue personnalisation et les admins
if ($view !== 'personnalisation' || $user['role'] !== 'admin') {
    return;
}
require_once __DIR__ . '/../../api/config.php';
$conn = getConnection();
$stmt = $conn->prepare("SELECT logo FROM accounts WHERE id = ?");
$stmt->execute([$user['account_id']]);
$logo = $stmt->fetchColumn();
?>
<section id="view-personnalisation" class="view active">
  <h2>Personnalisation</h2>
  <?php
    if (!empty($_SESSION['error'])) {
        echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
        unset($_SESSION['error']);
    }
    if (!empty($_SESSION['success'])) {
        echo '<p style="color:green;">' . htmlspecialchars($_SESSION['success']) . '</p>';
        unset($_SESSION['success']);
    }
  ?>
  <div style="margin:1em 0;">
    <?php if ($logo): ?>
      <img src="/<?php echo htmlspecialchars($logo); ?>" alt="Logo" style="max-width:120px;max-height:60px;"><br>
      <form method="post" action="../api/delete_logo.php" style="display:inline-block;margin-top:0.5em;">
        <button type="submit">Supprimer le logo</button>
      </form>
    <?php else: ?>
      <p>Aucun logo enregistré</p>
    <?php endif; ?>
  </div>
  <form method="post" enctype="multipart/form-data" action="../api/upload_logo.php">
    <label>Importer un logo (PNG/JPG, max 100ko):</label><br>
    <input type="file" name="logo" accept=".png,.jpg,.jpeg" required><br><br>
    <button type="submit">Uploader</button>
  </form>
</section>
