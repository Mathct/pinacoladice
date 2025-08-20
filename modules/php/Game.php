<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * pinacoladice implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */
declare(strict_types=1);

namespace Bga\Games\pinacoladice;

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");

include('Pending.php'); // ATTENTION


class Game extends \Table
{
    private static array $CARD_TYPES; // ATTENTION
    public static $instance = null; //ATTENTION

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate console.warnhere a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();

        require 'material.inc.php';

        $this->initGameStateLabels([
            "game_mode" => 100,
        ]);  
        
        
        self::$instance = $this; // ATTENTION

        $this->bocks= self::getNew("module.common.deck");
        $this->bocks->init("bocks");

        
    }


    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName()
    {
        return "pinacoladice";
    }

/////////////////////////////////////////////////////////////////////////////////  
//       _____                        _____       _ _   _       _ _          _   _             
//      / ____|                      |_   _|     (_) | (_)     | (_)        | | (_)            
//     | |  __  __ _ _ __ ___   ___    | |  _ __  _| |_ _  __ _| |_ ______ _| |_ _  ___  _ __  
//     | | |_ |/ _` | '_ ` _ \ / _ \   | | | '_ \| | __| |/ _` | | |_  / _` | __| |/ _ \| '_ \ 
//     | |__| | (_| | | | | | |  __/  _| |_| | | | | |_| | (_| | | |/ / (_| | |_| | (_) | | | |
//      \_____|\__,_|_| |_| |_|\___| |_____|_| |_|_|\__|_|\__,_|_|_/___\__,_|\__|_|\___/|_| |_|
//                                                                                               
/////////////////////////////////////////////////////////////////////////////////    


    protected function setupNewGame($players, $options = [])
    {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();


        self::initStat( 'table', 'turns_number', 0 );

        self::initStat( 'player', 'turns_number', 0 );
        self::initStat( 'player', 'score', 0 );
        self::initStat( 'player', 'pina', 0 );
        self::initStat( 'player', 'place', 0 );

        
        self::DbQuery("INSERT INTO dice () VALUES ()");


        /* init bocks */

        for ($i = 1; $i <= 25; $i++) {

            $bocks[] = array('type' => $i, 'type_arg' => 1, 'nbr' => 1);
        }

        $this->bocks->createCards($bocks, 'deck');
        $this->bocks->shuffle('deck');

        /* place bock */

        $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

        for ($i = 11; $i <= 14; $i++) 
        {
        $this->bocks->pickCardForLocation('deck', 'board', $i);
        }
        for ($i = 21; $i <= 24; $i++) 
        {
        $this->bocks->pickCardForLocation('deck', 'board', $i);
        }
        for ($i = 31; $i <= 34; $i++) 
        {
        $this->bocks->pickCardForLocation('deck', 'board', $i);
        }
        for ($i = 41; $i <= 44; $i++) 
        {
        $this->bocks->pickCardForLocation('deck', 'board', $i);
        }

        if($nbreplayers == 3)
        {

            self::DbQuery("UPDATE bocks SET card_type_arg = 2 WHERE card_location_arg IN (11, 14, 22, 23, 32, 33, 41, 44)");
        }

        if($nbreplayers == 4)
        {

            self::DbQuery("UPDATE bocks SET card_type_arg = 2 WHERE card_location = 'board'");
        }

        $players_for_no = self::getObjectListFromDB( "SELECT player_id id, player_no no FROM player" );
        foreach($players_for_no as $player_for_no)
        {
            if($player_for_no['no']==1)
            {
                self::DbQuery("UPDATE player SET player_score = 1 WHERE player_id = {$player_for_no['id']}");
            }
            if($player_for_no['no']==2)
            {
                self::DbQuery("UPDATE player SET player_score = 2 WHERE player_id = {$player_for_no['id']}");
            }
            if($player_for_no['no']==3)
            {
                self::DbQuery("UPDATE player SET player_score = 3 WHERE player_id = {$player_for_no['id']}");
            }
            if($player_for_no['no']==4)
            {
                self::DbQuery("UPDATE player SET player_score = 4 WHERE player_id = {$player_for_no['id']}");
            }

        }



                
        foreach( $players as $player_id => $player )
        {
            $this->addPendingFirst($player_id, "NormalTurn");
        }
    }

