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

function getDivPrompt(){
      let divPromptNextRun = createElem("div",{"class":"promptnextrun"});

      let textareaGprompt = createElem("textarea",{"id":"game-prompt","name":"prompt","rows":"4"});
      divPromptNextRun.appendChild(textareaGprompt);

      let buttonNextPrompt = createElem("button",{"id":"butonSendPrompt", "type":"button", "placeholder":"Indications à donner pour orienter l'IA au tour suivant"});
      buttonNextPrompt.innerText = "Lancer le tour suivant."
      buttonNextPrompt .addEventListener("click", function () {
        const promptText = document.getElementById("game-prompt").value.trim();
        sendPrompt(promptText);
      });
      divPromptNextRun.appendChild(buttonNextPrompt);
      return divPromptNextRun;

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


async function refreshTurnList(){
  fetch(`/API/board/${boarduid}/turnslist`)
  .then(response => {
    if (!response.ok) throw new Error("Erreur réseau");
    return response.json();
  })
  .then(data => {

    turnsUids = data["turns"];
    if(turnsUids.length == 0 ){
      if(! document.getElementById("game-prompt") ){
        document.getElementById("gameboard").appendChild( getDivPrompt() );
      }
      return;
    }
    //turnsUids.at(-1)
    if( currentTurnUid == "" ){
      lastTurnUid = turnsUids.at(-1);
      displayTurn(lastTurnUid );

    }else if( turnsUids.at(-1)!== lastTurnUid ){
      lastTurnUid = turnsUids.at(-1);
      displayTurn(lastTurnUid );

    }
  })
  .catch(error => {
    console.error("Erreur lors de la vérification de la version :", error);
  });
}
async function checkPlayerResponse(boarduid,turnUid,playerUid){
 
        const response = await fetch(`/API/board/${boarduid}/turnMJ/${turnUid}/playerresponse/${playerUid}`);

        if (response.status === 200) {
            const reponse = await response.json();
            const container = document.getElementById("divplayeranswer" + playerUid);




          let preponse = createElem("p",{"class":"preponse"});
          preponse.innerText = reponse.player_response;
          container.appendChild(preponse);

          if( reponse.tested_skills.length > 0 ){
            let ptestedskills = createElem("p",{"class": "pcompetences"});
            ptestedskills.innerText = reponse.tested_skills.toString();
            container.appendChild(ptestedskills);
            
            let pbonusmalus = createElem("p",{"class": "pbonus"});
            pbonusmalus.innerText = reponse.dices_bonus.toString();
            container.appendChild(pbonusmalus);

            let pjet = createElem("p",{"class":"pjets" });
            pjet.innerText = reponse.dices_scores.toString();
            container.appendChild(pjet);

            let img = createElem("img", {"src": "/imgs/dé20.png", "class": "pdicelogo"});
            container.appendChild(img);

            let pdiceResultConclusion = createElem("p",{"class":"pdicesconclusion"});
            if( reponse.dices_succes == false ){
              pdiceResultConclusion.innerText = "Echec";
            }else{
              pdiceResultConclusion.innerText = "Succès";
            }
            if( reponse.dices_critical == true){
              pdiceResultConclusion.innerText += " critique!";
            }
            container.appendChild(pdiceResultConclusion);
          }
          
          let piaanalyse = createElem("p",{"class":"panalyse"});
          piaanalyse.innerText = reponse.responseanalysis;
          container.appendChild(piaanalyse);
    
          clearInterval(checksresponsesintervals[playerUid]);

        } else if (response.status === 404) {
            // Ne rien faire
        } else {
            console.warn(`Requête échouée avec le statut ${response.status}`);
        }

}
async function displayTurn(turnUid){

  for (const playerUid in checksresponsesintervals) {
   clearInterval(checksresponsesintervals[playerUid]);
  }
  

  fetch(`/API/board/${boarduid}/turnMJ/${turnUid}`)
      .then(response => {
    if (!response.ok) throw new Error("Erreur réseau");
    return response.json();
  })
  .then(data => {
    currentTurnUid = data["turnuid"];
    const container = document.getElementById("gamedialogs");
    container.innerHTML = "";

    let pmjglobal = createElem("p",{"class": "mjglobal"});
    pmjglobal.innerText = data["allAwnser"];
    container.appendChild(pmjglobal);

    // Narations personnalisées des joueurs
    const playersAnswers = data.personalisedAwnsers;
    for (const playerUid in playersAnswers) {
      let divplayerAnswer = createElem("div",{"class": "playeranswer", "id": "divplayeranswer" + playerUid});



      let title =createElem("h3",{"class":"playeranswerTitle"});

      if( playersByUid[ playerUid ]){
        playerCaption = playersByUid[ playerUid ];
      }else{
        playerCaption = playerUid 
      }
      title.textContent = `${playerCaption}`;
      divplayerAnswer.appendChild(title);

      let pPlayerresponse = createElem("p",{"class": "pmjtoone"});
      pPlayerresponse.innerText = playersAnswers[playerUid];
      divplayerAnswer.appendChild(pPlayerresponse);

      let noresponse = true;
      data.playersResponses.forEach(function (reponse) {

        if( reponse.playerUID == playerUid ){
          noresponse = false;
          let preponse = createElem("p",{"class":"preponse"});
          preponse.innerText = reponse.player_response;
          divplayerAnswer.appendChild(preponse);

          if( reponse.tested_skills.length > 0 ){
            let ptestedskills = createElem("p",{"class": "pcompetences"});
            ptestedskills.innerText = reponse.tested_skills.toString();
            divplayerAnswer.appendChild(ptestedskills);
            
            let pbonusmalus = createElem("p",{"class": "pbonus"});
            pbonusmalus.innerText = reponse.dices_bonus.toString();
            divplayerAnswer.appendChild(pbonusmalus);

            let pjet = createElem("p",{"class":"pjets" });
            pjet.innerText = reponse.dices_scores.toString();
            divplayerAnswer.appendChild(pjet);

            let img = createElem("img", {"src": "/imgs/dé20.png", "class": "pdicelogo"});
            divplayerAnswer.appendChild(img);

            let pdiceResultConclusion = createElem("p",{"class":"pdicesconclusion"});
            if( reponse.dices_succes == false ){
              pdiceResultConclusion.innerText = "Echec";
            }else{
              pdiceResultConclusion.innerText = "Succès";
            }
            if( reponse.dices_critical == true){
              pdiceResultConclusion.innerText += " critique!";
            }
            divplayerAnswer.appendChild(pdiceResultConclusion);
          }

          let piaanalyse = createElem("p",{"class":"panalyse"});
          piaanalyse.innerText = reponse.responseanalysis;
          divplayerAnswer.appendChild(piaanalyse);
    

        }
      });
      container.appendChild(divplayerAnswer);
      if(noresponse && currentTurnUid == turnsUids.at(-1)){
        //le joueur n'a pas répondu, on va checker réguilièrement
        checksresponsesintervals[playerUid] = setInterval(  checkPlayerResponse , 3000, boarduid,currentTurnUid, playerUid );
      }

    }
    
    if( data["closedTurn"] == false  && currentTurnUid == turnsUids.at(-1) ){
      container.appendChild( getDivPrompt() );
    }


    //nav buttons
    let navs = createElem("div",{"class":"navdiv"});
    if( turnsUids.indexOf(currentTurnUid ) > 0 ){
      //previous page
      let buttonprevious = createElem("button", {"type":"button","class":"navturnsbutton previousbutton","title":"Voir le tour précédent"});
      buttonprevious.innerText = "◀";
      buttonprevious.addEventListener("click",(event)=>{
        let currentIndex = turnsUids.indexOf( currentTurnUid );
        displayTurn(turnsUids[ currentIndex - 1  ]);
      });
      navs.appendChild(buttonprevious);

      //first page
      let buttonfirst = createElem("button", {"type":"button","class":"navturnsbutton firstbutton","title":"Voir le premier tour"});
      buttonfirst.innerText = "⏮";
      buttonfirst.addEventListener("click",(event)=>{
        displayTurn(turnsUids[0]);
      });
      navs.appendChild(buttonfirst);
    }

    if( turnsUids.indexOf(currentTurnUid ) < turnsUids.length - 1 ){    
      //next page
      let buttonnext =  createElem("button", {"type":"button","class":"navturnsbutton nextbutton","title":"Voir le tour suivant"});
      buttonnext.innerText = "▶";
      buttonnext.addEventListener("click",(event)=>{
        let currentIndex = turnsUids.indexOf( currentTurnUid );
        displayTurn(turnsUids[ currentIndex + 1]);
      });
      navs.appendChild(buttonnext);

      //last page
      let buttonlast = createElem("button",{"type":"button","class":"navturnsbutton lastbutton","title":"Voir le dernier tour"});
      buttonlast.innerText = "⏭";
      buttonlast.addEventListener("click",(event)=>{
        displayTurn(turnsUids.at(-1));
      });
      navs.appendChild(buttonlast);
    }
    container.appendChild(navs);

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

    if (boarduid !== "") {
      async function fetchPlayers() {
        try {
          const response = await fetch(`/API/board/${boarduid}/players`);
          if (!response.ok) throw new Error('Erreur de chargement');
          const players = await response.json();
  
          const container = document.getElementById('players-list');
          
          players.forEach(player => {
            
            playersByUid[ player.uid ] = `${player.name} (${player.origine} ${player.job})`;

            if( document.getElementById("div-listplayers-" + player.uid)){
              //already exists just update pv, fortune and equipment
              document.getElementById("pointsdevie" + player.uid).innerText = `${player.lifePoints}/${player.lifePointsMax}`;
              document.getElementById("pfortune" + player.uid).innerText = `${player.fortune} pièces d'or`;
              let ulequipment = createElem("ul",{});
              ulequipment.innerHTML = player.equipment.map(eq => `<li>${eq}</li>`).join('');
              document.getElementById("pequipment" + player.uid ).innerHTML = "";
              document.getElementById("pequipment" + player.uid ).appendChild(ulequipment);
              

            }else{
              let divplayer = createElem("div",{"class": "player-entry", "id": "div-listplayers-" + player.uid});


              let divtitle = createElem("div", {"class":"player-header"} );
              divtitle.innerText = `${player.name} (${player.origine} ${player.job})`;
              divtitle.addEventListener('click', () => {
                document.getElementById("div-listplayers-details" + player.uid).style.display = (document.getElementById("div-listplayers-details" + player.uid).style.display === 'none') ? 'block' : 'none';
              });

              let divdetails = createElem("div",{"class":"player-details", "id":"div-listplayers-details" + player.uid });
              divdetails.style.display = 'none';

              let ppointsDeVie = createElem("p",{"class":"player_stats_elem stats_pv","id":"pointsdevie" + player.uid });
              ppointsDeVie.innerText = `${player.lifePoints}/${player.lifePointsMax}`;
              divdetails.appendChild(ppointsDeVie);

              let pfortune = createElem("p",{"class":"player_stats_elem stats_fortune","id":"pfortune" + player.uid });
              pfortune.innerText = `${player.fortune} pièces d'or`;
              divdetails.appendChild(pfortune);
              
              let pcompetances = createElem("p",{"class":"player_stats_elem stats_competences","id":"pcompetences" + player.uid });
              pcompetances.innerText = `COU: ${player.courage}, INT: ${player.intelligence}, CHA: ${player.charisma}, DEXT: ${player.dexterity}, FO: ${player.strength}`;
              divdetails.appendChild(pcompetances);

              let pequipment = createElem("p",{"class":"player_stats_elem stats_equipment","id":"pequipment" + player.uid });
              let ulequipment = createElem("ul",{});
              ulequipment.innerHTML = player.equipment.map(eq => `<li>${eq}</li>`).join('');
              pequipment.appendChild(ulequipment);
              divdetails.appendChild(pequipment);

              let ptraits = createElem("p",{"class":"player_stats_elem stats_traits","id":"ptraits" + player.uid });
              ptraits.innerText = `${player.specialFeatures || 'Aucun'}`;
              divdetails.appendChild(ptraits);

              let pdescription = createElem("p",{"class":"player_stats_elem stats_description","id":"pdescription" + player.uid });
              pdescription.innerText = `${player.description}`;
              divdetails.appendChild(pdescription);

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


     
      //setInterval(placeTurnsDivs,3000); //to rewrite
      setInterval( refreshTurnList, 3000 );
    }
});

let checksresponsesintervals = [];
let playersByUid = {};
let turnsUids = [];
let currentTurnUid = "";
let lastTurnUid = "";