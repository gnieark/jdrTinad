document.addEventListener('DOMContentLoaded', function () {
    // Accordéon
    document.querySelectorAll('.accordion-title').forEach(button => {
      button.addEventListener('click', () => {
        const content = button.nextElementSibling;
        const expanded = content.style.display === 'block';
        document.querySelectorAll('.accordion-content').forEach(c => c.style.display = 'none');
        content.style.display = expanded ? 'none' : 'block';
      });
    });
  
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