<?php

class PlayTurn{
    private string $mjPrompt; //Indications given by the host
    private string $allAwnser; //Prompt given to all players
    private array $personalisedAwnsers;
    private array $playerResponses = array(); //array of PlayerResponse Objs
    private bool $closedTurn = false;
    private string $turnUID;
    

    public function __toArrayToPlay( $filterawnsersbyuid = null, bool $withResponses = true ): array {
        
        $playerResponsesArr = array();
        if(is_null($filterawnsersbyuid)){
            
            foreach($this->playerResponses as $response )
            {
                $playerResponsesArr[] = $response->_toArrayToPlay();
            }

        }else{
            foreach($this->playerResponses as $response )
            {
                if( $response->get_playerUID() == $filterawnsersbyuid ){
                    $playerResponsesArr = $response->_toArrayToPlay();
                }
            }

        }
        if( is_null( $filterawnsersbyuid )){
            $personalisedAwnsers = $this->personalisedAwnsers;
        }else{
            $personalisedAwnsers = isset($this->personalisedAwnsers[$filterawnsersbyuid])?$this->personalisedAwnsers[$filterawnsersbyuid]:"";
        }
        if($withResponses){
            $arr = [
                'allAwnser' => $this->allAwnser ?? null,
                'personalisedAwnsers' => $personalisedAwnsers,
                'playersResponses'  =>  $playerResponsesArr,
                'closedTurn' => $this->is_closed($filterawnsersbyuid),
                'turnuid'   => $this->turnUID
            ];
        }else{
            $arr = [
                'allAwnser' => $this->allAwnser ?? null,
                'personalisedAwnsers' => $personalisedAwnsers
            ];
        }

        return $arr;
    }

    public function loadPlayersResponses(string $boarduid):self{
        $folderPath = "../gamesdatas/" . $boarduid . "/turn-" . $this->get_turnUID();
        if(!is_dir($folderPath)){
            mkdir($folderPath);
        }
        $files = scandir($folderPath);
        $this->playerResponses = array();
        foreach ($files as $file) {
            if (str_ends_with($file, '.txt')) {
                $filePath = $folderPath . "/" . $file;
                $this->playerResponses[] = PlayerResponse::load($filePath);
            }
        }
        return $this;
    }

    public function is_closed( $filterawnsersbyuid = null ): bool{
        return $this->closedTurn;
    }
    public function close(){
        $this->closeTurn = true;
    }
    public function set_mjPrompt( string $prompt, bool $isTheFirstTurn = false ):PlayTurn {
        $this->mjPrompt = $prompt;
        return $this;
    }
    public function get_personalisedAwnsers():array {
        return $this->personalisedAwnsers;
    }
    public function get_playersResponses():array{
        return $this->playerResponses;
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
    /*
    * The current board is needed to get the history
    */
    public function playPrompt( Board $board ): PlayTurn{

        $playsTurns = $board->get_playTurns();
        $tplFile = "../templates/prompts/promptIA-newTurn.txt";
        $tplBlock = new TplBlock();

        $players = $board->get_players();


        //players
        $playersArr = array();
        foreach ($players as $player){
            $playersArr[] = $player->__toArray();

        }
        $tplBlock->addVars(
            array(
                "players" => json_encode($playersArr,true)
            )
        );

        $tplBlock->addVars(
            array("summary"    => $board->get_gameSummary() 
        ));
        
        if(!empty($playsTurns)){
            $lastPlayTurn = end($playsTurns);
            $lastPlayTurn->loadPlayersResponses( $board->get_urlpart() );
            $tplBlock->addVars(
                array(
                    "lastturn" => json_encode($lastPlayTurn->__toArrayToPlay(),true)
                )
            );
        }else{
            $tplBlock->addVars(
                array(
                    "lastturn" => '[]'
                )
            );
        }

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

        //make the resquest to IA
        $rep = self::sendMessageToIa($promptToSend, $board);
       
        $board->set_gameSummary( $rep["storyState"] )
                ->save();
        
        $this->allAwnser = $rep["all"];
        foreach($rep["personalised"] as $r){
            $this->personalisedAwnsers[ $r["player-uid"] ] = $r["message"];

            //enregister les modifications de chaque player
            $player = $board->get_player_by_uid($r["player-uid"]);
            $player->applyDeltaPv($r["delta-lifePoints"])
                   ->applyDeltaFortune($r["delta-fortune"])
                   ->addXP( $r["delta-xp"] );

            foreach($r["lost-equipment"] as $lostEquipment){
                $player->removeEquipment($lostEquipment);
            }
            foreach($r["picked-equipment"] as $pickedEquipment){
                $player->addEquipment($pickedEquipment);
            }
     
            $player->save(  $board->get_save_real_path()."/player-" . $player->getUid()   );

        }

        return $this;

    }

    static public function sendMessageToIa(string $message, Board $board = null ){
        $traceDir = null;
        if( !is_null( $board ) && is_dir( $board->get_save_real_path(). "/apitraces") ){
            $traceDir = rtrim($board->get_save_real_path(), '/') . "/apitraces";
        }

        $apiKeyF = json_decode(file_get_contents("../config/mistralapikey.json"),true);
        $apiKey = $apiKeyF["key"];
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
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 45
        ];

        $maxAttempts = 3;
        $lastError = 'Réponse IA invalide.';

        for($attempt = 1; $attempt <= $maxAttempts; $attempt++){
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $response = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

            //debog mode only if path exists
            $trace = [
                'attempt' => $attempt,
                'max_attempts' => $maxAttempts,
                'http_code' => $httpCode,
                'curl_errno' => $curlErrno,
                'curl_error' => $curlError,
                'request' => $data,
                'raw_response' => $response
            ];

            if ($traceDir) {
                $now = microtime(true);
                $dt = DateTime::createFromFormat('U.u', sprintf('%.6f', $now));
                $timestamp = $dt->format("Ymd-His-u"); 
                $uuid = bin2hex(random_bytes(4));
                $filename = "$traceDir/$timestamp-$uuid.json";
                file_put_contents($filename, json_encode($trace, JSON_PRETTY_PRINT));
            }

            if ($curlErrno !== 0) {
                $lastError = 'Erreur cURL IA : ' . $curlError;
                curl_close($ch);
                if($attempt < $maxAttempts){
                    usleep(250000 * $attempt);
                    continue;
                }
                throw new RuntimeException($lastError);
            }

            curl_close($ch);

            $responseArr = json_decode($response,true);
            if(!isset($responseArr["choices"][0]["message"]["content"])){
                $lastError = 'Réponse IA invalide.';
                if($attempt < $maxAttempts && ($httpCode === 429 || $httpCode >= 500)){
                    usleep(250000 * $attempt);
                    continue;
                }
                throw new RuntimeException($lastError);
            }
            $onlyTheResponse = $responseArr["choices"][0]["message"]["content"];

            $rep = json_decode($onlyTheResponse,true); 
            if(!is_array($rep)){
                $lastError = 'JSON IA invalide.';
                if($attempt < $maxAttempts){
                    usleep(250000 * $attempt);
                    continue;
                }
                throw new RuntimeException($lastError);
            }
            return $rep;
        }

        throw new RuntimeException($lastError);

    }




}
