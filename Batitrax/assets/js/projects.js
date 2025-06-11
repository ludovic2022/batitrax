// Définit ou remet à zéro votre formulaire de création
function resetCreate() {
  const form = document.getElementById('createProjectForm');
  if (form) form.reset();
  // Réinitialiser aussi les champs de carte si nécessaire…
}

// Initialise votre carte (Leaflet, Google Maps…)
function initMap() {
  // Exemple pseudo-code :
  // map = new Map(…);
}

// Bouton “Créer un projet” dans la vue Projects (page interne)
document.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('createProjectBtn');
  if (btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (typeof resetCreate === 'function') {
        resetCreate();
        const modal = document.getElementById('createProjectModal');
        modal.style.display = 'flex';
        setTimeout(initMap, 50);
      }
    });
  }
});
