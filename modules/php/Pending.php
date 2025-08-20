<?php

namespace Bga\Games\pinacoladice;   // ATTENTION NOM DU JEU
use APP_GameClass;

require_once 'actions/Actions.php'; // Inclure le fichier contenant les fonctions

class Pending extends APP_GameClass
{
    use ActionsTrait; // ATTENTION

    public function __construct($player_id)
    {
        $this->player_id = $player_id;
        $p = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$player_id}");        
        $this->player_no = $p['player_no'];
        $this->player_id = $p['player_id'];
        $this->player_name = $p['player_name'];
        $this->player_score = $p['player_score'];
        $this->player_color = $p['player_color'];

        // GAME MODE
        $this->game_mode = game::$instance->getGameStateValue('game_mode');

        /// PREFERENCE DE CONFIRMATION
        //$this->player_pref_confirm = game::$instance->getUniqueValueFromDB("SELECT pgp_value FROM bga_user_preferences WHERE pgp_player='{$this->player_id}' AND pgp_preference_id = 100");
    }
    
    function argNormalTurn($parg1, $parg2) // DICE 1
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must roll the dice');
        $ret['titleyou'] = clienttranslate('${you} must roll the dice');

        $bockoccupedbyplayer = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE score1 = '{$this->player_id}' OR score2 = '{$this->player_id}'", true );
        foreach($bockoccupedbyplayer as $bockoccuped)
        {
            $ret["selected"][] = 'bock_'.$bockoccuped;
        }

        $ret['buttons'][]='roll';

                
        return $ret;
    }

    function NormalTurn($parg1, $parg2, $varg1, $varg2)
    {
        
        self::DbQuery("UPDATE dice set showdice = 1");
        $result = [];
        $block = [0, 0, 0, 0, 0];
        for($i =1; $i<=5;  $i++)
        {
            $rand = bga_rand(1, 6);
            $result[] = $rand;
            $dice = 'dice'.$i;
            self::DbQuery("UPDATE dice set {$dice} = $rand");

        }
        
        
        game::$instance->notifyAllPlayers(
                'rolldice',
                clienttranslate('${player_name} rolls the dice (First Roll)'),
                array(
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'roll' => $result,
                    'block' => $block,
                    'happy' => 0
                )
            );
        
        game::$instance->giveExtraTime($this->player_id);
        game::$instance->addPending($this->player_id, "Roll2");
        
        
    }


    function argRoll2($parg1, $parg2) // DICE 2
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["noselectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must place a cocktail token on a coaster or roll the dice');
        
        $bockoccupedbyplayer = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE score1 = '{$this->player_id}' OR score2 = '{$this->player_id}'", true );
        foreach($bockoccupedbyplayer as $bockoccuped)
        {
            $ret["selected"][] = 'bock_'.$bockoccuped;
        }

        //// dice result

        $resultdice = [];
        
        for($i = 1; $i <= 5; $i++)
        {
            $dice = 'dice'.$i;
            $resultdice[] = self::getUniqueValueFromDB("SELECT {$dice} FROM dice WHERE id = 1");

        }
        
        $combinaisons = game::$instance->Result($resultdice);
        
        for($i=1; $i<=5; $i++)
        {
            $ret["selectable_dice"][] = 'dice'.$i;
        }

        $allBocks = self::getObjectListFromDB( "SELECT card_type type, card_type_arg type_arg, score1 score1, score2 score2 FROM bocks WHERE card_location = 'board'");
        $allBocksOnBoard = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE card_location ='board'", true );

        //// match combinaison

        $matchingCombi = [];
        foreach (game::$instance->_BOCK_A as $index => $data) {
            if ((in_array($data['dice'], $combinaisons))&&(in_array($index, $allBocksOnBoard))) {

                foreach($allBocks as $bock)
                {

                    if($bock['type'] == $index)
                    {
                    
                        if($bock['type_arg'] == 1)
                        {                            
                            if($bock['score1'] == 0)
                            {
                                $matchingCombi[] = $index;
                            }
                        }

                        if($bock['type_arg'] == 2)
                        {
                            if((($bock['score1'] == 0)||($bock['score2'] == 0))&&($bock['score1'] != $this->player_id)&&($bock['score2'] != $this->player_id))
                            {
                                $matchingCombi[] = $index;
                            }
                        }
                    }
                    
                }
                
                
            }
        }
      
       
        foreach ($matchingCombi as $match)
        {
            $ret["selectable"][] = 'bock_'.$match;
        }

        //// no match combinaison
        
        $noselectable = array_diff($allBocksOnBoard, $matchingCombi);
        foreach ($noselectable as $bock)
        {
            $ret["noselectable"][] = 'bock_'.$bock;
        }


        if(count($ret["selectable"]) == 0)
        {
            $ret['titleyou'] = clienttranslate('First Roll: ${you} must block/unblock dice and re-roll');
        }

        else
        {
            $ret['titleyou'] = clienttranslate('First Roll: ${you} must place a cocktail token on a coaster or block/unblock dice and re-roll');
        }

        $ret['buttons'][]='block';
        
        
        
        return $ret;
    }

    function Roll2($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == 'block')
        {          
            $result = [];
            $index = 0;
            $blocked = self::getObjectListFromDB( "SELECT blockrolldice1 block1, blockrolldice2 block2, blockrolldice3 block3, blockrolldice4 block4, blockrolldice5 block5 FROM dice WHERE id = 1" );
            $block = [intval($blocked[0]['block1']), intval($blocked[0]['block2']), intval($blocked[0]['block3']), intval($blocked[0]['block4']) ,intval($blocked[0]['block5'])];
            

            foreach ($block as $etat)
            {
                $dice = 'dice'.($index+1);

                if($etat == 0)
                {
                    $rand = bga_rand(1, 6);
                    $result[] = $rand;
                    self::DbQuery("UPDATE dice set {$dice} = $rand");
                }

                if($etat == 1)
                {
                    $result[] = self::getUniqueValueFromDB("SELECT {$dice} FROM dice WHERE id = 1");
                }

                $index++;
                
            }
          
            
            
            game::$instance->notifyAllPlayers(
                    'rolldice',
                    clienttranslate('${player_name} rolls the dice (2nd Roll)'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'roll' => $result,
                        'block' => $block,
                        'happy' => 0
                    )
                );
            
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPending($this->player_id, "Roll3");
        }
        
        else
        {
            $explode = explode('_', $varg1);
            $type_arg = self::getUniqueValueFromDB("SELECT card_type_arg FROM bocks WHERE card_type={$explode[1]}");
            $score1 = self::getUniqueValueFromDB("SELECT score1 FROM bocks WHERE card_type={$explode[1]}");
            $score2 = self::getUniqueValueFromDB("SELECT score2 FROM bocks WHERE card_type={$explode[1]}");
            
            $positionscore = 0;
            $score = 0;

            if($type_arg == 1)
            {
                self::DbQuery("UPDATE bocks SET score1 = {$this->player_id} WHERE card_type={$explode[1]}");
                $positionscore = 1;
                $score = game::$instance->_BOCK_A[$explode[1]]['score1'];
            }

            if($type_arg == 2)
            {
                if($score2 == 0)
                {
                    self::DbQuery("UPDATE bocks SET score2 = {$this->player_id} WHERE card_type={$explode[1]}");
                    $positionscore = 2;
                    $score = game::$instance->_BOCK_B[$explode[1]]['score2'];
                }

                else
                {
                    self::DbQuery("UPDATE bocks SET score1 = {$this->player_id} WHERE card_type={$explode[1]}");
                    $positionscore = 1;
                    $score = game::$instance->_BOCK_B[$explode[1]]['score1'];
                }
            }

            $reserveToken = self::getUniqueValueFromDB("SELECT player_token FROM player WHERE player_id={$this->player_id}");
            self::DbQuery("UPDATE player SET player_token = player_token - 1 WHERE player_id={$this->player_id}");
            self::DbQuery("UPDATE player SET player_score = player_score + $score WHERE player_id={$this->player_id}");
            

            game::$instance->notifyAllPlayers(
                    'moveToken',
                    clienttranslate('${player_name} places ${log} on a coaster (${combi})'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'reserve_token' => $reserveToken,
                        'bock' => $explode[1],
                        'score_position' => $positionscore,
                        'log' => game::$instance->getLogsType($this->player_color),
                        'combi' =>    [
                        'log' => '${name}',
                        'args' => ['name' => game::$instance->_BOCK_A[$explode[1]]['name'], 'i18n' => ['name']]
                        ],
                    )
            );

            game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 

            game::$instance->positionPlace($this->player_id);
            $adj = game::$instance->adjScore($this->player_id, $explode[1]);
            $finalscore = $score + $adj;

            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} scores ${pv} points'),
                    array(
                        'player_name' => $this->player_name,
                        'pv' => $finalscore,
                        
                    )
            );

            game::$instance->notifyAllPlayers(
                    'animScore',
                    '',
                    array(
                       'bock' => $explode[1],
                       'score' => $score

                    )
            );

            game::$instance->majScore();
            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");


            
        }
        
    }

    function argRoll3($parg1, $parg2) // DICE 3
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must place a cocktail token on a coaster or roll the dice');
        

        $bockoccupedbyplayer = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE score1 = '{$this->player_id}' OR score2 = '{$this->player_id}'", true );
        foreach($bockoccupedbyplayer as $bockoccuped)
        {
            $ret["selected"][] = 'bock_'.$bockoccuped;
        }

        //// dice result

        $resultdice = [];
        
        for($i = 1; $i <= 5; $i++)
        {
            $dice = 'dice'.$i;
            $resultdice[] = self::getUniqueValueFromDB("SELECT {$dice} FROM dice WHERE id = 1");

        }
        
        $combinaisons = game::$instance->Result($resultdice);
        
        for($i=1; $i<=5; $i++)
        {
            $ret["selectable_dice"][] = 'dice'.$i;
        }

        $allBocks = self::getObjectListFromDB( "SELECT card_type type, card_type_arg type_arg, score1 score1, score2 score2 FROM bocks WHERE card_location = 'board'");
        $allBocksOnBoard = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE card_location ='board'", true );

        //// match combinaison

        $matchingCombi = [];
        foreach (game::$instance->_BOCK_A as $index => $data) {
            if ((in_array($data['dice'], $combinaisons))&&(in_array($index, $allBocksOnBoard))) {

                foreach($allBocks as $bock)
                {

                    if($bock['type'] == $index)
                    {
                    
                        if($bock['type_arg'] == 1)
                        {                            
                            if($bock['score1'] == 0)
                            {
                                $matchingCombi[] = $index;
                            }
                        }

                        if($bock['type_arg'] == 2)
                        {
                            if((($bock['score1'] == 0)||($bock['score2'] == 0))&&($bock['score1'] != $this->player_id)&&($bock['score2'] != $this->player_id))
                            {
                                $matchingCombi[] = $index;
                            }
                        }
                    }
                    
                }
                
                
            }
        }
      
       
        foreach ($matchingCombi as $match)
        {
            $ret["selectable"][] = 'bock_'.$match;
        }

        //// no match combinaison
        
        $noselectable = array_diff($allBocksOnBoard, $matchingCombi);
        foreach ($noselectable as $bock)
        {
            $ret["noselectable"][] = 'bock_'.$bock;
        }

        if(count($ret["selectable"]) == 0)
        {
            $ret['titleyou'] = clienttranslate('2nd Roll: ${you} must block/unblock dice and re-roll');
        }

        else
        {
            $ret['titleyou'] = clienttranslate('2nd Roll: ${you} must place a cocktail token on a coaster or block/unblock dice and re-roll');
        }



        $ret['buttons'][]='block';
        
        
        
        return $ret;
    }

    function Roll3($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == 'block')
        {          
            $result = [];
            $index = 0;
            $blocked = self::getObjectListFromDB( "SELECT blockrolldice1 block1, blockrolldice2 block2, blockrolldice3 block3, blockrolldice4 block4, blockrolldice5 block5 FROM dice WHERE id = 1" );
            $block = [intval($blocked[0]['block1']), intval($blocked[0]['block2']), intval($blocked[0]['block3']), intval($blocked[0]['block4']) ,intval($blocked[0]['block5'])];
            

            foreach ($block as $etat)
            {
                $dice = 'dice'.($index+1);

                if($etat == 0)
                {
                    $rand = bga_rand(1, 6);
                    $result[] = $rand;
                    self::DbQuery("UPDATE dice set {$dice} = $rand");
                }

                if($etat == 1)
                {
                    $result[] = self::getUniqueValueFromDB("SELECT {$dice} FROM dice WHERE id = 1");
                }

                $index++;
                
            }
          
            
            
            game::$instance->notifyAllPlayers(
                    'rolldice',
                    clienttranslate('${player_name} rolls the dice (3rd Roll)'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'roll' => $result,
                        'block' => $block,
                        'happy' => 0
                    )
                );
            
            

            
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPending($this->player_id, "Last");
        }
        
        else
        {
            $explode = explode('_', $varg1);
            $type_arg = self::getUniqueValueFromDB("SELECT card_type_arg FROM bocks WHERE card_type={$explode[1]}");
            $score1 = self::getUniqueValueFromDB("SELECT score1 FROM bocks WHERE card_type={$explode[1]}");
            $score2 = self::getUniqueValueFromDB("SELECT score2 FROM bocks WHERE card_type={$explode[1]}");
            
            $positionscore = 0;
            $score = 0;

            if($type_arg == 1)
            {
                self::DbQuery("UPDATE bocks SET score1 = {$this->player_id} WHERE card_type={$explode[1]}");
                $positionscore = 1;
                $score = game::$instance->_BOCK_A[$explode[1]]['score1'];
            }

            if($type_arg == 2)
            {
                if($score2 == 0)
                {
                    self::DbQuery("UPDATE bocks SET score2 = {$this->player_id} WHERE card_type={$explode[1]}");
                    $positionscore = 2;
                    $score = game::$instance->_BOCK_B[$explode[1]]['score2'];
                }

                else
                {
                    self::DbQuery("UPDATE bocks SET score1 = {$this->player_id} WHERE card_type={$explode[1]}");
                    $positionscore = 1;
                    $score = game::$instance->_BOCK_B[$explode[1]]['score1'];
                }
            }

            $reserveToken = self::getUniqueValueFromDB("SELECT player_token FROM player WHERE player_id={$this->player_id}");
            self::DbQuery("UPDATE player SET player_token = player_token - 1 WHERE player_id={$this->player_id}");
            self::DbQuery("UPDATE player SET player_score = player_score + $score WHERE player_id={$this->player_id}");
            

            game::$instance->notifyAllPlayers(
                    'moveToken',
                    clienttranslate('${player_name} places ${log} on a coaster (${combi})'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'reserve_token' => $reserveToken,
                        'bock' => $explode[1],
                        'score_position' => $positionscore,
                        'log' => game::$instance->getLogsType($this->player_color),
                        'combi' =>    [
                        'log' => '${name}',
                        'args' => ['name' => game::$instance->_BOCK_A[$explode[1]]['name'], 'i18n' => ['name']]
                        ],
                    )
            );

            game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 

            game::$instance->positionPlace($this->player_id);
            $adj = game::$instance->adjScore($this->player_id, $explode[1]);
            $finalscore = $score + $adj;

            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} scores ${pv} points'),
                    array(
                        'player_name' => $this->player_name,
                        'pv' => $finalscore,
                        
                    )
            );

            game::$instance->notifyAllPlayers(
                    'animScore',
                    '',
                    array(
                       'bock' => $explode[1],
                       'score' => $score

                    )
            );

            game::$instance->majScore();
            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
           
        }
        
    }

    function argLast($parg1, $parg2) // DICE 3
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must place a cocktail token or pass');
        

        $bockoccupedbyplayer = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE score1 = '{$this->player_id}' OR score2 = '{$this->player_id}'", true );
        foreach($bockoccupedbyplayer as $bockoccuped)
        {
            $ret["selected"][] = 'bock_'.$bockoccuped;
        }

        $resultdice = [];
        for($i = 1; $i <= 5; $i++)
        {
            $dice = 'dice'.$i;
            $resultdice[] = self::getUniqueValueFromDB("SELECT {$dice} FROM dice WHERE id = 1");

        }
        
        $combinaisons = game::$instance->Result($resultdice);

        $allBocks = self::getObjectListFromDB( "SELECT card_type type, card_type_arg type_arg, score1 score1, score2 score2 FROM bocks WHERE card_location = 'board'");
        $allBocksOnBoard = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE card_location ='board'", true );

        //// match combinaison

        $matchingCombi = [];
        foreach (game::$instance->_BOCK_A as $index => $data) {
            if ((in_array($data['dice'], $combinaisons))&&(in_array($index, $allBocksOnBoard))) {

                foreach($allBocks as $bock)
                {

                    if($bock['type'] == $index)
                    {
                    
                        if($bock['type_arg'] == 1)
                        {                            
                            if($bock['score1'] == 0)
                            {
                                $matchingCombi[] = $index;
                            }
                        }

                        if($bock['type_arg'] == 2)
                        {
                            if((($bock['score1'] == 0)||($bock['score2'] == 0))&&($bock['score1'] != $this->player_id)&&($bock['score2'] != $this->player_id))
                            {
                                $matchingCombi[] = $index;
                            }
                        }
                    }
                    
                }
                
                
            }
        }
      
       
        foreach ($matchingCombi as $match)
        {
            $ret["selectable"][] = 'bock_'.$match;
        }

        //// no match combinaison
        
        $noselectable = array_diff($allBocksOnBoard, $matchingCombi);
        foreach ($noselectable as $bock)
        {
            $ret["noselectable"][] = 'bock_'.$bock;
        }

        if(count($ret["selectable"]) == 0)
        {
            $ret['titleyou'] = clienttranslate('3rd Roll: ${you} cannot place a cocktail token');
            if($this->game_mode == 1)
            {
                $ret['buttons'][]='pass';
            }
            if($this->game_mode == 2)
            {
                $ret['buttons'][]='happy';
            }
        }

        else
        {
            $ret['titleyou'] = clienttranslate('3rd Roll: ${you} must place a cocktail token on a coaster');
        }


        
        
        
        
        return $ret;
    }

    function Last($parg1, $parg2, $varg1, $varg2)
    {   
        if($varg1 == "pass")
        {
            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} cannot place a cocktail token and passes'),
                    array(
                        'player_name' => $this->player_name,
                        
                        
                    )
            );

            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");

        }  

        elseif($varg1 == "happy")
        {
            
            game::$instance->addPending($this->player_id, "RollHappy");
        }
        
        else
        {
            $explode = explode('_', $varg1);
            $type_arg = self::getUniqueValueFromDB("SELECT card_type_arg FROM bocks WHERE card_type={$explode[1]}");
            $score1 = self::getUniqueValueFromDB("SELECT score1 FROM bocks WHERE card_type={$explode[1]}");
            $score2 = self::getUniqueValueFromDB("SELECT score2 FROM bocks WHERE card_type={$explode[1]}");
            
            $positionscore = 0;
            $score = 0;

            if($type_arg == 1)
            {
                self::DbQuery("UPDATE bocks SET score1 = {$this->player_id} WHERE card_type={$explode[1]}");
                $positionscore = 1;
                $score = game::$instance->_BOCK_A[$explode[1]]['score1'];
            }

            if($type_arg == 2)
            {
                if($score2 == 0)
                {
                    self::DbQuery("UPDATE bocks SET score2 = {$this->player_id} WHERE card_type={$explode[1]}");
                    $positionscore = 2;
                    $score = game::$instance->_BOCK_B[$explode[1]]['score2'];
                }

                else
                {
                    self::DbQuery("UPDATE bocks SET score1 = {$this->player_id} WHERE card_type={$explode[1]}");
                    $positionscore = 1;
                    $score = game::$instance->_BOCK_B[$explode[1]]['score1'];
                }
            }

            $reserveToken = self::getUniqueValueFromDB("SELECT player_token FROM player WHERE player_id={$this->player_id}");
            self::DbQuery("UPDATE player SET player_token = player_token - 1 WHERE player_id={$this->player_id}");
            self::DbQuery("UPDATE player SET player_score = player_score + $score WHERE player_id={$this->player_id}");
            

            game::$instance->notifyAllPlayers(
                    'moveToken',
                    clienttranslate('${player_name} places ${log} on a coaster (${combi})'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'reserve_token' => $reserveToken,
                        'bock' => $explode[1],
                        'score_position' => $positionscore,
                        'log' => game::$instance->getLogsType($this->player_color),
                        'combi' =>    [
                        'log' => '${name}',
                        'args' => ['name' => game::$instance->_BOCK_A[$explode[1]]['name'], 'i18n' => ['name']]
                        ],
                    )
            );

            game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 

            game::$instance->positionPlace($this->player_id);
            $adj = game::$instance->adjScore($this->player_id, $explode[1]);
            $finalscore = $score + $adj;

            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} scores ${pv} points'),
                    array(
                        'player_name' => $this->player_name,
                        'pv' => $finalscore,
                        
                    )
            );

            game::$instance->notifyAllPlayers(
                    'animScore',
                    '',
                    array(
                       'bock' => $explode[1],
                       'score' => $score

                    )
            );

            game::$instance->majScore();
            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");


        }

    }

    function argRollHappy($parg1, $parg2) // ROLL Happy DICE
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} rolls the Happy Hour dice');
        $ret['titleyou'] = clienttranslate('${you} roll the Happy Hour dice');

                        
        return $ret;
    }

    function RollHappy($parg1, $parg2, $varg1, $varg2)
    {
        
        game::$instance->initDiceHappy();

            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} cannot place a cocktail token and triggers Happy Hour'),
                    array(
                        'player_name' => $this->player_name,
                        
                        
                    )
            );

            $result = [];
            $index = 0;
            $blocked = self::getObjectListFromDB( "SELECT blockrolldice1 block1, blockrolldice2 block2, blockrolldice3 block3, blockrolldice4 block4, blockrolldice5 block5 FROM dice WHERE id = 1" );
            $block = [intval($blocked[0]['block1']), intval($blocked[0]['block2']), intval($blocked[0]['block3']), intval($blocked[0]['block4']) ,intval($blocked[0]['block5'])];
            

            foreach ($block as $etat)
            {
                $dice = 'dice'.($index+1);

                if($etat == 0)
                {
                    $rand = bga_rand(1, 6);
                    $result[] = $rand;
                    self::DbQuery("UPDATE dice set {$dice} = $rand");
                }

                if($etat == 1)
                {
                    $result[] = self::getUniqueValueFromDB("SELECT {$dice} FROM dice WHERE id = 1");
                }

                $index++;
                
            }
          
            
            
            game::$instance->notifyAllPlayers(
                    'rolldice',
                    clienttranslate('${player_name} rolls the dice for Happy Hour'),
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'roll' => $result,
                        'block' => $block,
                        'happy' => 1
                    )
                );

           
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPending($this->player_id, "HappyHour");
    
        
    }

    function argHappyHour($parg1, $parg2) 
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} rolls the Happy Hour dice');
        

        $result_dice = self::getUniqueValueFromDB("SELECT dice1 FROM dice WHERE id = 1");
        //$result_dice = 2;   // FORCER LE RESULTAT

        if($result_dice == 1)
        {
            $ret['titleyou'] = clienttranslate('${you} immediately lose 3 points');
            $ret['buttons'][]='continue';
        }

        if($result_dice == 2)
        {
            $ret['titleyou'] = clienttranslate('${you} must remove one of your cocktail tokens from a coaster');
        }

        if($result_dice == 3)
        {
            $ret['titleyou'] = clienttranslate('All other players immediately gain 1 point');
            $ret['buttons'][]='continue';
        }

        if($result_dice == 4)
        {
            $ret['titleyou'] = clienttranslate('${you} must move another player\'s cocktail token to a different, available space');
        }

        if($result_dice == 5)
        {
            $ret['titleyou'] = clienttranslate('${you} must flip any coaster to its other side');
        }

        if($result_dice == 6)
        {
            $ret['titleyou'] = clienttranslate('${you} immediately score 4 points');
            $ret['buttons'][]='continue';
        }
        
        

                
        return $ret;
    }

    function HappyHour($parg1, $parg2, $varg1, $varg2)
    {
        $result_dice = self::getUniqueValueFromDB("SELECT dice1 FROM dice WHERE id = 1");
        //$result_dice = 5;  // FORCER LE RESULTAT

        if($result_dice == 1)
        {
            self::DbQuery("UPDATE player SET player_score = player_score -3 WHERE player_id={$this->player_id}");
            game::$instance->notifyAllPlayers(
                    'animScoreHappyLose3',
                    '',
                    array(
                        'player_id' => $this->player_id,

                    )
            );

            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} immediately lose 3 points'),
                    array(
                        'player_name' => $this->player_name,
                                                
                    )
            );

            game::$instance->notifyAllPlayers(
                    'endhappy',
                    '',
                    array(
                        
                    )
                );
        
            game::$instance->majScore();
            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
            
        }

        elseif($result_dice == 2)
        {
            
            game::$instance->addPending($this->player_id, "Happy2");
            
        }

        elseif($result_dice == 3)
        {
            $players = self::getObjectListFromDB( "SELECT player_id FROM player WHERE player_id != '{$this->player_id}'", true );

            foreach ($players as $player) {

                self::DbQuery("UPDATE player SET player_score = player_score +1 WHERE player_id={$player}");
            }

            game::$instance->notifyAllPlayers(
                    'animScoreHappyOtherWin1',
                    '',
                    array(
                        'players_id' => $players,                

                    )
            );

             game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('All other players immediately gain 1 point'),
                    array(
                        'player_name' => $this->player_name,
                                                
                    )
            );

            game::$instance->notifyAllPlayers(
                    'endhappy',
                    '',
                    array(
                        
                    )
                );
        
            game::$instance->majScore();
            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
            
        }

        elseif($result_dice == 4)
        {
            game::$instance->notifyAllPlayers(
                    'endhappy',
                    '',
                    array(
                        
                    )
                );
        
            game::$instance->majScore();
            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
            
        }

        elseif($result_dice == 5)
        {
            game::$instance->addPending($this->player_id, "Happy5");
            
        }

        elseif($result_dice == 6)
        {
            self::DbQuery("UPDATE player SET player_score = player_score +4 WHERE player_id={$this->player_id}");

            game::$instance->notifyAllPlayers(
                    'animScoreHappyWin4',
                    '',
                    array(
                        'player_id' => $this->player_id,

                    )
            );

            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} immediately score 4 points'),
                    array(
                        'player_name' => $this->player_name,
                                                
                    )
            );

            game::$instance->notifyAllPlayers(
                    'endhappy',
                    '',
                    array(
                        
                    )
                );
        
            game::$instance->majScore();
            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
            
        }

                
        
    }


