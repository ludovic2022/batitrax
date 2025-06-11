<?php if(!defined('BATITRAX')) exit; ?>
<section id="view-create-project" class="view <?= $view==='create_project'?'active':'' ?>">
  <div class="card">
    <h3>Nouveau projet</h3>
    <?php
      $uStmt = $conn->prepare("SELECT id, CONCAT(first_name,' ',last_name) AS fullname FROM users WHERE account_id=?");
      $uStmt->execute([$user['account_id']]);
      $users = $uStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <form id="create-page-form" method="POST" action="../api/project.php?action=create_project">
      <div class="emoji-picker">
        <span id="pageSelectedEmoji" class="emoji-selected">🏗️</span>
        <div id="pageEmojiGrid" class="emoji-grid-picker">
          <?php
            $emojis = ['🏗️','🛠️','🚧','🏠','🔨','📐','📦','📝','🚀','💼',
                       '🎯','✅','📊','🗂️','📁','⚙️','🧱','🏢','🏘️','🏚️',
                       '🏛️','🏨','🏩','🏣','🏤','⛪','🕌','🛕','🕍','🏯',
                       '🏟️','🎡','🎢','🎪','🏖️','🏜️','🏝️','🏞️','🌆','🌇'];
            foreach($emojis as $e){
              echo '<span class="emoji-item" data-emoji="'.$e.'">'.$e.'</span>';
            }
          ?>
        </div>
        <input type="hidden" name="emoji" value="🏗️" required>
      </div>
      <input type="text" name="name" placeholder="Nom du projet" required>
      <input type="text" name="address" placeholder="Adresse" required>
      <div id="create-page-map"></div>
      <select name="manager_id" required>
        <option value="">-- Responsable --</option>
        <?php foreach($users as $u): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['fullname']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" name="lat" value="0">
      <input type="hidden" name="lng" value="0">
      <div style="margin-top:0.5rem; display:flex; gap:0.5rem;">
        <button type="submit" class="save-btn">Créer</button>
        <a href="?view=projects" class="cancel-btn">Annuler</a>
      </div>
    </form>
  </div>
</section>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('create-page-form');
  if(!form) return;
  const addrInput = form.querySelector('[name="address"]');
  const latInput = form.querySelector('[name="lat"]');
  const lngInput = form.querySelector('[name="lng"]');
  const selectedEmoji = document.getElementById('pageSelectedEmoji');
  const emojiGrid = document.getElementById('pageEmojiGrid');
  const emojiHidden = form.querySelector('[name="emoji"]');
  const defaultLatLng = [48.8566,2.3522];
  let map=null, marker=null, debounceId=null;
  const initMap = () => {
    map = L.map('create-page-map').setView(defaultLatLng, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    marker = L.marker(defaultLatLng, {draggable:true}).addTo(map);
    marker.on('dragend', () => {
      const {lat,lng} = marker.getLatLng();
      latInput.value = lat;
      lngInput.value = lng;
      reverseGeocode(lat,lng);
    });
  };
  const geocode = q => {
    if(!q) return;
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1`, {
      headers:{'Accept':'application/json','User-Agent':'Batitrax/1.0'}
    }).then(r=>r.json()).then(d => {
      if(!d.length) return;
      const lat=parseFloat(d[0].lat), lon=parseFloat(d[0].lon);
      map.setView([lat,lon],17);
      marker.setLatLng([lat,lon]);
      latInput.value=lat; lngInput.value=lon;
    });
  };
  const reverseGeocode = (lat,lng) => {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
      headers:{'Accept':'application/json','User-Agent':'Batitrax/1.0'}
    }).then(r=>r.json()).then(d => { if(d && d.display_name){ addrInput.value=d.display_name; }});
  };
  selectedEmoji.onclick = () => { emojiGrid.style.display = emojiGrid.style.display === 'grid' ? 'none' : 'grid'; };
  emojiGrid.querySelectorAll('.emoji-item').forEach(item => {
    item.onclick = () => {
      selectedEmoji.textContent = item.dataset.emoji;
      emojiHidden.value = item.dataset.emoji;
      emojiGrid.style.display = 'none';
    };
  });
  document.addEventListener('click', e => { if(!e.target.closest('.emoji-picker')) emojiGrid.style.display = 'none'; });
  addrInput.addEventListener('input', ()=>{
    clearTimeout(debounceId);
    debounceId = setTimeout(()=> geocode(addrInput.value.trim()), 600);
  });
  initMap();
});
</script>