/////////////////////////////////////////////////////////////////////////////////  
//               _            _ _ _____        _            
//              | |     /\   | | |  __ \      | |           
//     __ _  ___| |_   /  \  | | | |  | | __ _| |_ __ _ ___ 
//    / _` |/ _ \ __| / /\ \ | | | |  | |/ _` | __/ _` / __|
//   | (_| |  __/ |_ / ____ \| | | |__| | (_| | || (_| \__ \
//    \__, |\___|\__/_/    \_\_|_|_____/ \__,_|\__\__,_|___/
//     __/ |                                                
//    |___/                                                 
/////////////////////////////////////////////////////////////////////////////////  

protected function getAllDatas()
{
    $result = [];

    // WARNING: We must only return information visible by the current player.
    $current_player_id = (int) $this->getCurrentPlayerId();

    // Get information about players.
    // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
    $result["players"] = $this->getCollectionFromDb(
        "SELECT `player_id` `id`, `player_no` `no`, `player_score` `score`, `player_color` `color`, `player_token` `token` FROM `player`"
    );

    $result["mode"] = $this->getGameStateValue('game_mode');

    $result["nbre_payers"] = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

    $result['bocks'] = self::getObjectListFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, score1 score1, score2 score2 FROM bocks WHERE card_location = 'board'");

    $result['forcedFaces'] = self::getObjectListFromDB("SELECT dice1, dice2, dice3, dice4, dice5 FROM dice");
    $result['blockdice'] = self::getObjectListFromDB("SELECT blockrolldice1, blockrolldice2, blockrolldice3, blockrolldice4, blockrolldice5 FROM dice");
    $result['showdice'] = self::getUniqueValueFromDB("SELECT showdice FROM dice WHERE id = 1 ");

    
    // TODO: Gather all information about current game situation (visible by player $current_player_id).

    return $result;
}


/////////////////////////////////////////////////////////////////////////////////  
//     _____                      _____                                   _             
//    / ____|                    |  __ \                                 (_)            
//   | |  __  __ _ _ __ ___   ___| |__) | __ ___   __ _ _ __ ___  ___ ___ _  ___  _ __  
//   | | |_ |/ _` | '_ ` _ \ / _ \  ___/ '__/ _ \ / _` | '__/ _ \/ __/ __| |/ _ \| '_ \ 
//   | |__| | (_| | | | | | |  __/ |   | | | (_) | (_| | | |  __/\__ \__ \ | (_) | | | |
//    \_____|\__,_|_| |_| |_|\___|_|   |_|  \___/ \__, |_|  \___||___/___/_|\___/|_| |_|
//                                                 __/ |                                
//                                                |___/                                 
/////////////////////////////////////////////////////////////////////////////////  

public function getGameProgression()
{
    // TODO: compute and return the game progression

    return 0;
}


/////////////////////////////////////////////////////////////////////////////////  
//     _    _ _   _ _ _ _            __                  _   _                 
//    | |  | | | (_) (_) |          / _|                | | (_)                
//    | |  | | |_ _| |_| |_ _   _  | |_ _   _ _ __   ___| |_ _  ___  _ __  ___ 
//    | |  | | __| | | | __| | | | |  _| | | | '_ \ / __| __| |/ _ \| '_ \/ __|
//    | |__| | |_| | | | |_| |_| | | | | |_| | | | | (__| |_| | (_) | | | \__ \
//     \____/ \__|_|_|_|\__|\__, | |_|  \__,_|_| |_|\___|\__|_|\___/|_| |_|___/
//                           __/ |                                             
//                          |___/                                              
/////////////////////////////////////////////////////////////////////////////////  

function addPending($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL) {
    $sql = "INSERT INTO pending (player_id, function, arg, arg2, arg3, arg4) VALUES (".$player_id.", '".$function."', '".$arg."', '".$arg2."', '".$arg3."', '".$arg4."')";
    self::DbQuery( $sql );
}


function addPendingFirst($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL) {
    $minid = self::getUniqueValueFromDB( "select min(id) from pending")-1;
    $sql = "INSERT INTO pending (id, player_id, function, arg, arg2) VALUES (".$minid.",".$player_id.", '".$function."', '".$arg."', '".$arg2."')";
    self::DbQuery( $sql );
}

