<?php
class Api extends Route{

    protected static $displayOnPage = false;


    static public function send_content(User $user):string{


        header('Content-Type: application/json; charset=utf-8');

        if(preg_match ( "'^/API/board/(.+)/players$'" , $_SERVER["REQUEST_URI"], $matches)){
            
            $bordUid = $matches[1];
            if(!Board::boardFileExists($bordUid)){
                 C404::send_content_json();
            }

            $board = Board::loadBoard($bordUid);
            $players = $board->get_players();
            $playersArr = array();
            foreach($players as $player){
                $playersArr[] = $player->__toArray();
            }
            echo json_encode($playersArr,true);
            die();

        }elseif( preg_match ( "'^/API/board/(.+)/version-uid$'" , $_SERVER["REQUEST_URI"], $matches) ){
            $bordUid = $matches[1];
            if(!Board::boardFileExists($bordUid)){
                 C404::send_content_json();
            }

            $board = Board::loadBoard($bordUid);
            echo json_encode(array(
                "message"       => "OK",
                "version-uid"   => $board->get_saveUid()
            ),true);
            die();
            
        }elseif( preg_match ( "'^/API/board/(.+)/turns$'" , $_SERVER["REQUEST_URI"], $matches) ){
            $bordUid = $matches[1];
            if(!Board::boardFileExists($bordUid)){
                 C404::send_content_json();
            }

            $board = Board::loadBoard($bordUid);
            $arr = array();
            foreach( $board->get_playTurns() as $turn){
                $turn->loadPlayersResponses($bordUid);
                $arr[] = $turn->__toArrayToPlay( BoardPlayer::get_uid_from_cookie() );
            }
            echo (json_encode($arr, true ));

            die();
        }elseif( preg_match ( "'^/API/board/(.+)/turnslist$'" , $_SERVER["REQUEST_URI"], $matches) ){
            $bordUid = $matches[1];
            //retourne la liste des tours avec leur UID
            if( !$user->is_in_group("mj") ){
                return C403::send_content_json();
            }
            if(!$user->does_own_board($bordUid)){
                return C403::send_content_json();
            }

            $board = Board::loadBoard($bordUid);
            $turns = $board->get_playTurns();
            $turnsArr = array();
            foreach($turns as $turn){
                $turnsArr[] = $turn->get_turnUID();
            }
            echo json_encode(
                array(
                    "message"   => 'OK',
                    "turns"     => $turnsArr
                )
            );
            
            die();
        }elseif( preg_match ( "'^/API/board/(.+)/turnMJ/(.+)$'" , $_SERVER["REQUEST_URI"], $matches) ){
            $bordUid = $matches[1];
            $turnUId = $matches[2];
            $board = Board::loadBoard($bordUid);
            $turn = $board->get_PlayTurnByUid( $turnUId );
            echo json_encode( $turn->__toArrayToPlay(null),true );
            die();

        }else{
            C404::send_content_json();
        }
        return "";
    }

    static public function get_custom_js():string{
        return file_get_contents ("../templates/auth.js");
    }
    static public function apply_post(User $user):string{
        header('Content-Type: application/json; charset=utf-8');
        if(preg_match ( "'^/API/board/(.+)/mjprompt$'" , $_SERVER["REQUEST_URI"], $matches)){

            if( !$user->is_in_group("mj") ){
                return C403::send_content_json();
            }
    

            $bordUid = $matches[1];
            if(!Board::boardFileExists($bordUid)){
                 C404::send_content_json();
            }
            if(!$user->does_own_board($bordUid)){
                return C403::send_content_json();
            }

            $board = Board::loadBoard($bordUid);

            
            $arr = json_decode( file_get_contents('php://input'), true );
            $gameTurn = new PlayTurn();
            if(empty($board->get_playTurns())){
                //it's the first turn

            }

            $board->closeLastTurn();

            $gameTurn->set_mjPrompt($arr["prompt"]);

            
            $gameTurn->playPrompt( $board );




            $board->add_playTurn($gameTurn);
            $board->save();
         

            echo '{}'; die();


        }elseif( preg_match ( "'^/API/board/(.+)/turn/(.+)$'" , $_SERVER["REQUEST_URI"], $matches)   ){
        
            $boardUid = $matches[1];
            $turnUid = $matches[2];

            $board = Board::loadBoard($boardUid);
            $turns = $board->get_playTurns();

            $turn = end( $turns );

            if(( $turn->get_turnUID() !== $turnUid ) || $turn -> is_closed( BoardPlayer::get_uid_from_cookie() )) {
                header("HTTP/1.1 409 Conflict");
                echo json_encode(array(
                    "code" => 409,
                    "error" => "Turn is already closed for answers."
                ));
                die();

            }

            $arr = json_decode( file_get_contents('php://input'), true );
            $playerResponse = new PlayerResponse( $turnUid,  BoardPlayer::get_uid_from_cookie() );
            $playerResponse -> set_playerresponse($arr["message"]);
            $playerResponse -> analyseResponse($board);
            $success = $board->add_playerResponse($playerResponse );

            if(!$success) {
                header("HTTP/1.1 409 Conflict");
                echo json_encode(array(
                    "code" => 409,
                    "error" => "Turn is already closed for answers."
                ));
                die();

            }else{
                echo json_encode(array(
                    "code" => 200,
                    "message" => "Réponse ajoutée."
                ));


            }
            die();

      
        }else{
            C404::send_content_json();
        }


        return "";
    }
}