<?php if(!defined('BATITRAX')) exit; ?>
<section id="view-accounts" class="view <?= $view==='accounts'?'active':'' ?>">
  <div class="card"><h3>Comptes</h3>
    <?php
    $accts = $conn->query("
      SELECT a.id,a.name,COUNT(u.id) AS user_count,a.price_per_user 
      FROM accounts a 
      LEFT JOIN users u ON u.account_id=a.id 
      GROUP BY a.id
    ")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <table>
      <thead><tr><th>Nom</th><th>Users</th><th>Prix/u</th><th>Voir</th></tr></thead>
      <tbody>
      <?php foreach($accts as $a): ?>
        <tr>
          <td><?=htmlspecialchars($a['name'])?></td>
          <td><?=intval($a['user_count'])?></td>
          <td><?=number_format($a['price_per_user'],2)?>€</td>
          <td><a class="button" href="?view=accounts&view_account=<?=$a['id']?>">Voir</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <form method="post" action="../api/account.php?action=create_account" class="inline">
      <input name="name" placeholder="Nom du compte" required>
      <input name="price_per_user" type="number" step="0.01" placeholder="Prix/u (€)" required>
      <button>Créer compte</button>
    </form>
  </div>
</section>
