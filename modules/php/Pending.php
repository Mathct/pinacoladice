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
                '',
                array(
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'roll' => $result,
                    'block' => $block,
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
        $ret['title'] = clienttranslate('${actplayer} must roll the dice');
        $ret['titleyou'] = clienttranslate('First Roll: ${you} can block or unblock dice and re-roll');

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
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'roll' => $result,
                        'block' => $block,
                    )
                );
            
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPending($this->player_id, "Roll3");
        }
        
        else
        {

            
        }
        
    }

    function argRoll3($parg1, $parg2) // DICE 3
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must roll the dice');
        $ret['titleyou'] = clienttranslate('2nd Roll: ${you} can block or unblock dice and re-roll');

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
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'roll' => $result,
                        'block' => $block,
                    )
                );
            
            

            
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPending($this->player_id, "Last");
        }
        
        else
        {
           
        }
        
    }

    function argLast($parg1, $parg2) // DICE 3
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must roll the dice');
        $ret['titleyou'] = clienttranslate('3rd Roll: ${you} blabla');


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


        $ret['buttons'][]='continue';
        
        
        
        return $ret;
    }

    function Last($parg1, $parg2, $varg1, $varg2)
    {                   

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

            
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
    }
        
       
        
    








}