function checkArgs($arg1)
    {
        $ret = self::argPlayerTurn();

        if(!in_array($arg1,$ret['selectable']) && !in_array($arg1,$ret['selectable_dice']) && !in_array($arg1,$ret['buttons']))
        {
            throw new feException( "Not a valid selection");
        }
        
    }

function Result($dice) {

    $result = [];

    //RESUTATS:
    // 0 = tous les dès differents // pas de combinaisons
    // 1 = 1 paire
    // 2 = 2 paires differentes
    // 3 = brelan
    // 4 = full val diff
    // 5 = carre
    // 6 = yam's
    // 7 = petite suite
    // 8 = grande suite
    // 9 = tous les dés pairs
    // 10 = tous les dés impairs
    // 11 = somme <= 9
    // 12 = somme = 12,13,14
    // 13 = somme = 21,22,23
    // 14 = somme >= 26

    // paires
    foreach ($dice as $val) {
        if (array_count_values($dice)[$val] >= 2) {
            if(!in_array("1_".$val, $result))
            $result[] = "1_".$val;
            
        }
    }



    //2 paires
    // Compter les occurrences de chaque valeur
        $counts = array_count_values($dice);

        // Initialiser le compteur de paires
        $nbPaires = 0;

        // Parcourir les occurrences
        foreach ($counts as $val => $count) {
            if ($count >= 2) {
                $nbPaires++;
            }
        }

        // Vérifier s'il y a au moins 2 paires différentes
        if ($nbPaires >= 2) {
            if (!in_array(2, $result)) {
                $result[] = "2"; 
            }
        }




    //brelan
    foreach ($dice as $val) {
        if (array_count_values($dice)[$val] >= 3) {
            if(!in_array("3_".$val, $result))
            $result[] = "3_".$val;
            
        }
    }

    // Full : un brelan + une paire de valeur différente
    $counts = array_count_values($dice);
    $hasThree = false;
    $hasTwo = false;

    foreach ($counts as $val => $count) {
        if ($count == 3) $hasThree = true;
        if ($count == 2) $hasTwo = true;
    }

    if ($hasThree && $hasTwo && !in_array(4, $result)) {
        $result[] = "4";
    }

    //carre
    foreach ($dice as $val) {
        if (array_count_values($dice)[$val] >= 4) {
            if(!in_array(5, $result))
            $result[] = "5";
            if (!in_array(2, $result)) {
                $result[] = "2"; 
            }

            
        }
    }

   

    //yams
    foreach ($dice as $val) {
        if (array_count_values($dice)[$val] >= 5) {
            if(!in_array(6, $result))
            $result[] = "6";
            if (!in_array(4, $result)) {
                $result[] = "4"; 
            }

        }
        
    }

    // petites et grandes suites (7 et 8)
    $unique = array_unique($dice);
    sort($unique);
    $count = count($unique);

    // grande suite
    if ($count == 5 && $unique[4] - $unique[0] == 4 &&
        $unique[1] - $unique[0] == 1 &&
        $unique[2] - $unique[1] == 1 &&
        $unique[3] - $unique[2] == 1 &&
        $unique[4] - $unique[3] == 1) {

        if (!in_array(7, $result)) $result[] = "7"; // petite suite
        if (!in_array(8, $result)) $result[] = "8"; // grande suite
        if (!in_array(0, $result)) $result[] = "0"; // tous differents aussi
    } else {
        // sinon, on cherche une suite de 4 consécutifs
        for ($i = 0; $i <= $count - 4; $i++) {
            $slice = array_slice($unique, $i, 4);
            if ($slice[3] - $slice[0] == 3 &&
                $slice[1] - $slice[0] == 1 &&
                $slice[2] - $slice[1] == 1 &&
                $slice[3] - $slice[2] == 1) {

                if (!in_array(7, $result)) $result[] = "7"; // petite suite
                
            }
        }
    }

    //pairs
    $allEven = true;
    foreach ($dice as $val) {
        if ($val % 2 !== 0) {
            $allEven = false;
            break;
        }
    }
    if ($allEven && !in_array(9, $result)) {
        $result[] = "9";
    }

    //impairs
    $allOdd = true;
    foreach ($dice as $val) {
        if ($val % 2 === 0) {
            $allOdd = false;
            break;
        }
    }
    if ($allOdd && !in_array(10, $result)) {
        $result[] = "10";
    }

    //sommes

    $sum = array_sum($dice);

    // somme <= 9 → code 11
    if ($sum <= 9 && !in_array(11, $result)) {
        $result[] = "11";
    }

    // somme = 12, 13, 14 → code 12
    if (in_array($sum, [12, 13, 14]) && !in_array(12, $result)) {
        $result[] = "12";
    }

    // somme = 21, 22, 23 → code 13
    if (in_array($sum, [21, 22, 23]) && !in_array(13, $result)) {
        $result[] = "13";
    }

    // somme >= 26 → code 14
    if ($sum >= 26 && !in_array(14, $result)) {
        $result[] = "14";
    }

    sort($result);
    if(count($result) == 0)
    {
       $result[] = "0";
    }

    
    return $result;
}

