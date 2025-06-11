<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p>Accès réservé aux administrateurs du compte.</p>";
    return;
}
$account_id = $_SESSION['account_id'] ?? null;
$logo = null;
if ($account_id) {
    require_once(__DIR__ . '/../../api/config.php');
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT logo FROM accounts WHERE id=?");
    $stmt->execute([$account_id]);
    $logo = $stmt->fetchColumn();
}
?>

<h2>Identité du compte</h2>

<div style="margin:1em 0;">
    <?php if ($logo): ?>
        <img src="/<?php echo htmlspecialchars($logo); ?>" alt="Logo" style="max-width:120px;max-height:60px;">
    <?php else: ?>
        <span>Aucun logo enregistré</span>
    <?php endif; ?>
</div>

<form method="post" enctype="multipart/form-data" action="/api/upload_logo.php" style="margin:1em 0;">
    <label>Logo (PNG/JPG, max 100ko) :</label>
    <input type="file" name="logo" accept=".png,.jpg,.jpeg" required>
    <button type="submit">Uploader</button>
</form>
