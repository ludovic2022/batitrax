<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once '../api/config.php';
$conn = getConnection();
$stmt = $conn->prepare("SELECT role, account_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Batitrax - Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f9f9f9; }
        header { padding: 1rem; background-color: #007bff; color: white; position: relative; }
        header a.logout { position: absolute; right: 1rem; top: 1rem; color: white; text-decoration: none; }
        .container { display: grid; grid-template-columns: 1fr 3fr; height: calc(100vh - 56px); }
        .sidebar { background-color: #ffffff; padding: 1rem; border-right: 1px solid #ddd; overflow-y: auto; }
        .chat { padding: 1rem; overflow-y: auto; position: relative; }
        .project-list ul { list-style: none; padding: 0; }
        .project-list li { margin: 0.5rem 0; }
        .project-list a { text-decoration: none; color: #007bff; }
        .project-list a.selected { font-weight: bold; }
        .message { margin: 0.5rem 0; padding: 0.5rem; border-radius: 8px; max-width: 60%; }
        .message.self { background-color: #dcf8c6; margin-left: auto; }
        .message.other { background-color: #fff; margin-right: auto; }
        .chat-input { position: absolute; bottom: 1rem; width: 90%; }
        .chat-input input { width: 80%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        .chat-input button { padding: 0.5rem 1rem; border: none; border-radius: 4px; background-color: #007bff; color: white; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <h2>Dashboard - <?=htmlspecialchars($_SESSION['email']);?> (<?=htmlspecialchars($user['role']);?>)</h2>
        <a class="logout" href="../api/auth.php?action=logout">Déconnexion</a>
    </header>
    <?php if($user['role']=='superadmin'): ?>
        <?php
        $accounts = $conn->query("SELECT id, name FROM accounts")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="sidebar" style="width:100%; padding:2rem;">
            <h3>Créer un compte</h3>
            <form method="post" action="../api/account.php?action=create_account">
                <input type="text" name="name" placeholder="Nom du compte" required>
                <button>Créer compte</button>
            </form>
            <h3>Ajouter un utilisateur</h3>
            <form method="post" action="../api/account.php?action=create_user">
                <label>Compte:</label>
                <select name="account_id" required>
                    <?php foreach($accounts as $acct): ?>
                        <option value="<?=intval($acct['id']);?>"><?=htmlspecialchars($acct['name']);?></option>
                    <?php endforeach; ?>
                </select><br>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="salarie">Salarié</option>
                    <option value="externe">Externe</option>
                    <option value="encadrant">Encadrant</option>
                </select>
                <button>Ajouter utilisateur</button>
            </form>
            <h3>Comptes</h3>
            <table border="1" cellpadding="5" cellspacing="0">
                <tr><th>ID</th><th>Nom</th><th>Actions</th></tr>
                <?php foreach($accounts as $acct): ?>
                <tr>
                    <td><?=intval($acct['id']);?></td>
                    <td><?=htmlspecialchars($acct['name']);?></td>
                    <td>
                        <form style="display:inline" method="post" action="../api/account.php?action=delete_account">
                            <input type="hidden" name="account_id" value="<?=intval($acct['id']);?>">
                            <button>Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <h3>Membres</h3>
            <table border="1" cellpadding="5" cellspacing="0">
                <tr><th>ID</th><th>Email</th><th>Rôle</th><th>Compte</th><th>Actions</th></tr>
                <?php
                $members = $conn->query("SELECT u.id, u.email, u.role, a.name AS acct FROM users u LEFT JOIN accounts a ON u.account_id=a.id")->fetchAll(PDO::FETCH_ASSOC);
                foreach($members as $m):
                ?>
                <tr>
                    <td><?=intval($m['id']);?></td>
                    <td><?=htmlspecialchars($m['email']);?></td>
                    <td><?=htmlspecialchars($m['role']);?></td>
                    <td><?=htmlspecialchars($m['acct']?:'-');?></td>
                    <td>
                        <?php if($m['role']!='superadmin'): ?>
                        <form style="display:inline" method="post" action="../api/account.php?action=delete_user">
                            <input type="hidden" name="user_id" value="<?=intval($m['id']);?>">
                            <button>Supprimer</button>
                        </form>
                        <form style="display:inline" method="post" action="../api/account.php?action=change_password_user">
                            <input type="hidden" name="user_id" value="<?=intval($m['id']);?>">
                            <input type="password" name="new_password" placeholder="Nouveau MDP" required>
                            <button>Changer MDP</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php else: ?>
        <!-- Reste de la vue admin/utilisateur inchangée -->
    <?php endif; ?>
</body>
</html>