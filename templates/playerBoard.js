const boarduid = "{{boarduid}}";
let boardversion = "";
let turnsUids = [];
let currentTurnUid = "";
let lastTurnUid = "";

function createElem(type,attributes)
{
    var elem=document.createElement(type);
    for (var i in attributes)
    {elem.setAttribute(i,attributes[i]);}
    return elem;
}

async function refresh_character_sheet(){
  const container = document.getElementById("character-sheet");
  container.innerHTML = "";

  const endpoint = '/API/board/' + boarduid + '/player/myperso';

  const response = await fetch(endpoint);
  if (!response.ok) throw new Error('Erreur de chargement');
  const player = await response.json();

  let divplayer = createElem("div",{"class": "player-entry", "id": "div-listplayers-" + player.uid});


  let divtitle = createElem("div", {"class":"player-header"} );
  divtitle.innerText = `${player.name} (${player.origine} ${player.job})`;

  let divdetails = createElem("div",{"class":"player-details", "id":"div-listplayers-details" + player.uid });

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
      
            const bonus = turn.playersResponses.dices_bonus;
            const skills = turn.playersResponses.tested_skills;
            const scores = turn.playersResponses.dices_scores || [];
            const success = turn.playersResponses.dices_succes ? "Oui" : "Non";
            const critical = turn.playersResponses.dices_critical ? "Oui" : "Non";
      
            let resultHtml = `<strong>Jet de compétences (bonus/malus: ${bonus.toString()} ):</strong><br><ul>`;
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
  function thereisANewTurn(){

  }
  async function displayTurn(turnUid){
    fetch(`/API/board/${boarduid}/turnPLAYER/${turnUid}`)
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

      

      console.log(data);

 
    })
    .catch(error => {
      console.error("Erreur lors de la vérification de la version :", error);
    });

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
        thereisANewTurn();

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

    ///API/board/(.+)/turnslist
    setInterval( refreshTurnList, 3000 );

    //setInterval(checkBoardVersion, 3000);
    refresh_character_sheet();

  });