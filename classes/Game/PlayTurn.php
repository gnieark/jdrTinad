<?php

class PlayTurn{
    private string $mjPrompt; //Indications given by the host
    private string $allAwnser; //Prompt given to all players
    private array $personalisedAwnsers;
    private bool $closedTurn = false;
    private string $turnUID;
    

    public function __toArrayToPlay( $filterawnsersbyuid = null ): array {
        

        return [
            'allAwnser' => $this->allAwnser ?? null,
            'personalisedAwnsers' => is_null($filterawnsersbyuid )? $this->personalisedAwnsers: $this->personalisedAwnsers[$filterawnsersbyuid],
            'closedTurn' => $this->is_closed($filterawnsersbyuid),
            'turnuid'   => $this->turnUID
        ];
    }
    public function is_closed( $filterawnsersbyuid = null ): bool{
        return $this->closedTurn;
    }
    public function set_mjPrompt( string $prompt, bool $isTheFirstTurn = false ):PlayTurn {
        $this->mjPrompt = $prompt;
        return $this;
    }
    public function get_personalisedAwnsers():array {
        return $this->personalisedAwnsers;
    }
    public function get_playersResponses(){
        //to do
        return array();
    }
    public function get_allAwnser():string{
        return $this->allAwnser;
    }
    public function __Construct(){
        if(!isset( $this->turnUID )){
            $this->turnUID = uniqid();
        }
    }
    public function get_turnUID():string{
        return $this->turnUID;
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
        $rep = self::sendMessageToIa($promptToSend );
        $this->allAwnser = $rep["all"];
        foreach($rep["personalised"] as $r){
            $this->personalisedAwnsers[ $r["player-uid"] ] = $r["message"];
        }
        return $this;

    }

    static public function sendMessageToIa($message){

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
            //file_put_contents("./Mistral-brut.txt",$response);
            $responseArr = json_decode($response,true);
            $onlyTheResponse = $responseArr["choices"][0]["message"]["content"];

            $rep = json_decode($onlyTheResponse,true); 
            curl_close($ch);
            return $rep;
        }

    }




}