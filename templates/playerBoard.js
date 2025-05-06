const boarduid = "{{boarduid}}";
let boardversion = "";

function createElem(type,attributes)
{
    var elem=document.createElement(type);
    for (var i in attributes)
    {elem.setAttribute(i,attributes[i]);}
    return elem;
}


function submitAnwser(awnser, turnuid) {

  const endpoint = '/API/board/' + boarduid + '/turn/' + turnuid;
  

  document.getElementById('loading-overlay').style.display = 'flex';

  fetch(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ "message": awnser })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error("Échec de l'envoi de la réponse");
    }
    return response.json(); // ou .text() selon ce que l'API retourne
  })
  .then(data => {
    console.log("Réponse envoyée avec succès :", data);
    updateGame(); // Recharger les dialogues après l'envoi
  })
  .catch(error => {
    console.error("Erreur lors de l'envoi de la réponse :", error);
    alert("Une erreur est survenue lors de l'envoi de votre réponse.");
  });
}


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
      if(data.length == 0 ){
        const mjMessage = document.createElement('div');
        mjMessage.classList.add('mj-message');
        mjMessage.innerHTML = `<strong>MJ :</strong> La partie va bientot commencer, veuillez patienter.`;
        gamedialogsDiv.appendChild(mjMessage);
      }
      data.forEach((turn, index) => {
        const mjMessage = document.createElement('div');
        mjMessage.classList.add('mj-message');
        mjMessage.innerHTML = `<strong>MJ :</strong> ${turn.allAwnser}<br><em>${turn.personalisedAwnsers}</em>`;
        gamedialogsDiv.appendChild(mjMessage);

        if (turn.playersResponses.playerUID) {
          const playerMessage = document.createElement('div');
          playerMessage.classList.add('player-message');
          playerMessage.innerHTML = `<strong>Vous :</strong> ${turn.playersResponses.player_response}`;
          gamedialogsDiv.appendChild(playerMessage);

          if (Array.isArray(turn.playersResponses.tested_skills) && turn.playersResponses.tested_skills.length > 0) {
            const resultsMessage = document.createElement('div');
            resultsMessage.classList.add('dice-results');
      
            const skills = turn.playersResponses.tested_skills;
            const scores = turn.playersResponses.dices_scores || [];
            const success = turn.playersResponses.dices_succes ? "Oui" : "Non";
            const critical = turn.playersResponses.dices_critical ? "Oui" : "Non";
      
            let resultHtml = `<strong>Jet de compétences :</strong><br><ul>`;
            skills.forEach((skill, i) => {
              const score = scores[i] !== undefined ? scores[i] : "n/a";
              resultHtml += `<li>${skill} : ${score}</li>`;
            });
            resultHtml += `</ul>`;
            resultHtml += `<strong>Succès :</strong> ${success}<br>`;
            resultHtml += `<strong>Critique :</strong> ${critical}`;
            resultsMessage.innerHTML = resultHtml;
      
            gamedialogsDiv.appendChild(resultsMessage);
          }

        }

        // Si c'est le dernier tour
        if (index === data.length - 1) {
          let thetextarea = createElem("textarea",{"id":"playerresponsetextarea","placeholder":"Votre réponse...","rows":3});
          let theButton = createElem("button",{"type":"button","class":"submitButton"});
          theButton.textContent = 'Envoyer';
          theButton.addEventListener('click', () => { submitAnwser( document.getElementById("playerresponsetextarea").value,turn.turnuid ); });
          if (turn.closedTurn || turn.playersResponses.playerUID) {
            thetextarea.disabled = true;
            theButton .disabled = true;
          }
          gamedialogsDiv.appendChild(thetextarea);
          gamedialogsDiv.appendChild(theButton);

        }
        document.getElementById('loading-overlay').style.display = 'none';
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