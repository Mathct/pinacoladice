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
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
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
            /*"my_first_global_variable" => 10,
            "my_second_global_variable" => 11,
            "my_first_game_variant" => 100,
            "my_second_game_variant" => 101,*/
        ]);  
        
        
        self::$instance = $this; // ATTENTION

        
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

        
        self::DbQuery("INSERT INTO dice () VALUES ()");

                
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
        "SELECT `player_id` `id`, `player_score` `score` FROM `player`"
    );

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
    // 2 = 2 paires
    // 3 = brelan
    // 4 = full
    // 5 = carre
    // 6 = yam's
    // 7 = petite suite
    // 8 = grande suite
    // 9 = tous les dés pairs
    // 10 = tous les dés impairs
    // 11 = somme <= 9
    // 12 = somme = 12,13,14
    // 13 = somme = 22,23,24
    // 14 = somme >= 26

    // 1 paire
    foreach ($dice as $val) {
        if (array_count_values($dice)[$val] >= 2) {
            if(!in_array(1, $result))
            $result[] = 1;
            
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
                $result[] = 2; // Exemple : on ajoute 2 pour "double paire"
            }
        }




    //brelan
    foreach ($dice as $val) {
        if (array_count_values($dice)[$val] >= 3) {
            if(!in_array(3, $result))
            $result[] = 3;
            
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
        $result[] = 4;
    }

    //carre
    foreach ($dice as $val) {
        if (array_count_values($dice)[$val] >= 4) {
            if(!in_array(5, $result))
            $result[] = 5;

            if(!in_array(2, $result))
            $result[] = 2;
            
        }
    }

   

    //yams
    foreach ($dice as $val) {
        if (array_count_values($dice)[$val] >= 5) {
            if(!in_array(6, $result))
            $result[] = 6;

            if(!in_array(5, $result))
            $result[] = 5;

            if(!in_array(4, $result))
            $result[] = 4;

            if(!in_array(3, $result))
            $result[] = 3;

            if(!in_array(2, $result))
            $result[] = 2;

            if(!in_array(1, $result))
            $result[] = 1;
            
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

        if (!in_array(7, $result)) $result[] = 7; // petite suite
        if (!in_array(8, $result)) $result[] = 8; // grande suite
    } else {
        // sinon, on cherche une suite de 4 consécutifs
        for ($i = 0; $i <= $count - 4; $i++) {
            $slice = array_slice($unique, $i, 4);
            if ($slice[3] - $slice[0] == 3 &&
                $slice[1] - $slice[0] == 1 &&
                $slice[2] - $slice[1] == 1 &&
                $slice[3] - $slice[2] == 1) {

                if (!in_array(7, $result)) $result[] = 7; // petite suite
                
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
        $result[] = 9;
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
        $result[] = 10;
    }

    //sommes

    $sum = array_sum($dice);

    // somme <= 9 → code 11
    if ($sum <= 9 && !in_array(11, $result)) {
        $result[] = 11;
    }

    // somme = 12, 13, 14 → code 12
    if (in_array($sum, [12, 13, 14]) && !in_array(12, $result)) {
        $result[] = 12;
    }

    // somme = 22, 23, 24 → code 13
    if (in_array($sum, [22, 23, 24]) && !in_array(13, $result)) {
        $result[] = 13;
    }

    // somme >= 26 → code 14
    if ($sum >= 26 && !in_array(14, $result)) {
        $result[] = 14;
    }

    sort($result);
    if(count($result) == 0)
    {
       $result[] = 0;
    }
    return $result;
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
