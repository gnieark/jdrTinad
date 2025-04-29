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
});