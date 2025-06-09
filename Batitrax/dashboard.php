<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once '../api/config.php';
$conn = getConnection();

// Récupération de l'utilisateur courant
$stmt = $conn->prepare("SELECT id, role, account_id, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Paramètres d'URL
$selectedProject = $_GET['project_id'] ?? null;
$viewAccount     = $_GET['view_account'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Batitrax – Dashboard</title>
  <!-- Chemin absolu vers le CSS -->
  <link rel="stylesheet" href="/Batitrax/assets/style.css">
</head>
<body>
<header>
  <h2>Batitrax – <?= htmlspecialchars($user['email']) ?> (<?= htmlspecialchars($user['role']) ?>)</h2>
  <a class="logout" href="../api/auth.php?action=logout">Déconnexion</a>
</header>
<div class="container">
  <nav class="menu">
    <ul>
      <?php if ($user['role'] === 'superadmin'): ?>
        <li><a href="#" data-view="accounts" class="active">Comptes</a></li>
      <?php endif; ?>
      <?php if ($user['role'] !== 'superadmin'): ?>
        <li><a href="#" data-view="projects" class="active">Projets</a></li>
      <?php endif; ?>
      <li><a href="#" data-view="users">Utilisateurs</a></li>
      <li><a href="#" data-view="invoices">Factures</a></li>
      <?php if ($user['role'] === 'admin'): ?>
        <li><a href="#" data-view="chat">Chat</a></li>
      <?php endif; ?>
    </ul>
  </nav>
  <main class="content">
    <?php if ($user['role'] === 'superadmin'): ?>
    <section id="view-accounts" class="view active">
      <div class="card">
        <h3>Comptes</h3>
        <?php
        $accts = $conn->query("
          SELECT a.id, a.name, COUNT(u.id) AS user_count, a.price_per_user
          FROM accounts a
          LEFT JOIN users u ON u.account_id=a.id
          GROUP BY a.id
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table>
          <thead>
            <tr><th>Nom</th><th>Utilisateurs</th><th>Prix/u</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach ($accts as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['name']) ?></td>
              <td><?= intval($a['user_count']) ?></td>
              <td><?= number_format($a['price_per_user'],2) ?> €</td>
              <td>
                <a href="?view_account=<?= $a['id'] ?>" class="button">Voir</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <form method="post" action="../api/account.php?action=create_account" class="inline">
          <input type="text" name="name" placeholder="Nom compte" required>
          <input type="number" step="0.01" name="price_per_user" placeholder="Prix/u (€)" required>
          <button>Créer compte</button>
        </form>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($user['role'] !== 'superadmin'): ?>
    <section id="view-projects" class="view active">
      <div class="card">
        <h3>Projets</h3>
        <?php
        $pStmt = $conn->prepare("SELECT id,name FROM projects WHERE account_id = ?");
        $pStmt->execute([$user['account_id']]);
        $projects = $pStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <ul>
          <?php foreach ($projects as $p): ?>
            <li><?= htmlspecialchars($p['name']) ?></li>
          <?php endforeach; ?>
        </ul>
        <form method="post" action="../api/project.php?action=create_project" class="inline">
          <input type="text" name="name" placeholder="Nouveau projet" required>
          <button>Créer projet</button>
        </form>
      </div>
    </section>
    <?php endif; ?>

    <section id="view-users" class="view">
      <div class="card">
        <h3>Utilisateurs</h3>
        <?php
        if ($user['role']==='superadmin' && $viewAccount) {
          $uStmt = $conn->prepare("SELECT id,first_name,last_name,email,phone,role,account_id FROM users WHERE account_id = ?");
          $uStmt->execute([$viewAccount]);
        } else {
          $uStmt = $conn->prepare("SELECT id,first_name,last_name,email,phone,role,account_id FROM users WHERE account_id = ?");
          $uStmt->execute([$user['account_id']]);
        }
        $usrs = $uStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table>
          <thead>
            <tr><th>Prénom</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th>Compte</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach ($usrs as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['first_name']) ?></td>
              <td><?= htmlspecialchars($u['last_name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['phone']) ?></td>
              <td><?= htmlspecialchars($u['role']) ?></td>
              <td>
                <?= htmlspecialchars(
                      $conn
                        ->query("SELECT name FROM accounts WHERE id={$u['account_id']}")
                        ->fetchColumn()
                    ) ?>
              </td>
              <td>
                <form class="inline" method="post" action="../api/account.php?action=delete_user">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button>Suppr</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <form method="post" action="../api/account.php?action=create_user" class="inline">
          <input type="text"  name="first_name" placeholder="Prénom" required>
          <input type="text"  name="last_name"  placeholder="Nom"     required>
          <input type="text"  name="phone"      placeholder="Téléphone" required>
          <input type="email" name="email"      placeholder="Email"    required>
          <select name="role">
            <?php if ($user['role']==='superadmin'): ?>
              <option value="admin">Admin</option>
            <?php endif; ?>
            <option value="salarie">Salarié</option>
            <option value="externe">Externe</option>
            <option value="encadrant">Encadrant</option>
          </select>

          <?php if ($user['role']==='superadmin'): ?>
            <?php $accounts = $conn->query("SELECT id,name FROM accounts")->fetchAll(PDO::FETCH_ASSOC); ?>
            <select name="account_id" required>
              <?php foreach($accounts as $a): ?>
                <option value="<?= $a['id'] ?>" <?= ($viewAccount==$a['id'])?'selected':'' ?>>
                  <?= htmlspecialchars($a['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input type="hidden" name="account_id" value="<?= $user['account_id'] ?>">
          <?php endif; ?>

          <button>Ajouter</button>
        </form>
      </div>
    </section>

    <section id="view-invoices" class="view">
      <div class="card">
        <h3>Factures</h3>
        <?php $act = ($user['role']==='superadmin' ? $viewAccount : $user['account_id']); ?>
        <?php if ($user['role']==='superadmin'): ?>
          <form method="post" action="../api/invoice.php?action=generate" class="inline">
            <input type="hidden" name="account_id" value="<?= $act ?>">
            <button>Générer facture</button>
          </form>
        <?php endif; ?>

        <?php
        $base = __DIR__ . '/../invoices/' . $act . '/';
        if (is_dir($base)) {
          $years = array_filter(scandir($base), fn($d) => !in_array($d, ['.', '..']));
          foreach ($years as $yr) {
            echo "<h4>$yr</h4><ul>";
            $files = array_filter(scandir($base.$yr), fn($f) => substr($f,-4)=='.pdf');
            foreach ($files as $f) {
              $url = "../invoices/$act/$yr/$f";
              echo "<li><a href='$url' target='_blank'>$f</a></li>";
            }
            echo "</ul>";
          }
        } else {
          echo "<p>Aucune facture.</p>";
        }
        ?>
      </div>
    </section>

    <?php if ($user['role'] === 'admin'): ?>
    <section id="view-chat" class="view">
      <div class="card">
        <h3>Chat</h3>
        <?php if ($selectedProject): ?>
          <div class="messages">
            <?php
            $ms = $conn->prepare("
              SELECT m.content,m.created_at,u.email 
              FROM messages m 
              JOIN users u ON m.user_id=u.id 
              WHERE project_id=? 
              ORDER BY m.created_at
            ");
            $ms->execute([$selectedProject]);
            foreach ($ms->fetchAll(PDO::FETCH_ASSOC) as $m): ?>
              <div class="message <?= ($m['email']===$user['email'])?'self':'other' ?>">
                <strong><?= htmlspecialchars($m['email']) ?></strong><br>
                <?= nl2br(htmlspecialchars($m['content'])) ?><br>
                <small><?= htmlspecialchars($m['created_at']) ?></small>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="chat-input">
            <form method="post" action="../api/project.php?action=send_message&project_id=<?= $selectedProject ?>">
              <input name="content" placeholder="Votre message…" required>
              <button>Envoyer</button>
            </form>
          </div>
        <?php else: ?>
          <p>Sélectionnez un projet.</p>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>
  </main>
</div>

<script>
  document.querySelectorAll('.menu a').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      document.querySelectorAll('.menu a').forEach(x => x.classList.remove('active'));
      a.classList.add('active');
      document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
      document.getElementById('view-'+a.dataset.view).classList.add('active');
    });
  });
</script>
</body>
</html>
