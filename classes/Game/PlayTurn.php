<?php

class PlayTurn{
    private string $mjPrompt;
    private string $allAwnser;
    private array $personalisedAwnsers;
    private bool $closedTurn = false;

    public function __toArrayToPlay( $filterawnsersbyuid = null ): array {
        

        return [
            'allAwnser' => $this->allAwnser ?? null,
            'personalisedAwnsers' => is_null($filterawnsersbyuid )? $this->personalisedAwnsers: $this->personalisedAwnsers[$filterawnsersbyuid],
            'closedTurn' => $this->closedTurn,
        ];
    }

    public function set_mjPrompt( string $prompt, bool $isTheFirstTurn = false ):PlayTurn {
        $this->mjPrompt = $prompt;
        return $this;
    }

    public function playPrompt( array $players, bool $isTheFirstTurn = false ): PlayTurn{
        $tplFile = $isTheFirstTurn? "../templates/promptIA-firstTurn.txt" : "../templates/promptIA-newTurn.txt";
        $tplBlock = new TplBlock();
        $playersArr = array();
        foreach ($players as $player){
            $playersArr[] = $player->__toArray();

        }
        $tplBlock->addVars(
            array(
                "players" => json_encode($playersArr,true)
            )
        );
        if(!empty($this->mjPrompt)){
            $tplcustomInstructs = new TplBlock("customInstructs");
            $tplcustomInstructs->addVars(
                array(
                    "text"  => $this->mjPrompt
                )
            );
            $tplBlock->addSubBlock($tplcustomInstructs);
        }
        $promptToSend =  $tplBlock->applyTplFile($tplFile );
        $rep = $this->sendMessageToIa($promptToSend );
        $this->allAwnser = $rep["all"];
        foreach($rep["personalised"] as $r){
            $this->personalisedAwnsers[ $r["player-uid"] ] = $r["message"];
        }
        return $this;

    }

    private function sendMessageToIa($message){

        $apiKey = file_get_contents("../config/mistralapikey.txt");
        $url = 'https://api.mistral.ai/v1/chat/completions';
        
        $data = array(
            'model' => 'mistral-large-latest',
            'messages' => array(array(
                    'role' => 'user',
                    'content' => $message
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
            file_put_contents("./Mistral-brut.txt",$response);
            $responseArr = json_decode($response,true);
            $onlyTheResponse = $responseArr["choices"][0]["message"]["content"];

            $rep = json_decode($onlyTheResponse,true); 
            curl_close($ch);
            return $rep;
        }

    }




}