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

            $bordUid = $matches[1];
            if(!Board::boardFileExists($bordUid)){
                 C404::send_content_json();
            }

            $board = Board::loadBoard($bordUid);

            
            $arr = json_decode( file_get_contents('php://input'), true );
            $gameTurn = new PlayTurn();
            if(empty($board->get_playTurns())){
                //it's the first turn

            }
           
            $gameTurn->set_mjPrompt($arr["prompt"]);
            $gameTurn->playPrompt( $board->get_players(), true );
            $board->add_playTurn($gameTurn);
            $board->save();
         

            echo '{}'; die();


        }


        return "";
    }
}