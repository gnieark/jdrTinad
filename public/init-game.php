<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //triche
    //header('Content-Type: application/json');


    die();


    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (isset($data['players']) && is_array($data['players'])) {
        $players = $data['players'];
        
        // Affichage pour vérification (à retirer en production)
        $promptIA = "Nous démarrons un jeu de rôle dans l'univers Donjon de Naheulbeuk. Il y a " 
        . count($players) . " joueurs dont voici leurs descriptions partielles sous forme d'un JSON:\n"
        . json_encode($players)."\n
        Génère leurs fiches personnages avec des compétances équilibrées (score de 0 à 20 en Courage,Intelligence,charisme,Adresse,"
        ."Force). leurs signes particuliers, la liste de leur équipement et une description du personnage"
        ."Réponds sous forme d'un JSON structuré comme ceci:" .'
            [
                {"nom" : "Roger",
                 "type": "Barbare",
                 "courage" : 12,
                 "intelligence": 6,
                 "charisme" : 7,
                 "adresse": 13,
                 "force": 4,
                 "équipement": "Lorem Ipsum...",
                 "Description": "Lorem Ipsum..."
                }
            ]';


        


        $apiKey = file_get_contents("../config/mistralapikey.txt");
        $url = 'https://api.mistral.ai/v1/chat/completions';
        
        $data = array(
            'model' => 'mistral-large-latest',
            'messages' => array(array(
                    'role' => 'user',
                    'content' => $promptIA
            )),
            'response_format' => array("type" => "json_object")
        )
        ;
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {

            echo 'Erreur cURL : ' . curl_error($ch);

        } else {

            file_put_contents("prompt.txt",$promptIA);
            file_put_contents("response.txt",$response);
    
            $responseArr = json_decode($response,true);
            $onlyTheResponse = $responseArr["choices"][0]["message"]["content"];
          
    
            $onlyTheResponseArr = json_decode($onlyTheResponse);
            header('Content-Type: application/json');
            file_put_contents("sample.txt",json_encode($onlyTheResponseArr, true));
            echo json_encode($onlyTheResponseArr, true);

        }
        
        curl_close($ch);





        //echo json_encode(["status" => "success", "players" => $players]);
    } else {
        echo json_encode(["status" => "error", "message" => "Données invalides"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
}