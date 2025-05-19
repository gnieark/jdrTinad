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

  //to rewrite

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
            plistcompetances.innerText = "Compétances testées :" + reponse.tested_skills.toString()+ " bonus: " + reponse.dices_bonus.toString();
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


async function refreshTurnList(){
  fetch(`/API/board/${boarduid}/turnslist`)
  .then(response => {
    if (!response.ok) throw new Error("Erreur réseau");
    return response.json();
  })
  .then(data => {

    turnsUids = data["turns"];
    if(turnsUids.length == 0 ){
      //do nothing
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



async function displayTurn(turnUid){
  /*
  * To do
  * la modifier pour l'adapter au MJ
  */

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

    let pmjpersonalized = createElem("p",{"class": "mjpersonalized"});
    pmjpersonalized.innerText = data["personalisedAwnsers"];
    container.appendChild(pmjpersonalized);
  
    if(data["playersResponses"]["player_response"]){
      let pPlayerresponse = createElem("p",{"class": "pplayerresponse"});
      pPlayerresponse.innerText = data["playersResponses"]["player_response"];
      container.appendChild(pPlayerresponse);
    }

    if(( data["playersResponses"]["tested_skills"] ) && (data["playersResponses"]["tested_skills"].length > 0 )){

      let imgDices = createElem("img",{"src" :"/imgs/dé20.png", "alt": "dé 20", "class":"diceimg"});
      container.appendChild(imgDices);

      let divTests = createElem("div", {"class": "test"});
      //compétances testees lancer de dé bonusmalus 
      let pcompetances = createElem("p",{"class": "pcompetances"});
      pcompetances.innerText = data["playersResponses"]["tested_skills"].toString();
      divTests.appendChild(pcompetances);
      
      let pbonus = createElem("p",{"class": "pbonus"});
      pbonus.innerText = data["playersResponses"]["dices_bonus"].toString();
      divTests.appendChild(pbonus);

      let pjets = createElem("p",{"class":"pjets"});
      //pjets.innerText = data
      pjets.innerText = data["playersResponses"]["dices_scores"].toString();
      divTests.appendChild(pjets);

      let presult = createElem("p", {"class":"presult"});
      if( data["playersResponses"]["dices_succes"] == true ){
        presult.innerText = "Succès";
      }else{
        presult.innerText = "Echec";
      }

      if( data["playersResponses"]["dices_critical"] == true ){
        presult.innerText += " critique!";
      }
      divTests.appendChild(presult);
    
      container.appendChild(divTests);
    }
   
    if( data["closedTurn"] == false && !data["playersResponses"]["player_response"]  && currentTurnUid == turnsUids.at(-1) ){

      let divreponse = createElem("div",{"class": "formresponse"});
      let reponseTextarea = createElem("textarea", {"id":"playerresponsetextarea","placeholder":"Votre réponse...","rows":3} )
      divreponse.appendChild(reponseTextarea);
      let theButton = createElem("button",{"type":"button","class":"submitButton"});
      theButton.innerText = "Envoyer";
      theButton.addEventListener('click', () => { submitAnwser( document.getElementById("playerresponsetextarea").value,currentTurnUid ); });
      divreponse.appendChild(theButton);
      container.appendChild(divreponse);
    }

    //nav buttons
    if( turnsUids.indexOf(currentTurnUid ) > 0 ){
      //previous page
      let buttonprevious = createElem("button", {"type":"button","class":"navturnsbutton previousbutton","title":"Voir le tour précédent"});
      buttonprevious.innerText = "◀";
      buttonprevious.addEventListener("click",(event)=>{
        let currentIndex = turnsUids.indexOf( currentTurnUid );
        displayTurn(turnsUids[ currentIndex - 1  ]);
      });
      container.appendChild(buttonprevious);

      //first page
      let buttonfirst = createElem("button", {"type":"button","class":"navturnsbutton firstbutton","title":"Voir le premier tour"});
      buttonfirst.innerText = "⏮";
      buttonfirst.addEventListener("click",(event)=>{
        displayTurn(turnsUids[0]);
      });
      container.appendChild(buttonfirst);
    }

    if( turnsUids.indexOf(currentTurnUid ) < turnsUids.length - 1 ){    
      //next page
      let buttonnext =  createElem("button", {"type":"button","class":"navturnsbutton nextbutton","title":"Voir le tour suivant"});
      buttonnext.innerText = "▶";
      buttonnext.addEventListener("click",(event)=>{
        let currentIndex = turnsUids.indexOf( currentTurnUid );
        displayTurn(turnsUids[ currentIndex + 1]);
      });
      container.appendChild(buttonnext);

      //last page
      let buttonlast = createElem("button",{"type":"button","class":"navturnsbutton lastbutton","title":"Voir le dernier tour"});
      buttonlast.innerText = "⏭";
      buttonlast.addEventListener("click",(event)=>{
        displayTurn(turnsUids.at(-1));
      });
      container.appendChild(buttonlast);
    }
  })
  .catch(error => {
    console.error("Erreur lors de la vérification de la version :", error);
  });

}




































async function placeTurnsDivs(){


  //to rewrite

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

let playersByUid = {};
let turnsUids = [];
let currentTurnUid = "";
let lastTurnUid = "";