function initDice(){

    game::$instance->notifyAllPlayers(
            'maskdice',
            '',
            array(
                
            )
        );

    game::$instance->notifyAllPlayers(
            'masklock',
            '',
            array(
                
            )
        );

    self::DbQuery("UPDATE dice set showdice = 0");
    self::DbQuery("UPDATE dice set blockrolldice1 = 0");
    self::DbQuery("UPDATE dice set blockrolldice2 = 0");
    self::DbQuery("UPDATE dice set blockrolldice3 = 0");
    self::DbQuery("UPDATE dice set blockrolldice4 = 0");
    self::DbQuery("UPDATE dice set blockrolldice5 = 0");

}

function initDiceHappy(){

    game::$instance->notifyAllPlayers(
            'masklock',
            '',
            array(
                
            )
        );

    self::DbQuery("UPDATE dice set showdice = 2");
    self::DbQuery("UPDATE dice set blockrolldice1 = 0");
    self::DbQuery("UPDATE dice set blockrolldice2 = 0");
    self::DbQuery("UPDATE dice set blockrolldice3 = 0");
    self::DbQuery("UPDATE dice set blockrolldice4 = 0");
    self::DbQuery("UPDATE dice set blockrolldice5 = 0");

}

function majScore() {

    $players = self::getObjectListFromDB( "SELECT player_id FROM player", true );
    foreach($players as $player)
    {

        $score = self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id={$player}");
        game::$instance->notifyAllPlayers(
            'score',
            '',
            array(
                'player_id' => $player,
                'score' => $score
                
            )
        );

    }


}

function adjScore($id, $type) {

    $adj = 0;
    $location = self::getUniqueValueFromDB("SELECT card_location_arg FROM bocks WHERE card_type={$type}");

    $tests = [$location -1, $location+1, $location+10, $location -10, $location-11, $location-9, $location+9, $location+11];

    foreach ($tests as $test) {
        
        $emplacement1 = self::getUniqueValueFromDB("SELECT score1 FROM bocks WHERE card_location_arg={$test}");
        $emplacement2 = self::getUniqueValueFromDB("SELECT score2 FROM bocks WHERE card_location_arg={$test}");

        if(($emplacement1 == $id)||($emplacement2 == $id))
        {
            $adj++;
            $type = self::getUniqueValueFromDB("SELECT card_type FROM bocks WHERE card_location_arg={$test}");
            game::$instance->notifyAllPlayers(
                    'animScore',
                    '',
                    array(
                       'bock' => $type,
                       'score' => 1

                    )
            );
        }

    }

    self::DbQuery("UPDATE player SET player_score = player_score + $adj WHERE player_id={$id}");

    return $adj;

}

function positionPlace($id) {

    $position = self::getUniqueValueFromDB("SELECT player_positionplace FROM player WHERE player_id={$id}");
    if($position == 0)
    {
        $newposition = count(self::getObjectListFromDB( "SELECT player_id FROM player WHERE player_positionplace != 0", true )) + 1;
        self::DbQuery("UPDATE player SET player_positionplace = $newposition WHERE player_id={$id}");
        $this->setStat($newposition, 'place', $id);
    }


}

