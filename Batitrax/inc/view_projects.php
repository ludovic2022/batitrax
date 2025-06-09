<?php if(!defined('BATITRAX')) exit; ?>
<section id="view-projects" class="view <?= $view==='projects'?'active':'' ?>">
  <div class="card"><h3>Projets</h3>
    <?php
    $pStmt = $conn->prepare("SELECT id,name,emoji,address,lat,lng,manager_id FROM projects WHERE account_id=?");
    $pStmt->execute([$user['account_id']]);
    $projects = $pStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <ul>
      <?php foreach($projects as $p): ?>
      <li>
        <?=htmlspecialchars($p['emoji'])?> <?=htmlspecialchars($p['name'])?>
        <?php if($p['address']): ?>
          <br><small><?=htmlspecialchars($p['address'])?></small>
          <div id="map-<?=$p['id']?>" class="mini-map" style="height:120px;"></div>
          <script>
            (function(){
              var m = L.map('map-<?=$p['id']?>').setView([<?=$p['lat']?>,<?=$p['lng']?>],13);
              L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(m);
              L.marker([<?=$p['lat']?>,<?=$p['lng']?>]).addTo(m);
            })();
          </script>
        <?php endif; ?>
        <?php if($p['manager_id']): 
          $m = $conn->prepare("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=?");
          $m->execute([$p['manager_id']]);
          $mgr = $m->fetchColumn();
        ?>
          <br><em>Resp. : <?=htmlspecialchars($mgr)?></em>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <form method="post" action="../api/project.php?action=create_project" class="inline">
      <input name="emoji" placeholder="🏗️" style="width:3rem" required>
      <input name="name" placeholder="Nom projet" required>
      <input id="addr" name="address" placeholder="Adresse" required>
      <input type="hidden" id="lat" name="lat">
      <input type="hidden" id="lng" name="lng">
      <select name="manager_id" required>
        <?php 
        $uL = $conn->prepare("SELECT id, CONCAT(first_name,' ',last_name) AS nm FROM users WHERE account_id=?");
        $uL->execute([$user['account_id']]);
        foreach($uL as $u): ?>
          <option value="<?=$u['id']?>"><?=htmlspecialchars($u['nm'])?></option>
        <?php endforeach; ?>
      </select>
      <button>Créer projet</button>
    </form>
    <div id="map-create" style="height:200px;margin-top:1rem"></div>
    <script>
      var mc = L.map('map-create').setView([46.2,2.2],6);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mc);
      var mk;
      document.getElementById('addr').addEventListener('change',function(){
        fetch('https://nominatim.openstreetmap.org/search?format=json&q='+encodeURIComponent(this.value))
        .then(r=>r.json()).then(d=>{
          if(!d[0])return;
          var la=d[0].lat, lo=d[0].lon;
          document.getElementById('lat').value=la;
          document.getElementById('lng').value=lo;
          if(mk) mc.removeLayer(mk);
          mk=L.marker([la,lo]).addTo(mc);
          mc.setView([la,lo],13);
        });
      });
    </script>
  </div>
</section>
