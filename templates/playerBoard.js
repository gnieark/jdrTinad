const boarduid = "{{boarduid}}";
let boardversion = "";


function updateGame(){



}
  function checkBoardVersion() {
    fetch(`/API/board/${boarduid}/version-uid`)
      .then(response => {
        if (!response.ok) throw new Error("Erreur réseau");
        return response.json();
      })
      .then(data => {
        if (data["version-uid"] && data["version-uid"] !== boardversion) {
          boardversion = data["version-uid"];
          updateGame();
        }
      })
      .catch(error => {
        console.error("Erreur lors de la vérification de la version :", error);
      });
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

    setInterval(checkBoardVersion, 3000);

  });