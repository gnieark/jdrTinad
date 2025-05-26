document.addEventListener('DOMContentLoaded', function () {

  document.getElementById('characterform').addEventListener('submit', function (e) {
    const name = document.getElementById('name').value.trim();
    const race = document.getElementById('race').value;
    if (!name || !race) {
      alert('Merci de remplir au moins le nom et le type/race du personnage.');
      e.preventDefault();
    }
  });

  const form = document.getElementById('characterform');
  const submitButton = form.querySelector('button[type="submit"]');
  form.addEventListener('submit', function(event) {
      document.getElementById('loading-overlay').style.display = 'flex';
      submitButton.disabled = true;
      submitButton.textContent = 'Chargement...';
  });

  const jobsavailabled = {
    "humain": [
      "guerrier", "gladiateur", "ninja", "assassin", "voleur", "pretre",
      "mage", "sorcier", "paladin", "ranger", "menestrel", "pirate", "marchand", "bourgeois"
    ],
    "barbare": [
      "guerrier", "gladiateur", "assassin", "pirate", "paladin"
    ],
    "nain": [
      "guerrier", "gladiateur", "paladin", "ranger", "menestrel", "pirate", "marchand", "bourgeois"
    ],
    "haut-elfe": [
      "guerrier", "gladiateur", "voleur", "pretre", "mage", "sorcier",
      "paladin", "ranger", "menestrel", "pirate", "marchand", "bourgeois"
    ],
    "demi-elfe": [
      "guerrier", "gladiateur", "voleur", "pretre", "mage", "sorcier",
      "paladin", "ranger", "menestrel", "pirate", "marchand", "bourgeois"
    ],
    "elfe-sylvain": [
      "guerrier", "gladiateur", "voleur", "ranger", "menestrel", "marchand", "bourgeois"
      // Pas de assassin, ninja, pirate (trop sombres)
      // Pas de prêtre/mage/sorcier (trop complexe/intellectuel)
    ],
    "elfe-noir": [
      "guerrier", "gladiateur", "ninja", "assassin", "voleur", "pretre", "mage", "sorcier",
      "ranger", "pirate"
      // Rejeté : menestrel, marchand, bourgeois, paladin (trop « gentils » ou dociles)
    ],
    "orque": [
      "guerrier", "gladiateur", "assassin", "pirate"
    ],
    "demi-orque": [
      "guerrier", "gladiateur", "assassin", "paladin", "pirate", "ranger"
    ],
    "gobelin": [
      "guerrier", "voleur", "pirate"
    ],
    "ogre": [
      "guerrier", "gladiateur", "pirate"
    ],
    "semi-homme": [
      "guerrier", "voleur", "pretre", "ranger", "menestrel", "pirate", "marchand", "bourgeois"
    ],
    "gnome-des-forets-du-nord": [
      "guerrier", "voleur", "ranger", "menestrel", "marchand", "bourgeois"
    ]
  };

  const origineDescriptions = {{origineDescriptions}};
  
  const raceSelect = document.getElementById('race');
  const jobSelect = document.getElementById('job');

  raceSelect.addEventListener('change', function () {
    const selectedRace = raceSelect.value;
    const availableJobs = jobsavailabled[selectedRace] || [];

    // Vider les anciennes options
    jobSelect.innerHTML = '<option value="">-- Choisir --</option>';

    // Ajouter les nouvelles options
    availableJobs.forEach(function (job) {
      const option = document.createElement('option');
      option.value = job;
      option.textContent = job;
      jobSelect.appendChild(option);
    });

    // Désactiver le select si aucune race choisie
    jobSelect.disabled = availableJobs.length === 0;



    const descriptionZone = document.getElementById('racedescription');
    descriptionZone.innerHTML = '';
    
    if (origineDescriptions[selectedRace]) {
      const fullHTML = origineDescriptions[selectedRace];
    
      // On crée un élément temporaire pour extraire le premier paragraphe
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = fullHTML;
      const firstParagraph = tempDiv.querySelector('p');
      const preview = firstParagraph ? firstParagraph.outerHTML : fullHTML.substring(0, 300) + '...';
    
      // Lien pour afficher la suite
      const moreLink = document.createElement('a');
      moreLink.href = '#';
      moreLink.textContent = ' [Voir plus]';
      moreLink.style.cursor = 'pointer';
      moreLink.addEventListener('click', function (e) {
        e.preventDefault();
        descriptionZone.innerHTML = fullHTML;
      });
    
      // Affichage de l'extrait avec lien
      const container = document.createElement('div');
      container.innerHTML = preview;
      container.appendChild(moreLink);
      descriptionZone.appendChild(container);
    }

  });

  // Initialement désactivé
  jobSelect.disabled = true;
  

});