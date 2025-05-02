document.addEventListener('DOMContentLoaded', function () {
    // Affichage conditionnel de l'URL personnalisée
    const urlYes = document.getElementById('url-yes');
    const urlNo = document.getElementById('url-no');
    const urlField = document.getElementById('custom-url-field');
  
    function toggleUrlField() {
      urlField.style.display = urlYes.checked ? 'block' : 'none';
    }
  
    urlYes.addEventListener('change', toggleUrlField);
    urlNo.addEventListener('change', toggleUrlField);
    toggleUrlField(); // Initialisation

});