function checkEndGame($id) {

    $player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$id}");

    //test Pina
    $locations = self::getObjectListFromDB( "SELECT card_location_arg FROM bocks WHERE score1 = {$id} OR score2 = {$id}", true );
    
    $pinas = [
    'pina_1' => [11,12,13,14],
    'pina_2' => [21,22,23,24],
    'pina_3' => [31,32,33,34],
    'pina_4' => [41,42,43,44],
    'pina_5' => [11,21,31,41],
    'pina_6' => [12,22,32,42],
    'pina_7' => [13,23,33,43],
    'pina_8' => [14,24,34,44],
    'pina_9' => [11,22,33,44],
    'pina_10' => [14,23,32,41],
    ];

    $pinas_presentes = [];

    foreach ($pinas as $nom => $pina) {
        $diff = array_diff($pina, $locations);
        if (empty($diff)) {
            $pinas_presentes[] = $nom;
    }
    }

    if(count($pinas_presentes) != 0)
    {
        ///y a un PINA !!

        self::DbQuery("UPDATE player SET player_pina = 1 WHERE player_id={$id}");

        self::notifyAllPlayers( 'message', clienttranslate('${player_name} makes a Piña Coladice and wins the game'),
        array(
            'player_name' => $player_name,
                
        ));

        game::$instance->notifyAllPlayers(
                    'pina',
                    '',
                    array(
                      

                    )
        );

        game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 

        // END GAME
        game::$instance->End();
    }

    else
    {
        /// sinon on continue les tests (si score >=20  ou nombre de tokens en reserve = 0)

        $score = self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id={$id}");
        $reservetoken = self::getUniqueValueFromDB("SELECT player_token FROM player WHERE player_id={$id}");
        $end_other_player = self::getObjectListFromDB( "SELECT player_id FROM player WHERE player_end = 1", true );

        if($end_other_player == NULL)  // on teste si un joueur n'a pas declenché la fin de game
        {
            if($score >= 20)
            {
                self::DbQuery("UPDATE player set player_end = 1 WHERE player_id={$id}");

                self::notifyAllPlayers( 'message', clienttranslate('${player_name} reaches 20 points and triggers the end of the game (at the end of the turn)'),
                array(
                    'player_name' => $player_name,
                        
                ));

                $count_players = count(self::getObjectListFromDB( "SELECT player_id FROM player", true )); 
                $no = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE player_id={$id}");

                if($no == $count_players)
                {
                    game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 
                    // END GAME
                    game::$instance->End();
                }

            }

            elseif ($reservetoken == 0)
            {
                self::DbQuery("UPDATE player set player_end = 1 WHERE player_id={$id}");

                self::notifyAllPlayers( 'message', clienttranslate('${player_name} places the last cocktail token and triggers the end of the game (at the end of the turn)'),
                array(
                    'player_name' => $player_name,
                        
                ));

                $count_players = count(self::getObjectListFromDB( "SELECT player_id FROM player", true )); 
                $no = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE player_id={$id}");

                if($no == $count_players)
                {
                    game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 
                    // END GAME
                    game::$instance->End();
                }

            }
        }

        else
        {
            //il faut verifier si le joueur est et le dernier à jouer.. si c'est le cas c'est un end game
            $count_players = count(self::getObjectListFromDB( "SELECT player_id FROM player", true )); 
            $no = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE player_id={$id}");

            if($no == $count_players)
            {
                game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 
                // END GAME
                game::$instance->End();
            }   


        }

    }


}

// Stats turns

    function updateNbTurns()
    {
        $player_id = self::getActivePlayerId();
        $this->incStat(1, 'turns_number', $player_id);
        if (self::getPlayerNoById($player_id) == 1) {
            $this->incStat(1, 'turns_number');
        }
    }

