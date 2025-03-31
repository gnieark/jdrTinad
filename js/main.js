// Fonction pour créer un élément HTML avec des attributs
function createElem(type, attributes) {
    var elem = document.createElement(type);
    for (var i in attributes) {
        elem.setAttribute(i, attributes[i]);
    }
    return elem;
}

// Tableau pour stocker les personnages
var players = [];

document.addEventListener('DOMContentLoaded', function () {
    // Ajouter l'écouteur d'événements sur le bouton 'addperso' une fois le DOM chargé
    document.getElementById('addperso').addEventListener('click', function(event) {
        event.preventDefault(); // Empêche l'envoi du formulaire (page ne se recharge pas)
        
        // Récupérer les valeurs des champs
        var name = document.getElementById('addpersoninputname').value;
        var type = document.querySelector('input[name="type"]:checked');
        var traits = document.getElementById('customcaractere').value;
        var otherType = document.getElementById('inputtextothertype') ? document.getElementById('inputtextothertype').value : '';

        // Vérifier que le type a bien été sélectionné
        if (!name || !type) {
            alert('Veuillez remplir tous les champs requis.');
            return;
        }

        // Si le type est "Autre", utiliser la valeur personnalisée
        if (type.value === 'other' && otherType) {
            type = otherType;
        } else {
            type = type.value;
        }

        // Créer un objet représentant le personnage
        var player = {
            name: name,
            type: type,
            traits: traits
        };

        // Ajouter le personnage au tableau
        players.push(player);

        // Réinitialiser les champs du formulaire
        document.getElementById('addpersoninputname').value = '';
        document.querySelector('input[name="type"]:checked').checked = false;
        document.getElementById('customcaractere').value = '';
        document.getElementById('inputtextothertype').value = '';
        document.getElementById('pothertype').style.display = 'none'; // Masquer le champ "Autre"

        // Afficher la liste des personnages
        displayPlayers();
    });
});

// Fonction pour afficher les personnages
function displayPlayers() {
    // Récupérer l'élément où afficher les personnages
    var displaySection = document.getElementById('personnagesInit');

    // Si la section des personnages existe déjà, la vider avant de la remplir à nouveau
    var playerList = document.getElementById('playerList');
    if (playerList) {
        playerList.remove();
    }

    // Créer un conteneur pour la liste des personnages
    playerList = createElem('div', { id: 'playerList' });

    // Ajouter chaque personnage dans la liste
    players.forEach(function(player) {
        var playerCard = createElem('div', { class: 'player-card' });

        var playerName = createElem('h3', {});
        playerName.textContent = player.name;

        var playerType = createElem('p', {});
        playerType.textContent = 'Type: ' + player.type;

        var playerTraits = createElem('p', {});
        playerTraits.textContent = 'Traits: ' + player.traits;

        // Ajouter les éléments au conteneur de la carte
        playerCard.appendChild(playerName);
        playerCard.appendChild(playerType);
        playerCard.appendChild(playerTraits);

        // Ajouter la carte au conteneur de la liste
        playerList.appendChild(playerCard);
    });

    // Ajouter la liste des personnages dans le formulaire
    displaySection.appendChild(playerList);
}




function createCharacterCards(characters) {
    let container = createElem("div",{"id": "characters-container"});
    container.innerHTML = "";

    characters.forEach(character => {
        var card = createElem("div", { class: "character-card" });
        
        var name = createElem("h2", {});
        name.textContent = character.nom;

        var type = createElem("p", {});
        type.innerHTML = `<strong>Type:</strong> ${character.type}`;
        
        var stats = createElem("p", {});
        stats.innerHTML = `<strong>Stats:</strong> Courage: ${character.courage}, Intelligence: ${character.intelligence}, Charisme: ${character.charisme}, Adresse: ${character.adresse}, Force: ${character.force}`;
        
        var traits = createElem("p", {});
        traits.innerHTML = `<strong>Traits:</strong> ${character.traits}`;
        
        var equipement = createElem("p", {});
        equipement.innerHTML = `<strong>Équipement:</strong> ${character.équipement}`;
        
        var description = createElem("p", {});
        description.innerHTML = `<strong>Description:</strong> ${character.Description}`;

        card.appendChild(name);
        card.appendChild(type);
        card.appendChild(stats);
        card.appendChild(traits);
        card.appendChild(equipement);
        card.appendChild(description);
        
        container.appendChild(card);
    });
    return container;
}




async function initGame(){
    document.getElementById("initform").innerHTML = '<h2>Patientez...</h2>'
    try {
        const response = await fetch('/init-game.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ players })
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP : ${response.status}`);
        }

        const data = await response.json();
        document.getElementById("initform")?.remove();
        //document.getElementById("playersCards").innerHTML = JSON.stringify(data);
        document.getElementById("playersCards").appendChild(createCharacterCards(data));
        console.log('Réponse du serveur :', data);
        return data;
    } catch (error) {
        console.error('Erreur lors de l\envoi des joueurs :', error);
    }
    
}
// Afficher les personnages dès le chargement de la page si nécessaire
displayPlayers();