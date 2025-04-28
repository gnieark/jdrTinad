function createLinkAndQR(urlPart) {
  const container = document.getElementById('linkandqrcontainer');
  const fullUrl = window.location.protocol + '//' + window.location.host + '/' + urlPart.trim();
  
  container.innerHTML = `
    <div class="qr-container">
      <a href="${fullUrl}" target="_blank">${fullUrl}</a>
      <div class="qr-code" id="qr-code"></div>
      <button id="fullscreen-btn" style="margin-top:1em; padding:0.5em 1em; background:#333; color:white; border:none; border-radius:5px; cursor:pointer;">Plein écran</button>
    </div>
  `;

  // Générer le QR code
  if (window.QRious) {
    const canvas = document.createElement('canvas');
    const qr = new QRious({
      element: canvas,
      value: fullUrl,
      size: 200,
    });
    document.getElementById('qr-code').appendChild(canvas);

    // Ajouter la fonction plein écran
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    fullscreenBtn.addEventListener('click', function() {
      if (canvas.requestFullscreen) {
        canvas.requestFullscreen();
      } else if (canvas.webkitRequestFullscreen) { // Safari
        canvas.webkitRequestFullscreen();
      } else if (canvas.msRequestFullscreen) { // IE11
        canvas.msRequestFullscreen();
      } else {
        alert('Le plein écran n’est pas supporté sur ce navigateur.');
      }
    });
  } else {
    document.getElementById('qr-code').innerText = 'QR library not loaded';
  }
}

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