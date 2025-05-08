function createElem(type,attributes)
{
    var elem=document.createElement(type);
    for (var i in attributes)
    {elem.setAttribute(i,attributes[i]);}
    return elem;
}

async function deleteBoard(boarduid) {
  if (!confirm("Es-tu sûr de vouloir supprimer ce plateau ? Cette action est irréversible.")) {
    return;
  }

  const url = '/board/' + boarduid;

  try {
    const response = await fetch(url, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json'
      }
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Erreur lors de la suppression : ${errorText}`);
    }

    // Rediriger vers la liste des plateaux
    window.location.href = '/board';
  } catch (error) {
    alert("La suppression a échoué : " + error.message);
  }
}

async function sendPrompt(prompt) {
  const url = '/API/board/' + boarduid + '/mjprompt';
  const data = { prompt: prompt };

  try {
    // Afficher l'overlay de chargement
    document.getElementById('loading-overlay').style.display = 'flex';

    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    });

    if (!response.ok) {
      throw new Error(`Erreur HTTP ${response.status}`);
    }

    const result = await response.json();
    console.log('Prompt envoyé avec succès:', result);
  } catch (error) {
    console.error('Erreur lors de l’envoi du prompt :', error);
    alert("Erreur lors de l’envoi du prompt : " + error.message);
  } finally {
    // Masquer l'overlay de chargement
    document.getElementById('loading-overlay').style.display = 'none';
  }
}
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

async function fillTurnDiv(turnuid) {
  const urlapi = '/API/board/' + boarduid + '/turnMJ/' + turnuid;
  try {
    const response = await fetch(urlapi);
    if (!response.ok) throw new Error("Erreur de récupération du tour");

    const data = await response.json();
    const container = document.getElementById("div-gameturns-t" + turnuid);
    if (!container) return;

    // Nettoyer la div avant d’ajouter du contenu
    container.innerHTML = "";

    // Créer l’élément pour la narration du MJ
    const mjDesc = createElem("div",{"class":"mjglobalmessage"});
    mjDesc.textContent = "MJ (message global) : " + data.allAwnser;
    container.appendChild(mjDesc);

    // Narations personnalisées des joueurs
    const playersAnswers = data.personalisedAwnsers;
    for (const playerUid in playersAnswers) {
      
      const playerDiv = document.createElement("div");
      playerDiv.style.marginBottom = "0.5em";
      let playerCaption = "";
      if( playersByUid[ playerUid ]){
        playerCaption = playersByUid[ playerUid ];
      }else{
        playerCaption = playerUid 
      }
      
      playerDiv.textContent = `MJ - message à ${playerCaption} - : ${playersAnswers[playerUid]}`;
      container.appendChild(playerDiv);


      //réponses des joueurs:
      let currentplayeruid = playerUid;
      data.playersResponses.forEach(function (reponse) {
        if(reponse.playerUID == playerUid ){
          const playerResponse = createElem("div",{"class": "playerresponse"});
          playerResponse.textContent = reponse.player_response;
          container.appendChild(playerResponse );

          if(reponse.tested_skills.length > 0 ){
            const diceResults = createElem("div",{"class":"diceresults"});
            let plistcompetances = createElem("p",{});
            plistcompetances.innerText = "Compétances testées :" + reponse.tested_skills.toString();
            diceResults.appendChild(plistcompetances);
            
            let pdiceresult = createElem("p",{});
            let result = "Jets de dés: " + reponse.dices_scores.toString();
            if( reponse.dices_succes){
              result += ' succès';
            }else{
              result += ' échec';
            }
            if(reponse.dices_critical){
              result += ' critique!'
            }
            pdiceresult.innerText = result;
            diceResults.appendChild(pdiceresult);

            container.appendChild(diceResults);
          }

        }
      });
    }
    
  } catch (err) {
    console.error("Erreur dans fillTurnDiv :", err);
  }
}

async function placeTurnsDivs(){
  let urlapi = '/API/board/' + boarduid + '/turnslist';

  const response = await fetch(urlapi);
  if (!response.ok) throw new Error('Erreur de chargement');
  const rep = await response.json();

  const container = document.getElementById('gameturns');
  
  rep.turns.forEach((turnuid, index) => {
    
    if( document.getElementById("div-gameturns-t" + turnuid )){
      //already exists
    }else{
      let divturn = createElem("div",{"class": "turn-entry", "id": "div-gameturns-t" + turnuid });
      divturn.innerText = turnuid;
      container.appendChild( divturn );
      fillTurnDiv( turnuid );
    }
    if (index === rep.turns.length - 1) {
      var timeoutturndiv = setTimeout(fillTurnDiv,3000,turnuid);
    }
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

    const sendPromptButton = document.getElementById("butonSendPrompt");
    if (sendPromptButton) {
      sendPromptButton.addEventListener("click", function () {
        const promptText = document.getElementById("game-prompt").value.trim();
        sendPrompt(promptText);
      });
    }

    if (boarduid !== "") {
      async function fetchPlayers() {
        try {
          const response = await fetch(`/API/board/${boarduid}/players`);
          if (!response.ok) throw new Error('Erreur de chargement');
          const players = await response.json();
  
          const container = document.getElementById('players-list');
          
          players.forEach(player => {
            
            playersByUid[ player.uid ] = `${player.name} (${player.type})`;

            if( document.getElementById("div-listplayers-" + player.uid)){
              //already exists
              //divplayer = document.getElementById("div-listplayers-" + player.uid);

            }else{
              let divplayer = createElem("div",{"class": "player-entry", "id": "div-listplayers-" + player.uid});


              let divtitle = createElem("div", {"class":"player-header"} );
              divtitle.innerText = `${player.name} (${player.type} )`;
              divtitle.addEventListener('click', () => {
                document.getElementById("div-listplayers-details" + player.uid).style.display = (document.getElementById("div-listplayers-details" + player.uid).style.display === 'none') ? 'block' : 'none';
              });

              let divdetails = createElem("div",{"class":"player-details", "id":"div-listplayers-details" + player.uid });
              divdetails.style.display = 'none';
              divdetails.innerHTML = `
              <p><strong>Courage:</strong> ${player.courage}</p>
              <p><strong>Intelligence:</strong> ${player.intelligence}</p>
              <p><strong>Charisme:</strong> ${player.charisma}</p>
              <p><strong>Dextérité:</strong> ${player.dexterity}</p>
              <p><strong>Force:</strong> ${player.strength}</p>
              <p><strong>Équipement:</strong> <ul>${player.equipment.map(eq => `<li>${eq}</li>`).join('')}</ul></p>
              <p><strong>Traits spéciaux:</strong> ${player.specialFeatures || 'Aucun'}</p>
              <p><strong>Description:</strong> ${player.description}</p>
              `;
              divplayer.appendChild(divtitle);
              divplayer.appendChild(divdetails);
              container.appendChild(divplayer);
            }
          });
        } catch (err) {
          console.error('Erreur lors du chargement des joueurs:', err);
        }
      }
      fetchPlayers(); // Chargement initial
      setInterval(fetchPlayers, 3000); // Mise à jour toutes les 3 secondes
      setInterval(placeTurnsDivs,3000);
    }
});

let playersByUid = {};