<?php

class BoardPlayer extends Route{

    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/playerBoard.css");
    }
    static public function get_custom_js():string{

        preg_match ( "'^/(.+)$'" , $_SERVER["REQUEST_URI"], $matches);
        $urlpart = $matches[1];
        if( file_exists( "../gamesdatas/" . $urlpart . "/player-" .  self::get_uid_from_cookie()  )){
            $tpl = new TplBlock();
            $tpl->addVars(array("boarduid" => $urlpart ));
            return $tpl->applyTplFile ("../templates/playerBoard.js");
        }else{
            return file_get_contents ("../templates/playerBoard-init.js");

        }


    }

    static public function get_uid_from_cookie():string{
        if( !isset($_COOKIE["jdr_uid"]) ){
            $cookie_name = "jdr_uid";
            $cookie_value = uniqid();
            $cookie_duration = time() + (30 * 24 * 60 * 60); // 30 jours
            $cookie_path = '/'; 

            setcookie($cookie_name, $cookie_value, [
                'expires' => $cookie_duration,
                'path' => $cookie_path,
                'secure' => isset($_SERVER['HTTPS']), 
                'httponly' => true, 
                'samesite' => 'Lax' 
            ]);
            return $cookie_value;
        }else{
            return $_COOKIE["jdr_uid"];
        }
    }

    static public function get_content_html_initialized_player(User $user, Board $board){
        $tpl = new TplBlock();
        $player = Player::loadPlayer(  $board->get_save_real_path()."/player-" . self::get_uid_from_cookie()  );
        $tpl->addVars(
            array(
                "player_name"           => $player->getName(),
                "player_type"           => $player->getType(),
                "player_courage"        => $player->getCourage(),
                "player_intelligence"   => $player->getIntelligence(),
                "player_charisma"       => $player->getCharisma(),
                "player_dexterity"      => $player->getDexterity(),
                "player_strength"       => $player->getStrength(),
                "player_description"    => $player->getDescription()

            )
        );
        foreach( $player->getEquipment() as $equipment ){
            $tplplayerequipment = new TplBlock("playerequipment");
            $tplplayerequipment->addVars(array("name"   => $equipment));
            $tpl->addSubBlock($tplplayerequipment);
        }
        return $tpl->applyTplFile("../templates/playerBoard.html");
    }

    static public function get_content_html(User $user):string{
        if(preg_match ( "'^/(.+)/initpersonnage$'" , $_SERVER["REQUEST_URI"], $matches)){
            header('Location: /' . $matches[1]);
            die();
        }
        if(preg_match ( "'^/(.+)$'" , $_SERVER["REQUEST_URI"], $matches)){
            $urlpart = $matches[1];
        }else{
            return C404::get_content_html($user);
        }
        if(!Board::boardFileExists($urlpart)){
            return C404::get_content_html($user);
        }
        $board = Board::loadBoard($urlpart);
        $savePath = $board->get_save_real_path();

        if(!file_exists($savePath ."/player-" . self::get_uid_from_cookie() )){
            //joueur non initialisé
            return self::get_content_html_new_player($user,$board );
        }
        
        return self::get_content_html_initialized_player( $user,$board );
    }

    static public function get_content_html_new_player(User $user, Board $board):string{
        $tpl = new TplBlock();
        foreach($board -> get_allowedCreatures() as $allowedCreature ){
            $tplAllowedTypes = new TplBlock("allowedTypes");
            $tplAllowedTypes->addVars(array(
                "value"     => $allowedCreature,
                "caption"   => $allowedCreature
            ));
            $tpl->addSubBlock($tplAllowedTypes);
        }

        $tpl->addVars(
            array("gameurlpart" => $board->get_urlpart() )
        );
        return $tpl->applyTplFile("../templates/playerBoard-init.html");
    }


    static public function apply_post(User $user):string {

        if(preg_match ( "'^/(.+)/initpersonnage$'" , $_SERVER["REQUEST_URI"], $matches)){
            $urlPart = $matches[1];
            $board = Board::loadBoard($urlPart);
            $promptIa = new TplBlock();
            $promptIa->addVars(
                array(
                    "playername" => $_POST["name"],
                    "playertype" => $_POST["race"],
                    "traits"    => $_POST["traits"]
                )
            );
            
            $apiKey = file_get_contents("../config/mistralapikey.txt");
            $url = 'https://api.mistral.ai/v1/chat/completions';
            
            $data = array(
                'model' => 'mistral-large-latest',
                'messages' => array(array(
                        'role' => 'user',
                        'content' => $promptIa->applyTplFile("../templates/promptIA-creerpersonnage.txt")
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
    
                $responseArr = json_decode($response,true);

                // To do gérer le cas d'erreur
                $onlyTheResponse = $responseArr["choices"][0]["message"]["content"];

                $rep = json_decode($onlyTheResponse,true);

                $player = new Player();
                
                $player ->setUid( SELF::get_uid_from_cookie() )
                        ->setName( $rep["nom"] )
                        ->setType( $rep["type"] )
                        ->setCourage( $rep["courage"] )
                        ->setIntelligence( $rep["intelligence"] )
                        ->setCharisma( $rep["charisme"] )
                        ->setDexterity( $rep["adresse"] )
                        ->setStrength( $rep["force"] )
                        ->setEquipment( $rep["equipement"] )
                        ->setDescription( $rep["description"]);

                $player->save( $board->get_save_real_path()."/player-" . self::get_uid_from_cookie() );     
            }
            
            curl_close($ch);
            header('Location: /' . $board->get_urlpart() );
            die();
            

        }
        return "";
    }
}