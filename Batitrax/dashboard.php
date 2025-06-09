<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once '../api/config.php';
$conn = getConnection();

// Fetch current user
$stmt = $conn->prepare("SELECT id, role, account_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Parameters
$selectedProject = $_GET['project_id'] ?? null;
$viewAccount = $_GET['view_account'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Batitrax - Dashboard</title>
    <style>
        body{font-family:Arial,sans-serif;margin:0;background:#f9f9f9;}
        header{padding:1rem;background:#007bff;color:#fff;position:relative;}
        header a.logout{position:absolute;right:1rem;top:1rem;color:#fff;text-decoration:none;}
        .content{padding:2rem;}
        table{width:100%;border-collapse:collapse;margin-bottom:1.5rem;}
        th,td{padding:0.5rem;border:1px solid #ccc;text-align:left;}
        th{background:#007bff;color:#fff;}
        form.inline{display:inline-block;margin:0 0.5rem;}
        input,select,button{margin:0.25rem 0;padding:0.4rem;}
        .button{padding:0.3rem 0.6rem;background:#28a745;color:#fff;text-decoration:none;border-radius:4px;}
        .container{display:grid;grid-template-columns:1fr 3fr;height:calc(100vh - 56px);}
        .sidebar{background:#fff;padding:1rem;border-right:1px solid #ddd;overflow-y:auto;}
        .chat{padding:1rem;overflow-y:auto;position:relative;height:calc(100vh - 56px);}
        .message{margin:0.5rem 0;padding:0.5rem;border-radius:8px;max-width:60%;}
        .self{background:#dcf8c6;margin-left:auto;}
        .other{background:#fff;margin-right:auto;}
        .chat-input{position:absolute;bottom:1rem;width:95%;}
    </style>
</head>
<body>
<header>
    <h2>Dashboard - <?=htmlspecialchars($_SESSION['email']);?> (<?=htmlspecialchars($user['role']);?>)</h2>
    <a class="logout" href="../api/auth.php?action=logout">Déconnexion</a>
</header>
<div class="content">

<?php if ($user['role'] === 'superadmin'): ?>

    <?php
    // Fetch accounts
    $accts = $conn->query("
        SELECT a.id, a.name, COUNT(u.id) AS user_count, a.price_per_user
        FROM accounts a
        LEFT JOIN users u ON u.account_id = a.id
        GROUP BY a.id
    ")->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <h3>Comptes</h3>
    <table>
        <thead><tr><th>ID</th><th>Nom</th><th>Utilisateurs</th><th>Prix/u</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($accts as $a): ?>
            <tr>
                <td><?=intval($a['id']);?></td>
                <td><?=htmlspecialchars($a['name']);?></td>
                <td><?=intval($a['user_count']);?></td>
                <td><?=number_format($a['price_per_user'],2,',',' ');?> €</td>
                <td>
                    <a class="button" href="?view_account=<?=intval($a['id']);?>">Voir users</a>
                    <form class="inline" method="post" action="../api/account.php?action=update_account_price">
                        <input type="hidden" name="account_id" value="<?=intval($a['id']);?>">
                        <input type="number" step="0.01" name="price_per_user" value="<?=htmlspecialchars($a['price_per_user']);?>" required>
                        <button>Maj prix</button>
                    </form>
                    <form class="inline" method="post" action="../api/account.php?action=delete_account" onsubmit="return confirm('Supprimer ce compte ?');">
                        <input type="hidden" name="account_id" value="<?=intval($a['id']);?>">
                        <button>Suppr</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    // Create new account form
    ?>
    <h3>Créer un compte</h3>
    <form method="post" action="../api/account.php?action=create_account">
        <input type="text" name="name" placeholder="Nom du compte" required>
        <input type="number" step="0.01" name="price_per_user" placeholder="Prix/utilisateur (€)" required>
        <button>Créer compte</button>
    </form>

    <?php if ($viewAccount): ?>
        <?php
        $vid = intval($viewAccount);
        $vStmt = $conn->prepare("SELECT name FROM accounts WHERE id = ?");
        $vStmt->execute([$vid]); $vname = $vStmt->fetchColumn();
        $uStmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, role, created_at FROM users WHERE account_id = ?");
        $uStmt->execute([$vid]); $vus = $uStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <h3>Utilisateurs du compte <?=htmlspecialchars($vname);?></h3>
        <table>
            <thead><tr><th>ID</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($vus as $u): ?>
                <tr>
                    <td><?=intval($u['id']);?></td>
                    <td><?=htmlspecialchars($u['first_name']);?></td>
                    <td><?=htmlspecialchars($u['last_name']);?></td>
                    <td><?=htmlspecialchars($u['email']);?></td>
                    <td><?=htmlspecialchars($u['phone']);?></td>
                    <td><?=htmlspecialchars($u['role']);?></td>
                    <td>
                        <?php if ($u['role'] !== 'superadmin'): ?>
                        <form class="inline" method="post" action="../api/account.php?action=delete_user">
                            <input type="hidden" name="user_id" value="<?=intval($u['id']);?>">
                            <button>Suppr</button>
                        </form>
                        <form class="inline" method="post" action="../api/account.php?action=change_password_user">
                            <input type="hidden" name="user_id" value="<?=intval($u['id']);?>">
                            <input type="password" name="new_password" placeholder="Nouveau MDP" required>
                            <button>MDP</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        // Add user form
        ?>
        <h3>Ajouter un utilisateur</h3>
        <form method="post" action="../api/account.php?action=create_user">
            <input type="text" name="first_name" placeholder="Prénom" required>
            <input type="text" name="last_name" placeholder="Nom" required>
            <input type="text" name="phone" placeholder="Téléphone" required>
            <input type="email" name="email" placeholder="Email" required>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="salarie">Salarié</option>
                <option value="externe">Externe</option>
                <option value="encadrant">Encadrant</option>
            </select>
            <input type="hidden" name="account_id" value="<?=$vid;?>">
            <button>Ajouter utilisateur</button>
        </form>

        <?php
        // Invoice generation and listing
        ?>
        <h3>Factures du compte <?=htmlspecialchars($vname);?></h3>
        <form method="post" action="../api/invoice.php?action=generate">
            <input type="hidden" name="account_id" value="<?=$vid;?>">
            <button>Générer facture</button>
        </form>
        <?php
        $base = __DIR__ . '/../invoices/' . $vid . '/';
        if (is_dir($base)) {
            $years = array_filter(scandir($base), fn($d)=>!in_array($d,['.','..']));
            foreach ($years as $yr) {
                echo "<h4>$yr</h4><ul>";
                $files = array_filter(scandir($base.$yr), fn($f)=>substr($f,-4)=='.pdf');
                foreach ($files as $f) {
                    $url = "../invoices/$vid/$yr/$f";
                    echo "<li><a href='$url' target='_blank'>$f</a></li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p>Aucune facture.</p>";
        }
        ?>
    <?php endif; ?>

<?php elseif ($user['role'] === 'admin'): ?>

    <div class="container">
        <div class="sidebar">
            <!-- projects, users, add user, invoices as before -->
            <h3>Projets</h3>
            <?php
            $pStmt = $conn->prepare("SELECT id,name FROM projects WHERE account_id = ?");
            $pStmt->execute([$user['account_id']]);
            $projects = $pStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <ul>
                <?php foreach ($projects as $p): ?>
                    <li><a href="?project_id=<?=intval($p['id']);?>" class="<?=( $selectedProject==$p['id'])?'selected':'';?>"><?=htmlspecialchars($p['name']);?></a></li>
                <?php endforeach; ?>
            </ul>
            <form method="post" action="../api/project.php?action=create_project">
                <input type="text" name="name" placeholder="Nouveau projet" required>
                <button>Créer projet</button>
            </form>
            <h3>Utilisateurs</h3>
            <?php
            $uStmt = $conn->prepare("SELECT id,first_name,last_name,email,phone,role FROM users WHERE account_id = ?");
            $uStmt->execute([$user['account_id']]);
            $usrs = $uStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <table>
                <thead><tr><th>Prénom</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($usrs as $u): ?>
                    <tr>
                        <td><?=htmlspecialchars($u['first_name']);?></td>
                        <td><?=htmlspecialchars($u['last_name']);?></td>
                        <td><?=htmlspecialchars($u['email']);?></td>
                        <td><?=htmlspecialchars($u['phone']);?></td>
                        <td><?=htmlspecialchars($u['role']);?></td>
                        <td>
                            <form class="inline" method="post" action="../api/account.php?action=delete_user">
                                <input type="hidden" name="user_id" value="<?=intval($u['id']);?>">
                                <button>Suppr</button>
                            </form>
                            <form class="inline" method="post" action="../api/account.php?action=update_user">
                                <input type="hidden" name="user_id" value="<?=intval($u['id']);?>">
                                <input type="text" name="first_name" value="<?=htmlspecialchars($u['first_name']);?>" required>
                                <input type="text" name="last_name" value="<?=htmlspecialchars($u['last_name']);?>" required>
                                <input type="text" name="phone" value="<?=htmlspecialchars($u['phone']);?>" required>
                                <input type="email" name="email" value="<?=htmlspecialchars($u['email']);?>" required>
                                <select name="role">
                                    <option value="salarie" <?=($u['role']=='salarie')?'selected':'';?>>Salarié</option>
                                    <option value="externe" <?=($u['role']=='externe')?'selected':'';?>>Externe</option>
                                    <option value="encadrant" <?=($u['role']=='encadrant')?'selected':'';?>>Encadrant</option>
                                </select>
                                <button>Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <h4>Ajouter un utilisateur</h4>
            <form method="post" action="../api/account.php?action=create_user">
                <input type="text" name="first_name" placeholder="Prénom" required>
                <input type="text" name="last_name" placeholder="Nom" required>
                <input type="text" name="phone" placeholder="Téléphone" required>
                <input type="email" name="email" placeholder="Email" required>
                <select name="role">
                    <option value="salarie">Salarié</option>
                    <option value="externe">Externe</option>
                    <option value="encadrant">Encadrant</option>
                </select>
                <button>Ajouter</button>
            </form>

            <h3>Factures</h3>
            <?php
            $base = __DIR__ . '/../invoices/' . $user['account_id'] . '/';
            if(is_dir($base)) {
                $years = array_filter(scandir($base), fn($d)=>!in_array($d,['.','..']));
                foreach ($years as $yr) {
                    echo "<h4>$yr</h4><ul>";
                    $files = array_filter(scandir($base.$yr), fn($f)=>substr($f,-4)=='.pdf');
                    foreach ($files as $f) {
                        $url = "../invoices/{$user['account_id']}/{$yr}/{$f}";
                        echo "<li><a href='$url' target='_blank'>$f</a></li>";
                    }
                    echo "</ul>";
                }
            } else {
                echo "<p>Aucune facture.</p>";
            }
            ?>
        </div>
        <div class="chat">
            <?php if ($selectedProject): 
                $ms = $conn->prepare("SELECT m.content,m.created_at,u.email FROM messages m JOIN users u ON m.user_id=u.id WHERE project_id=? ORDER BY m.created_at");
                $ms->execute([$selectedProject]); $msgs = $ms->fetchAll(PDO::FETCH_ASSOC);
                foreach ($msgs as $m): ?>
                <div class="message <?=($m['email']==$_SESSION['email'])?'self':'other';?>">
                    <strong><?=htmlspecialchars($m['email']);?></strong><br>
                    <?=nl2br(htmlspecialchars($m['content']));?><br>
                    <small><?=htmlspecialchars($m['created_at']);?></small>
                </div>
                <?php endforeach; ?>
                <div class="chat-input">
                    <form method="post" action="../api/project.php?action=send_message&project_id=<?=intval($selectedProject);?>">
                        <input type="text" name="content" placeholder="Votre message..." required>
                        <button>Envoyer</button>
                    </form>
                </div>
            <?php else: ?>
                <p>Sélectionnez un projet.</p>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

</div>
</body>
</html>