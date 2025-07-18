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
    
    function argNormalTurn($parg1, $parg2) // DICEE 1
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectable_dice"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must roll the dice');
        $ret['titleyou'] = clienttranslate('${you} must roll the dice');

        for($i=1; $i<=5; $i++)
        {
            $ret["selectable_dice"][] = 'dice'.$i;
        }

        $ret['buttons'][]='continue';
        
        
        
        return $ret;
    }

    function NormalTurn($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == 'continue')
        {
            
            game::$instance->notifyAllPlayers(
                    'dice',
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        
                    )
                );
            game::$instance->addPending($this->player_id, "NormalTurn");
        }
        else
        {
            game::$instance->addPendingFirst($this->player_id, "NormalTurn");
        }
        
    }
}