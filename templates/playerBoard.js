document.getElementById('character-form').addEventListener('submit', function (e) {
    const name = document.getElementById('name').value.trim();
    const race = document.getElementById('race').value;
    if (!name || !race) {
      alert('Merci de remplir au moins le nom et le type/race du personnage.');
      e.preventDefault();
    }
  });