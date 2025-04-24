document.getElementById('auth-form').addEventListener('submit', function (e) {
    const login = document.getElementById('login').value.trim();
    const password = document.getElementById('password').value.trim();
  
    if (!login || !password) {
      alert('Veuillez remplir tous les champs.');
      e.preventDefault();
    }
  });