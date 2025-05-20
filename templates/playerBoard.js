const boarduid = "{{boarduid}}";

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
  
  let pcompetences = createElem("p",{"class":"player_stats_elem stats_competences","id":"pcompetences" + player.uid });
  pcompetences.innerText = `COU: ${player.courage}, INT: ${player.intelligence}, CHA: ${player.charisma}, DEXT: ${player.dexterity}, FO: ${player.strength}`;
  divdetails.appendChild(pcompetences);

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
    displayTurn(currentTurnUid);
    document.getElementById('loading-overlay').style.display = 'none';
    //updateGame(); // Recharger les dialogues après l'envoi

  })
  .catch(error => {
    console.error("Erreur lors de l'envoi de la réponse :", error);
    alert("Une erreur est survenue lors de l'envoi de votre réponse.");
  });
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
        let pcompetences = createElem("p",{"class": "pcompetences"});
        pcompetences.innerText = data["playersResponses"]["tested_skills"].toString();
        divTests.appendChild(pcompetences);
        
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

    setInterval( refreshTurnList, 3000 );
    refresh_character_sheet();

  });