function argHappy2($parg1, $parg2) // RECUP TOKEN
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["noselectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        

        $ret["nosettimeout"] = [1];

        $bockoccupedbyplayer = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE score1 = '{$this->player_id}' OR score2 = '{$this->player_id}'", true );
        $bockinoccupedbyplayer = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE score1 != '{$this->player_id}' AND score2 != '{$this->player_id}'", true );

        foreach ($bockoccupedbyplayer as $occuped)
        {
            $ret["selectable"][] = 'bock_'.$occuped;
        }

        foreach ($bockinoccupedbyplayer as $inoccuped)
        {
            $ret["noselectable"][] = 'bock_'.$inoccuped;
        }

        if(count($ret["selectable"]) != 0)
        {
            $ret['title'] = clienttranslate('${actplayer} must remove one of their cocktail tokens from a coaster');
            $ret['titleyou'] = clienttranslate('${you} must remove one of your cocktail tokens from a coaster');

        }

        else
        {
            $ret['title'] = clienttranslate('${actplayer} has no cocktail token to remove');
            $ret['titleyou'] = clienttranslate('${you} have no cocktail token to remove');
            $ret['buttons'][]='continue';
        }

        

                
        return $ret;
    }

    function Happy2($parg1, $parg2, $varg1, $varg2)
    {

        if($varg1 == 'continue')
        {

            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} has no cocktail token to remove'),
                    array(
                        'player_name' => $this->player_name,
                                                
                    )
            );

            game::$instance->notifyAllPlayers(
                    'endhappy',
                    '',
                    array(
                        
                    )
                );
        
            game::$instance->majScore();
            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");

        }

        else
        {
            $explode = explode('_', $varg1);

            self::DbQuery("UPDATE player SET player_token = player_token +1 WHERE player_id='{$this->player_id}'");

            $idscore1 = self::getUniqueValueFromDB("SELECT score1 FROM bocks WHERE card_type = '{$explode[1]}'");
            $idscore2 = self::getUniqueValueFromDB("SELECT score2 FROM bocks WHERE card_type = '{$explode[1]}'");
            $new_nb_token = self::getUniqueValueFromDB("SELECT player_token FROM player WHERE player_id='{$this->player_id}'");

            if($idscore1  == $this->player_id)
            {
                self::DbQuery("UPDATE bocks SET score1 = 0 WHERE card_type = '{$explode[1]}'");
            }

            if($idscore2  == $this->player_id)
            {
                self::DbQuery("UPDATE bocks SET score2 = 0 WHERE card_type = '{$explode[1]}'");
            }

            $mobile = 'token_'.$explode[1].'_'.$this->player_id;

            game::$instance->notifyAllPlayers(
                    'recupToken',
                    '',
                    array(

                        'player_id' => $this->player_id,
                        'mobile' => $mobile,
                        'nb' => $new_nb_token,
                        
                    )
                );


            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} remove ${log} (from ${combi})'),
                    array(
                        'player_name' => $this->player_name,
                        'log' => game::$instance->getLogsType($this->player_color),
                        'combi' =>    [
                        'log' => '${name}',
                        'args' => ['name' => game::$instance->_BOCK_A[$explode[1]]['name'], 'i18n' => ['name']]
                        ],
                                                
                    )
            );

             game::$instance->notifyAllPlayers(
                    'endhappy',
                    '',
                    array(
                        
                    )
                );
        
            game::$instance->majScore();
            game::$instance->updateNbTurns();
            game::$instance->initDice();
            game::$instance->checkEndGame($this->player_id);
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");

        }
        
        
        
    }

    function argHappy5($parg1, $parg2) // FLIP 
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["noselectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must flip any coaster to its other side');
        $ret['titleyou'] = clienttranslate('${you} must flip any coaster to its other side');

        $ret["nosettimeout"] = [1];

        $bocks = self::getObjectListFromDB( "SELECT card_type FROM bocks WHERE card_location = 'board'", true );
        foreach($bocks as $bock)
        {
            $ret["selectable"][] = 'bock_'.$bock;
        }

               
                
        return $ret;
    }

    function Happy5($parg1, $parg2, $varg1, $varg2)
    {
        $explode = explode('_', $varg1);

        $type_arg = self::getUniqueValueFromDB("SELECT card_type_arg FROM bocks WHERE card_type = '{$explode[1]}'");
        $location_arg = self::getUniqueValueFromDB("SELECT card_location_arg FROM bocks WHERE card_type = '{$explode[1]}'");
        $new_type_arg = 0;

        if($type_arg == 1)
        {
            self::DbQuery("UPDATE bocks SET card_type_arg = 2 WHERE card_type = '{$explode[1]}'");
            $new_type_arg = 2;
        }

        elseif($type_arg == 2)
        {
            self::DbQuery("UPDATE bocks SET card_type_arg = 1 WHERE card_type = '{$explode[1]}'");
            $new_type_arg = 1;
        }

        $idscore1 = self::getUniqueValueFromDB("SELECT score1 FROM bocks WHERE card_type = '{$explode[1]}'");
        $idscore2 = self::getUniqueValueFromDB("SELECT score2 FROM bocks WHERE card_type = '{$explode[1]}'");
        $color1 = '0';
        $color2 = '0';

        if($idscore1 != 0)
        {
            $color1 = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id='{$idscore1}'");
            self::DbQuery("UPDATE bocks SET score1 = 0 WHERE card_type = '{$explode[1]}'");
            self::DbQuery("UPDATE player SET player_token = player_token +1 WHERE player_id='{$idscore1}'");
            $new_nb_token = self::getUniqueValueFromDB("SELECT player_token FROM player WHERE player_id='{$idscore1}'");

            $mobile = 'token_'.$explode[1].'_'.$idscore1;

            game::$instance->notifyAllPlayers(
                    'recupToken',
                    '',
                    array(

                        'player_id' => $idscore1,
                        'mobile' => $mobile,
                        'nb' => $new_nb_token,
                        
                    )
                );


        }

        if($idscore2 != 0)
        {
            $color2 = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id='{$idscore2}'");
            self::DbQuery("UPDATE bocks SET score2 = 0 WHERE card_type = '{$explode[1]}'");
            self::DbQuery("UPDATE player SET player_token = player_token +1 WHERE player_id='{$idscore2}'");
            $new_nb_token = self::getUniqueValueFromDB("SELECT player_token FROM player WHERE player_id='{$idscore2}'");

            $mobile = 'token_'.$explode[1].'_'.$idscore2;

            game::$instance->notifyAllPlayers(
                    'recupToken',
                    '',
                    array(

                        'player_id' => $idscore2,
                        'mobile' => $mobile,
                        'nb' => $new_nb_token,
                        
                    )
                );
            
        }

        game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} flips a coaster (${combi}) and remove ${log1}${log2}'),
                    array(
                        'player_name' => $this->player_name,
                        'log1' => game::$instance->getLogsType($color1),
                        'log2' => game::$instance->getLogsType($color2),
                        'combi' =>    [
                        'log' => '${name}',
                        'args' => ['name' => game::$instance->_BOCK_A[$explode[1]]['name'], 'i18n' => ['name']]
                        ],
                                                
                    )
        );

        // RAjouter un log pour les tokens retirés


        game::$instance->notifyAllPlayers(
                    'flip',
                    '',
                    array(
                        'type' => $explode[1],
                        'position' => $location_arg,
                        'new_type_arg' => $new_type_arg,
                                               
                    )
        );

        game::$instance->notifyAllPlayers(
                    'endhappy',
                    '',
                    array(
                        
                    )
                );

 
        game::$instance->majScore();
        game::$instance->updateNbTurns();
        game::$instance->initDice();
        game::$instance->checkEndGame($this->player_id);
        game::$instance->giveExtraTime($this->player_id);
        game::$instance->addPendingFirst($this->player_id, "NormalTurn");
  
    }
        
        
       
        
    








}