<?php
// … votre logique PHP pour récupérer $projects …
?>
<div class="projects-view">
  <?php if(in_array($user['role'], ['admin','encadrant'])): ?>
    <button id="createProjectBtn" class="save-btn">
      Créer un projet
    </button>
  <?php endif; ?>

  <!-- Reste de votre affichage de la liste… -->
</div>
