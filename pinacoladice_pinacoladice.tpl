{OVERALL_GAME_HEADER}




<div id="dice_content">
    <div id="scene_1" class="scene">
        <div id="blockdice1" class="blockdice"></div>
        <div class="dice" id="dice1">
          <div class="face face1"></div>
          <div class="face face2"></div>
          <div class="face face3"></div>
          <div class="face face4"></div>
          <div class="face face5"></div>
          <div class="face face6"></div>
        </div>
    </div>

    <div id="scene_2" class="scene">
    <div id="blockdice2" class="blockdice"></div>
      <div class="dice" id="dice2">
        <div class="face face1"></div>
        <div class="face face2"></div>
        <div class="face face3"></div>
        <div class="face face4"></div>
        <div class="face face5"></div>
        <div class="face face6"></div>
      </div>
    </div>

    <div id="scene_3" class="scene">
    <div id="blockdice3" class="blockdice"></div>
      <div class="dice" id="dice3">
        <div class="face face1"></div>
        <div class="face face2"></div>
        <div class="face face3"></div>
        <div class="face face4"></div>
        <div class="face face5"></div>
        <div class="face face6"></div>
      </div>
    </div>

    <div id="scene_4" class="scene">
    <div id="blockdice4" class="blockdice"></div>
      <div class="dice" id="dice4">
        <div class="face face1"></div>
        <div class="face face2"></div>
        <div class="face face3"></div>
        <div class="face face4"></div>
        <div class="face face5"></div>
        <div class="face face6"></div>
      </div>
    </div>
    
    <div id="scene_5" class="scene">
    <div id="blockdice5" class="blockdice"></div>
      <div class="dice" id="dice5">
        <div class="face face1"></div>
        <div class="face face2"></div>
        <div class="face face3"></div>
        <div class="face face4"></div>
        <div class="face face5"></div>
        <div class="face face6"></div>
      </div>
    </div>

  </div>

  <div id="board_id">

    <div id="carre11" class="carre"></div>
    <div id="carre12" class="carre"></div>
    <div id="carre13" class="carre"></div>
    <div id="carre14" class="carre"></div>
    <div id="carre21" class="carre"></div>
    <div id="carre22" class="carre"></div>
    <div id="carre23" class="carre"></div>
    <div id="carre24" class="carre"></div>
    <div id="carre31" class="carre"></div>
    <div id="carre32" class="carre"></div>
    <div id="carre33" class="carre"></div>
    <div id="carre34" class="carre"></div>
    <div id="carre41" class="carre"></div>
    <div id="carre42" class="carre"></div>
    <div id="carre43" class="carre"></div>
    <div id="carre44" class="carre"></div>

    <div id="overlay_modal"></div>
  
    <div id="ModalHelpHappy" class="ModalHelpHappy">

        <div id="croix"></div>

        <div id="image_modal"></div>
      
        <div class="help_zone1 help_zone">
          <div class="help">
            <div class="dice_help_1"></div>
            <div id="descritpion_help_1" class="description"></div>
          </div>
          <div class="help">
            <div class="dice_help_2"></div>
            <div id="descritpion_help_2" class="description"></div>
          </div>
          <div class="help">
            <div class="dice_help_3"></div>
            <div id="descritpion_help_3" class="description"></div>
          </div> 
        </div> 

        <div class="help_zone2 help_zone">
          <div class="help">
            <div class="dice_help_4"></div>
            <div id="descritpion_help_4" class="description"></div>
          </div>
          <div class="help">
            <div class="dice_help_5"></div>
            <div id="descritpion_help_5" class="description"></div>
          </div>
          <div class="help">
            <div class="dice_help_6"></div>
            <div id="descritpion_help_6" class="description"></div>
          </div>
        </div>
      

    </div>
  
  
  </div>

  







<script type="text/javascript">

var jstpl_bockA='<div id="bock_${id}" class="bockA" style="background-position-x: ${x}%; background-position-y: ${y}%;"></div>';
var jstpl_bockB='<div id="bock_${id}" class="bockB" style="background-position-x: ${x}%; background-position-y: ${y}%;"></div>';

var jstpl_bockA_2='<div id="bock_${id}" class="bockA_2" style="background-position-x: ${x}%; background-position-y: ${y}%;"></div>';
var jstpl_bockB_2='<div id="bock_${id}" class="bockB_2" style="background-position-x: ${x}%; background-position-y: ${y}%;"></div>';

var jstpl_flipbock='<div id="flipbock_${id}" class="${class} rotate" style="background-position-x: ${x}%; background-position-y: ${y}%;"></div>';

var jstpl_score1='<div id="score1_${id}" class="score1 score" style="top: ${x}%; left: ${y}%;"></div>';
var jstpl_score2='<div id="score2_${id}" class="score2 score" style="top: ${x}%; left: ${y}%;"></div>';

var jstpl_flipscore1='<div id="flipscore1_${id}" class="score1 score" style="top: ${x}%; left: ${y}%;"></div>';
var jstpl_flipscore2='<div id="flipscore2_${id}" class="score2 score" style="top: ${x}%; left: ${y}%;"></div>';

var jstpl_token='<div id="token_${type}_${player}" class="token tokenhover" style="background-position-x: ${x}%;"></div>';
var jstpl_reservetoken='<div id="reservetoken_${nb}_${player}" class="token" style="background-position-x: ${x}%;"></div>';

var jstpl_reservetokencontainer='<div id="reservetokencontainer_${id}" class="reservetokencontainer"></div>';

var jstpl_firstplayercontainer='<div id="firstplayercontainer_${id}" class="firstplayercontainer"></div>';
var jstpl_firstplayer='<div id="firstplayer" class="first_${mode}"></div>';

var jstpl_animScore='<div class="animScore">${score}</div>';

var jstpl_animPina='<div id="pina"></div>';

var jstpl_player_board_title='<div id="player_board_title"><div id="titre_${mode}"></div></div>';

</script>  

{OVERALL_GAME_FOOTER}