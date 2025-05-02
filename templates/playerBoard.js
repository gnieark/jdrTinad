const boarduid = "{{boarduid}}";
let boardversion = "";


function updateGame(){
  const endpointApi = '/API/board/' + boarduid + '/turns';
  const gamedialogsDiv = document.getElementById('gamedialogs');
  gamedialogsDiv.innerHTML = ''; // Vide les dialogues

  fetch(endpointApi)
    .then(response => {
      if (!response.ok) throw new Error("Erreur réseau");
      return response.json();
    })
    .then(data => {
      if (!Array.isArray(data)) throw new Error("Format de réponse inattendu");

      data.forEach((turn, index) => {
        const mjMessage = document.createElement('div');
        mjMessage.classList.add('mj-message');
        mjMessage.innerHTML = `<strong>MJ :</strong> ${turn.allAwnser}<br><em>${turn.personalisedAwnsers}</em>`;
        gamedialogsDiv.appendChild(mjMessage);

        if (turn.playerResponse) {
          const playerMessage = document.createElement('div');
          playerMessage.classList.add('player-message');
          playerMessage.innerHTML = `<strong>Vous :</strong> ${turn.playerResponse}`;
          gamedialogsDiv.appendChild(playerMessage);
        }

        // Si c'est le dernier tour
        if (index === data.length - 1) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/API/board/${boarduid}/answer`;

          const textarea = document.createElement('textarea');
          textarea.name = 'player_response';
          textarea.placeholder = 'Votre réponse...';
          textarea.rows = 3;

          const button = document.createElement('button');
          button.type = 'submit';
          button.textContent = 'Envoyer';

          if (turn.closedTurn) {
            textarea.disabled = true;
            button.disabled = true;
          }

          form.appendChild(textarea);
          form.appendChild(button);
          gamedialogsDiv.appendChild(form);
        }
      });
    })
    .catch(error => {
      console.error("Erreur lors de la mise à jour des dialogues :", error);
      gamedialogsDiv.innerHTML = '<p style="color:red;">Erreur lors du chargement des dialogues.</p>';
    });

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