//END

    function End()
    {
        $players = self::getObjectListFromDB( "SELECT player_id FROM player", true );
        $player_pina = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_pina=1");

        foreach($players as $player)
        {
            $score = self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id={$player}");
            $pina = self::getUniqueValueFromDB("SELECT player_pina FROM player WHERE player_id={$player}");
            $this->setStat($score, 'score', $player);
            $this->setStat($pina, 'pina', $player);

        }

        if($player_pina != null)
        {
            self::DbQuery("UPDATE player SET player_score = 0");
            self::DbQuery("UPDATE player SET player_score = 1 WHERE player_id={$player_pina}");

        }

        else
        {

            $wins = self::getObjectListFromDB( "SELECT player_id id, player_score score, player_positionplace place FROM player WHERE player_score = (SELECT MAX(player_score) FROM player)" );
            //self::DbQuery("UPDATE player SET player_score = 0");
            
            if(count($wins) >= 2)
            {
                // foreach($wins as $win)
                // {
                //     self::DbQuery("UPDATE player SET player_score = 1 WHERE player_id={$win['id']}");
                // }

                // Trouver la ligne avec le place max et place le plus haut
                $idWin= $wins[array_search(max(array_column($wins, 'place')), array_column($wins, 'place'))]['id'];
                self::DbQuery("UPDATE player set player_score_aux = 1 WHERE player_id={$idWin}");
                
            }

            // else{
            //     self::DbQuery("UPDATE player SET player_score = 1 WHERE player_id={$wins[0]['id']}");
            // }
        }
               
        game::$instance->majScore();
        $this->gamestate->nextState('end');


}

/// TOKEN POUR LOG

function getLogsType($color) {
    
        if ($color == 'f18400') {
            return "<div class='token_log token_log_1' title=''></div>";
        }
        if ($color == '542583') {
            return "<div class='token_log token_log_2' title=''></div>";
        }
        if ($color == 'bbbd02') {
            return "<div class='token_log token_log_3' title=''></div>";
        }
        if ($color == 'e84041') {
            return "<div class='token_log token_log_4' title=''></div>";
        }
        if ($color == '0') {
            return "";
        }
}


///////////////////////////////////////////////////////////////////////////////// 
//     _____  _                                    _   _                 
//    |  __ \| |                                  | | (_)                
//    | |__) | | __ _ _   _  ___ _ __    __ _  ___| |_ _  ___  _ __  ___ 
//    |  ___/| |/ _` | | | |/ _ \ '__|  / _` |/ __| __| |/ _ \| '_ \/ __|
//    | |    | | (_| | |_| |  __/ |    | (_| | (__| |_| | (_) | | | \__ \
//    |_|    |_|\__,_|\__, |\___|_|     \__,_|\___|\__|_|\___/|_| |_|___/
//                     __/ |                                             
//                    |___/                                              
/////////////////////////////////////////////////////////////////////////////////


    public function actSelect(string $arg1)
    {
    
        self::checkArgs($arg1);        
        
        $pending =  self::getObjectFromDB( "SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=".$pending['id']);
        $this->gamestate->nextState( 'next');
        
    }

    public function actButton(string $arg1)
    {

        self::checkArgs($arg1);       
        
        $pending =  self::getObjectFromDB( "SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=".$pending['id']);
        $this->gamestate->nextState( 'next');
        
    }

    public function actBlock(string $arg1, string $arg2)
    {

        self::checkArgs($arg1);
        
        if ($arg2 != null){
            $explode = explode('_', $arg2);

            for ($i = 1; $i <=5; $i++)
            {
                $diceblock = 'blockrolldice'.$i;

                if(in_array($i, $explode))
                {
                    self::DbQuery("UPDATE dice set {$diceblock} = 1");
                }

                else {
                    self::DbQuery("UPDATE dice set {$diceblock} = 0");
                }
            }

           
        }

        else {

            for ($i = 1; $i <=5; $i++)
            {
                $diceblock = 'blockrolldice'.$i;
                self::DbQuery("UPDATE dice set {$diceblock} = 0");
            }
            
        }

        $blocked = self::getObjectListFromDB( "SELECT blockrolldice1 block1, blockrolldice2 block2, blockrolldice3 block3, blockrolldice4 block4, blockrolldice5 block5 FROM dice WHERE id = 1" );
        $block = [intval($blocked[0]['block1']), intval($blocked[0]['block2']), intval($blocked[0]['block3']), intval($blocked[0]['block4']) ,intval($blocked[0]['block5'])];
        for ($i = 1; $i <=5; $i++)
        {
            game::$instance->notifyAllPlayers(
                        'displayblock',
                        '',
                        array(
                            'dice' => $i,
                            'block' => $block[$i -1]
                        )
            );
        }
        
                
        $pending =  self::getObjectFromDB( "SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=".$pending['id']);
        $this->gamestate->nextState( 'next');
        
    }

///////////////////////////////////////////////////////////////////////////////// 
//     _____                             _        _                                                    _       
//    / ____|                           | |      | |                                                  | |      
//    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _ _ __ __ _ _   _ _ __ ___   ___ _ __ | |_ ___ 
//    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` | '__/ _` | | | | '_ ` _ \ / _ \ '_ \| __/ __|
//    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | | | (_| | |_| | | | | | |  __/ | | | |_\__ \
//     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|_|  \__, |\__,_|_| |_| |_|\___|_| |_|\__|___/
//                                                                    __/ |                                   
//                                                                   |___/                                    
///////////////////////////////////////////////////////////////////////////////// 


    public function argPlayerTurn()
    {
        $pending =  self::getObjectFromDB( "SELECT* FROM pending order by id desc limit 1");
        $arg = $this->callPending($pending, false);
    
        return $arg;
    }


///////////////////////////////////////////////////////////////////////////////// 
//      _____                            _        _                    _   _                 
//     / ____|                          | |      | |                  | | (_)                
//    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _  ___| |_ _  ___  _ __  ___ 
//    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` |/ __| __| |/ _ \| '_ \/ __|
//    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | (__| |_| | (_) | | | \__ \
//     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|\___|\__|_|\___/|_| |_|___/
//                                                                                       
/////////////////////////////////////////////////////////////////////////////////     


public function callPending($pending, $execute, $arg1 = null, $arg2 = null)
{
    
        $obj = $this;
        if($pending['player_id'] != null)
        {
            $obj = new Pending($pending['player_id']);
        }
        
        $fname ="";
        if(!$execute)
        {
            $fname .= "arg";
        }
        $fname .= $pending['function'];
        
        $ret = null;
        if(method_exists($obj, $fname))
        {
            $ret = $obj->$fname($pending['arg'], $pending['arg2'], $arg1, $arg2);
        }
    
    return $ret;
}


public function stPending() {
   
   $pending =  self::getObjectFromDB( "SELECT * FROM pending order by id desc limit 1");
   if($pending == null)
   {
        //$this->endGame();
        $this->gamestate->nextState( 'end' ); 
   }
   else
   {
       $args = $this->callPending($pending, false);

       
       if($pending['player_id'] != self::getActivePlayerId())
            {          
               

                //change active player      
                $this->gamestate->changeActivePlayer( $pending['player_id']);    
                $this->gamestate->nextState( 'same' );
            }
              
       else if($args == null || (count($args['selectable']) == 0 && count($args['buttons']) == 0))
       {
           //no args required, execute
           $this->callPending($pending, true);
           self::DbQuery("delete from pending where id=".$pending['id']);
           $this->gamestate->nextState( 'same' );  
       }
       
       else
       {
           
           $this->gamestate->nextState( 'player' ); 
       }            
   }
   
}

///////////////////////////////////////////////////////////////////////////////// 
//     _____  ____                                    _      
//    |  __ \|  _ \                                  | |     
//    | |  | | |_) |  _   _ _ __   __ _ _ __ __ _  __| | ___ 
//    | |  | |  _ <  | | | | '_ \ / _` | '__/ _` |/ _` |/ _ \
//    | |__| | |_) | | |_| | |_) | (_| | | | (_| | (_| |  __/
//    |_____/|____/   \__,_| .__/ \__, |_|  \__,_|\__,_|\___|
//                         | |     __/ |                     
//                         |_|    |___/                      
/////////////////////////////////////////////////////////////////////////////////  


    public function upgradeTableDb($from_version)
    {

    }


    

/////////////////////////////////////////////////////////////////////////////////
//    ______               _     _      
//   |___  /              | |   (_)     
//      / / ___  _ __ ___ | |__  _  ___ 
//     / / / _ \| '_ ` _ \| '_ \| |/ _ \
//    / /_| (_) | | | | | | |_) | |  __/
//   /_____\___/|_| |_| |_|_.__/|_|\___|
//                                   
/////////////////////////////////////////////////////////////////////////////////     

    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($state_name) {
                default:
                {
                    $player_id = $this->getActivePlayerId();
    	            self::DbQuery("delete from pending where player_id = {$player_id}");
                    $this->gamestate->nextState("end");
                    break;
                }
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state["type"] === "multipleactiveplayer") {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            $this->gamestate->nextState("end");
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }
}
