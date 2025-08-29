/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * pinacoladice implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * pinacoladice.js
 *
 * pinacoladice user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo",
    "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    getLibUrl('bga-animations', '1.x'),
],
function (dojo, declare, gamegui, counter, BgaAnimations) {   //Attention si on utilise bga-animations il faut respecer cette lui...bga-animations doit etre en 5eme position si il est declaré en 5eme position
    return declare("bgagame.pinacoladice", ebg.core.gamegui, {
        constructor: function(){
            console.log('pinacoladice constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

            
        },

        updatePlayerOrdering() {
            
            this.inherited(arguments);
            dojo.place(this.format_block('jstpl_player_board_title', {mode: this.gamedatas.mode}), 'player_boards', 'first');

            
        }, 
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

/////////////////////////////////////////////////////////////////////////////////           
//    _____                      _____        _            
//   / ____|                    |  __ \      | |           
//  | |  __  __ _ _ __ ___   ___| |  | | __ _| |_ __ _ ___ 
//  | | |_ |/ _` | '_ ` _ \ / _ \ |  | |/ _` | __/ _` / __|
//  | |__| | (_| | | | | | |  __/ |__| | (_| | || (_| \__ \
//   \_____|\__,_|_| |_| |_|\___|_____/ \__,_|\__\__,_|___/
//                                                        
/////////////////////////////////////////////////////////////////////////////////
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );

            // create the animation manager, and bind it to the `game.bgaAnimationsActive()` function
            this.animationManager = new BgaAnimations.Manager({
                animationsActive: () => this.bgaAnimationsActive(),
            });

                                   
            // TODO: Set up your game interface here, according to "gamedatas"

            this.players = gamedatas.players; // A RAJOUTER POUR MOTEUR (UTILITY METHODS)
            
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            this.setupBoard();
            this.initDice();

            
            //// CONNECTIONS CLICK
            dojo.query(".dice").connect('onclick', this, 'onSelect' )
            

            console.log( "Ending game setup" );
        },

/////////////////////////////////////////////////////////////////////////////////   
//         _____ _        _            
//        / ____| |      | |           
//       | (___ | |_ __ _| |_ ___  ___ 
//        \___ \| __/ _` | __/ _ \/ __|
//        ____) | || (_| | ||  __/\__ \
//       |_____/ \__\__,_|\__\___||___/
//                                    
/////////////////////////////////////////////////////////////////////////////////    
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName, args );

            switch( stateName )
            {
            
            case 'playerTurn':
                this.args = args.args;


                for (let sid in this.args.selectable) {
                    if (this.isCurrentPlayerActive()) {
                        if(this.args.nosettimeout)
                        {
                            dojo.query("#" + this.args.selectable[sid]).addClass("selectable");
                        }
                        else
                        {
                            setTimeout(() => {
                            dojo.query("#" + this.args.selectable[sid]).addClass("selectable");
                            }, 1500);
                        }
                    }
                        
                }

                for( var sid in this.args.selected)
                {
                    if(this.isCurrentPlayerActive())
                    {
                        dojo.query("#"+this.args.selected[sid]).addClass("selected");
                    }
                }

                

                for( var sid in this.args.selectable_dice)
                {
                    if(this.isCurrentPlayerActive())
                    {
                        dojo.query("#"+this.args.selectable_dice[sid]).addClass("selectable_dice");
                    
                    }
                }

                if(this.args.noselectable)
                {
                    for( let sid in this.args.noselectable)
                    {
                        if(this.isCurrentPlayerActive())
                        {

                            setTimeout(() => {
                                dojo.query("#"+this.args.noselectable[sid]).addClass("noselectable");
                                }, 1500);

                        }
                    }

                }

                if(this.args.selected2)
                {
                    for( let sid in this.args.selected2)
                    {
                        if(this.isCurrentPlayerActive())
                        {
                            
                            dojo.query("#"+this.args.selected2[sid]).addClass("selected2");
                           
                        }
                    }
                }



          

                if( this.isCurrentPlayerActive() )
                {
                    if(args.args.titleyou != null) 
                    {
                        $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.titleyou).replace('${you}', this.divYou()).replace(/#opponent#/g,args.args.opponent).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);
                    }
                } 
                    
                else
                {
                    if(args.args.title != null) 
                    {
                        $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.title).replace('${actplayer}', this.divActPlayer()).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);  
                    }
                }

                    
                
                break;
    
           
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
           
            dojo.query(".selected").removeClass("selected");
            dojo.query(".selected2").removeClass("selected2");
            dojo.query(".selectable_dice").removeClass("selectable_dice");

            dojo.query(".selectable").removeClass("selectable");
            
            // dojo.query(".selectable").addClass("reverse-selectable");
            dojo.query(".noselectable").addClass("reverse-noselectable");

            setTimeout(() => 
            {
            // dojo.query(".selectable").removeClass("selectable");
            dojo.query(".noselectable").removeClass("noselectable");
            // dojo.query(".reverse-selectable").removeClass("reverse-selectable");
            dojo.query(".reverse-noselectable").removeClass("reverse-noselectable");

            }, "1000");


            if(this.gamedatas.mode == 2)
            {
                const modal = document.getElementById("ModalHelpHappy");
                 modal.style.display = 'none';
            }
            
                                 
            
            switch( stateName )
            {
            
                      
           
            case 'dummy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName, args );
                      
            if( this.isCurrentPlayerActive() )
                {            
                    switch( stateName )
                    {
    
                        case "playerTurn":
                            for( var nb in args.buttons )
                            { 
                                     
                                if(args.buttons[nb] == "cancel")
                                {
                                this.addActionButton( 'cancel', _("Cancel") ,'onOpButton', null, null, 'red' );
                                }
                                if(args.buttons[nb] == "yes") 
                                {
                                this.addActionButton('btn_yes', _("Yes"), 'onOpButton', null, null, 'blue');
                                this.startActionTimer('btn_yes', 5, 1);
                                }
                                if(args.buttons[nb] == "no") 
                                {
                                this.addActionButton( 'no', _("No") ,'onOpButton', null, null, 'red' );
                                }
                                if(args.buttons[nb] == "dice") 
                                {
                                this.addActionButton( 'dice', _("Dice") ,'onOpButton', null, null, 'blue' );
                                }
                                if(args.buttons[nb] == "pass") 
                                {
                                this.addActionButton( 'pass', _("Pass") ,'onOpButton', null, null, 'red' );
                                    dojo.addClass( 'pass', 'disabled');
                                    setTimeout(() => 
                                    {
                                        dojo.removeClass( 'pass', 'disabled');
                                        this.startActionTimer('pass', 5, 1);
                                    }, "2000");
                                }
                                if(args.buttons[nb] == "continue") 
                                {
                                this.addActionButton( 'continue', _("Continue") ,'onOpButton', null, null, 'red' );
                                    dojo.addClass( 'continue', 'disabled');
                                    setTimeout(() => 
                                    {
                                        dojo.removeClass( 'continue', 'disabled');
                                        this.startActionTimer('continue', 5, 1);
                                    }, "2000");
                                }
                                if(args.buttons[nb] == "happy") 
                                {
                                this.addActionButton( 'happy', _("Happy Hour") ,'onOpButton', null, null, 'red' );
                                    dojo.addClass( 'happy', 'disabled');
                                    setTimeout(() => 
                                    {
                                        dojo.removeClass( 'happy', 'disabled');
                                        this.startActionTimer('happy', 5, 1);
                                    }, "2000");
                                }
                                if(args.buttons[nb] == "roll") 
                                {
                                this.addActionButton( 'roll', _("Roll the dice") ,'onOpButton', null, null, 'blue' );
                                }
                                if(args.buttons[nb] == "block") 
                                {
                                    this.addActionButton( 'block', _("Roll the dice") ,'onOpBlock', null, null, 'blue' );
                                    dojo.addClass( 'block', 'disabled');
                                    setTimeout(() => 
                                    {
                                        dojo.removeClass( 'block', 'disabled');
                                    }, "2000");
                                }
                            
                            }
                                      
                            
                            break;
    
    
    
                    }
                }
        },        

/////////////////////////////////////////////////////////////////////////////////         
//   _    _ _   _ _ _ _                          _   _               _     
//  | |  | | | (_) (_) |                        | | | |             | |    
//  | |  | | |_ _| |_| |_ _   _   _ __ ___   ___| |_| |__   ___   __| |___ 
//  | |  | | __| | | | __| | | | | '_ ` _ \ / _ \ __| '_ \ / _ \ / _` / __|
//  | |__| | |_| | | | |_| |_| | | | | | | |  __/ |_| | | | (_) | (_| \__ \
//   \____/ \__|_|_|_|\__|\__, | |_| |_| |_|\___|\__|_| |_|\___/ \__,_|___/
//                         __/ |                                           
//                        |___/                                            
/////////////////////////////////////////////////////////////////////////////////  

divYou : function() {
            
    var color = this.players[this.player_id].color;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + _("You") + "</span>";
    return you;
},

divActPlayer : function() {        	
    var color = this.players[this.getActivePlayerId()].color;
    var name = this.players[this.getActivePlayerId()].name;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + name + "</span>";
    return you;
},

format_string_recursive : function(log, args) {
    try {
        if (log && args && !args.processed) {
            args.processed = true;

            
        }
    } catch (e) {
        console.error(log,args,"Exception thrown", e.stack);
    }
    return this.inherited(arguments);
},
 
attachToNewParentNoDestroy: function (mobile_in, new_parent_in, relation, place_position) 
    {

        const mobile = $(mobile_in);
        const new_parent = $(new_parent_in);

        var src = dojo.position(mobile);
        if (place_position)
            mobile.style.position = place_position;
        dojo.place(mobile, new_parent, relation);
        mobile.offsetTop;//force re-flow
        var tgt = dojo.position(mobile);
        var box = dojo.marginBox(mobile);
        var cbox = dojo.contentBox(mobile);
        var left = box.l + src.x - tgt.x;
        var top = box.t + src.y - tgt.y;

        mobile.style.position = "absolute";
        mobile.style.left = left + "px";
        mobile.style.top = top + "px";
        box.l += box.w - cbox.w;
        box.t += box.h - cbox.h;
        mobile.offsetTop;//force re-flow
        return box;
    },

// TIMER sur bouton confirm

startActionTimer: function(buttonId, time, pref, autoclick = false) {
    var button = $(buttonId);
    var isReadOnly = this.isReadOnly();
    if (button == null || isReadOnly || pref == 2) {
        //debug('Ignoring startActionTimer(' + buttonId + ')', 'readOnly=' + isReadOnly, 'prefValue=' + pref);
        return;
    }

    // If confirm disabled, click on button
    if (pref == 0) {
        if (autoclick) 
            button.click();
        return;
    }

    this._actionTimerLabel = button.innerHTML;
    this._actionTimerSeconds = time;
    this._actionTimerFunction = () => {
        var button = $(buttonId);
        if (button == null) {
            this.stopActionTimer();
        } 
        else if (this._actionTimerSeconds-- > 1) {
            button.innerHTML = this._actionTimerLabel + ' (' + this._actionTimerSeconds + ')';
        } 
        else {
            //debug('Timer ' + buttonId + ' execute');
            button.click();
            this.stopActionTimer();
        }
    };
    this._actionTimerFunction();
    this._actionTimerId = window.setInterval(this._actionTimerFunction.bind(this), 1000);
    //debug('Timer #' + this._actionTimerId + ' ' + buttonId + ' start');
},

stopActionTimer() {
    if (this._actionTimerId != null) {
        //debug('Timer #' + this._actionTimerId + ' stop');
        window.clearInterval(this._actionTimerId);
        delete this._actionTimerId;
    }
},

isReadOnly: function () {
    return (
        this.isSpectator || typeof g_replayFrom != "undefined" || g_archive_mode
    );
},

//////// RESIZED

onScreenWidthChange: function () {
this.updateLayout();
},

updateLayout: function () {

},

/// SETUP BOARD

setupBoard: function () {

    for( var player_id in this.gamedatas.players )   
    {
                                         
            var player_board_div = $('player_board_'+player_id);
            dojo.place( this.format_block('jstpl_firstplayercontainer', {id: player_id} ), player_board_div );
            dojo.place( this.format_block('jstpl_reservetokencontainer', {id: player_id} ), player_board_div );
            
            for(let i = this.gamedatas.players[player_id].token; i>0; i--){

                if(this.gamedatas.players[player_id].color == 'f18400')
                {
                    dojo.place( this.format_block( 'jstpl_reservetoken', {
                        nb: i,
                        player: player_id,
                        x: 0,
                                            
                    } ) , 'reservetokencontainer_'+player_id );
                }

                if(this.gamedatas.players[player_id].color == '542583')
                {
                    dojo.place( this.format_block( 'jstpl_reservetoken', {
                        nb: i,
                        player: player_id,
                        x: -100,
                                            
                    } ) , 'reservetokencontainer_'+player_id );
                }

                if(this.gamedatas.players[player_id].color == 'bbbd02')
                {
                    dojo.place( this.format_block( 'jstpl_reservetoken', {
                        nb: i,
                        player: player_id,
                        x: -200,
                                            
                    } ) , 'reservetokencontainer_'+player_id );
                }

                if(this.gamedatas.players[player_id].color == 'e84041')
                {
                    dojo.place( this.format_block( 'jstpl_reservetoken', {
                        nb: i,
                        player: player_id,
                        x: -300,
                                            
                    } ) , 'reservetokencontainer_'+player_id );
                }

                dojo.query("#reservetoken_"+i+"_"+player_id).connect('onclick', this, 'onSelect' );

            }

        this.addTooltip('reservetokencontainer_'+player_id, _('Cocktail tokens available'),'' );

        if(this.gamedatas.players[player_id].no == 1)
        {
            dojo.place( this.format_block( 'jstpl_firstplayer', {
                mode: this.gamedatas.mode,                                    
            } ) , 'firstplayercontainer_'+player_id );

            if(this.gamedatas.mode == 1)
            {
                this.addTooltip( 'firstplayer', _('First player'),'' );
            }
            if(this.gamedatas.mode == 2)
            {
                this.addTooltip( 'firstplayer', _('First player / Aid'),'' );
            }
        }

        
    }

    if(this.gamedatas.mode == 2)
        {
            const first = document.getElementById("firstplayer");
            first.addEventListener('click', () => {
              const modal = document.getElementById("ModalHelpHappy");
              modal.style.display = 'flex';
                
            })

            const croix = document.getElementById("croix");
            croix.addEventListener('click', () => {
              const modal = document.getElementById("ModalHelpHappy");
              modal.style.display = 'none';
                
            })

            var description1 = _(this.gamedatas.happyhelp[1].description);
            var description2 = _(this.gamedatas.happyhelp[2].description);
            var description3 = _(this.gamedatas.happyhelp[3].description);
            var description4 = _(this.gamedatas.happyhelp[4].description);
            var description5 = _(this.gamedatas.happyhelp[5].description);
            var description6 = _(this.gamedatas.happyhelp[6].description);


            const help1 = document.getElementById("descritpion_help_1");
            help1.textContent = description1;
            const help2 = document.getElementById("descritpion_help_2");
            help2.textContent = description2;
            const help3 = document.getElementById("descritpion_help_3");
            help3.textContent = description3;
            const help4 = document.getElementById("descritpion_help_4");
            help4.textContent = description4;
            const help5 = document.getElementById("descritpion_help_5");
            help5.textContent = description5;
            const help6 = document.getElementById("descritpion_help_6");
            help6.textContent = description6;


            







        }
    
    if(this.gamedatas.showdice == 1)
    {
        var dice = document.getElementById('dice_content')
        dice.style.display = "flex";
    }

    if(this.gamedatas.showdice == 2)
    { 
        var dice = document.getElementById('dice_content')
        dice.style.display = "flex";
        var dice2 = document.getElementById('scene_2');
        dice2.style.display = "none";
        var dice3 = document.getElementById('scene_3');
        dice3.style.display = "none";
        var dice4 = document.getElementById('scene_4');
        dice4.style.display = "none";
        var dice5 = document.getElementById('scene_5');
        dice5.style.display = "none";
}

    

    
    for( var bock in this.gamedatas.bocks)   
    {
        var bock = this.gamedatas.bocks[bock];
        this.addBock(bock.type, bock.type_arg, bock.location_arg);
        if((bock.score1 != 0)||(bock.score2 != 0))
        {
            this.addToken(bock.type, bock.score1, bock.score2);
        }
    }

    

},


// CREATION DES BOCKS

addBock: function (type, type_arg, location_arg) {

if(type_arg == 1)
{
    if(type>= 1 && type <=5)
    dojo.place( this.format_block( 'jstpl_bockA', {
        id: type,
        x: (type-1)*(-100),
        y: 0,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 6 && type <=10)
    dojo.place( this.format_block( 'jstpl_bockA', {
        id: type,
        x: (type-6)*(-100),
        y: -100,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 11 && type <=15)
    dojo.place( this.format_block( 'jstpl_bockA', {
        id: type,
        x: (type-11)*(-100),
        y: -200,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 16 && type <=20)
    dojo.place( this.format_block( 'jstpl_bockA', {
        id: type,
        x: (type-16)*(-100),
        y: -300,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 21 && type <=25)
    dojo.place( this.format_block( 'jstpl_bockA', {
        id: type,
        x: (type-21)*(-100),
        y: -400,
        
                            
    } ) , 'carre'+location_arg );

    dojo.query("#bock_"+type).connect('onclick', this, 'onSelect' );
    

    if(type <=7)
    {
        dojo.place( this.format_block( 'jstpl_score1', {
            id: type,
            x: 66.1,
            y: 67.8,
                
                                
        } ) , 'bock_'+type );
    }

    if(type >= 8 && type <=13)
    {
        dojo.place( this.format_block( 'jstpl_score1', {
            id: type,
            x: 69.4,
            y: 68.3,
                
                                
        } ) , 'bock_'+type );
    }

    if(type >= 14)
    {
        dojo.place( this.format_block( 'jstpl_score1', {
            id: type,
            x: 61.7,
            y: 69.4,
                
                                
        } ) , 'bock_'+type );
    }

    //dojo.query("#score1_"+type).connect('onclick', this, 'onSelect' );
}

if(type_arg == 2)
{
    if(type>= 1 && type <=5)
    dojo.place( this.format_block( 'jstpl_bockB', {
        id: type,
        x: (type-1)*(-100),
        y: 0,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 6 && type <=10)
    dojo.place( this.format_block( 'jstpl_bockB', {
        id: type,
        x: (type-6)*(-100),
        y: -100,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 11 && type <=15)
    dojo.place( this.format_block( 'jstpl_bockB', {
        id: type,
        x: (type-11)*(-100),
        y: -200,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 16 && type <=20)
    dojo.place( this.format_block( 'jstpl_bockB', {
        id: type,
        x: (type-16)*(-100),
        y: -300,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 21 && type <=25)
    dojo.place( this.format_block( 'jstpl_bockB', {
        id: type,
        x: (type-21)*(-100),
        y: -400,
        
                            
    } ) , 'carre'+location_arg );

    dojo.query("#bock_"+type).connect('onclick', this, 'onSelect' ); 

    if(type <=7)
    {
        dojo.place( this.format_block( 'jstpl_score1', {
            id: type,
            x: 66.1,
            y: 67.8,
                
                                
        } ) , 'bock_'+type );

        dojo.place( this.format_block( 'jstpl_score2', {
            id: type,
            x: 46.7,
            y: 67.8,
                
                                
        } ) , 'bock_'+type );
    }

    if(type >= 8 && type <=13)
    {
        dojo.place( this.format_block( 'jstpl_score1', {
            id: type,
            x: 69.4,
            y: 68.3,
                
                                
        } ) , 'bock_'+type );

        dojo.place( this.format_block( 'jstpl_score2', {
            id: type,
            x: 50,
            y: 68.3,
                
                                
        } ) , 'bock_'+type );

        
    }

    if(type >= 14)
    {
        dojo.place( this.format_block( 'jstpl_score1', {
            id: type,
            x: 61.7,
            y: 69.4,
                
                                
        } ) , 'bock_'+type );

        dojo.place( this.format_block( 'jstpl_score2', {
            id: type,
            x: 42.2,
            y: 69.4,
                
                                
        } ) , 'bock_'+type );
    }

    //dojo.query("#score1_"+type).connect('onclick', this, 'onSelect' );
    //dojo.query("#score2_"+type).connect('onclick', this, 'onSelect' );
    
}

},

// CREATION DES TOKENS

addToken: function (type, score1, score2) {

    
    if(score1 != 0)
    {
        if(this.gamedatas.players[score1].color == 'f18400')
        {
            dojo.place( this.format_block( 'jstpl_token', {
                type: type,
                player: score1,
                x: 0,
                                    
            } ) , 'score1_'+type );
        }

        if(this.gamedatas.players[score1].color == '542583')
        {
            dojo.place( this.format_block( 'jstpl_token', {
                type: type,
                player: score1,
                x: -100,
                                    
            } ) , 'score1_'+type );
        }

        if(this.gamedatas.players[score1].color == 'bbbd02')
        {
            dojo.place( this.format_block( 'jstpl_token', {
                type: type,
                player: score1,
                x: -200,
                                    
            } ) , 'score1_'+type );
        }

        if(this.gamedatas.players[score1].color == 'e84041')
        {
            dojo.place( this.format_block( 'jstpl_token', {
                type: type,
                player: score1,
                x: -300,
                                    
            } ) , 'score1_'+type );
        }

        dojo.query("#token_"+type+"_"+score1).connect('onclick', this, 'onSelect' );

    }

    if(score2 != 0)
    {

        if(this.gamedatas.players[score2].color == 'f18400')
        {
            dojo.place( this.format_block( 'jstpl_token', {
                type: type,
                player: score2,
                x: 0,
                                    
            } ) , 'score2_'+type );
        }

        if(this.gamedatas.players[score2].color == '542583')
        {
            dojo.place( this.format_block( 'jstpl_token', {
                type: type,
                player: score2,
                x: -100,
                                    
            } ) , 'score2_'+type );
        }

        if(this.gamedatas.players[score2].color == 'bbbd02')
        {
            dojo.place( this.format_block( 'jstpl_token', {
                type: type,
                player: score2,
                x: -200,
                                    
            } ) , 'score2_'+type );
        }

        if(this.gamedatas.players[score2].color == 'e84041')
        {
            dojo.place( this.format_block( 'jstpl_token', {
                type: type,
                player: score2,
                x: -300,
                                    
            } ) , 'score2_'+type );
        }

        dojo.query("#token_"+type+"_"+score2).connect('onclick', this, 'onSelect' );
        
    }


},


// FLIP BOCK

addFlipBock: function (type, type_arg, location_arg) {

    var bock ='';

    if(type_arg == 1)
    {
        bock ='bockA';
    }

    if(type_arg == 2)
    {
        bock ='bockB';
    }

    
    if(type>= 1 && type <=5)
    dojo.place( this.format_block( 'jstpl_flipbock', {
        id: type,
        x: (type-1)*(-100),
        y: 0,
        class: bock,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 6 && type <=10)
    dojo.place( this.format_block( 'jstpl_flipbock', {
        id: type,
        x: (type-6)*(-100),
        y: -100,
        class: bock,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 11 && type <=15)
    dojo.place( this.format_block( 'jstpl_flipbock', {
        id: type,
        x: (type-11)*(-100),
        y: -200,
        class: bock,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 16 && type <=20)
    dojo.place( this.format_block( 'jstpl_flipbock', {
        id: type,
        x: (type-16)*(-100),
        y: -300,
        class: bock,
        
                            
    } ) , 'carre'+location_arg );

    if(type>= 21 && type <=25)
    dojo.place( this.format_block( 'jstpl_flipbock', {
        id: type,
        x: (type-21)*(-100),
        y: -400,
        class: bock,
        
                            
    } ) , 'carre'+location_arg );


    dojo.query("#flipbock_"+type).connect('onclick', this, 'onSelect' );

    
    if(type_arg == 1)
    {
        if(type <=7)
        {
            dojo.place( this.format_block( 'jstpl_flipscore1', {
                id: type,
                x: 66.1,
                y: 67.8,
                    
                                    
            } ) , 'flipbock_'+type );
        }

        if(type >= 8 && type <=13)
        {
            dojo.place( this.format_block( 'jstpl_flipscore1', {
                id: type,
                x: 69.4,
                y: 68.3,
                    
                                    
            } ) , 'flipbock_'+type );
        }

        if(type >= 14)
        {
            dojo.place( this.format_block( 'jstpl_flipscore1', {
                id: type,
                x: 61.7,
                y: 69.4,
                    
                                    
            } ) , 'flipbock_'+type );
        }

        //dojo.query("#flipscore1_"+type).connect('onclick', this, 'onSelect' );
    }

    if(type_arg == 2)
    {
        if(type <=7)
        {
            dojo.place( this.format_block( 'jstpl_flipscore1', {
                id: type,
                x: 66.1,
                y: 67.8,
                    
                                    
            } ) , 'flipbock_'+type );

            dojo.place( this.format_block( 'jstpl_flipscore2', {
                id: type,
                x: 46.7,
                y: 67.8,
                    
                                    
            } ) , 'flipbock_'+type );
        }

        if(type >= 8 && type <=13)
        {
            dojo.place( this.format_block( 'jstpl_flipscore1', {
                id: type,
                x: 69.4,
                y: 68.3,
                    
                                    
            } ) , 'flipbock_'+type );

            dojo.place( this.format_block( 'jstpl_flipscore2', {
                id: type,
                x: 50,
                y: 68.3,
                    
                                    
            } ) , 'flipbock_'+type );

            
        }

        if(type >= 14)
        {
            dojo.place( this.format_block( 'jstpl_flipscore1', {
                id: type,
                x: 61.7,
                y: 69.4,
                    
                                    
            } ) , 'flipbock_'+type );

            dojo.place( this.format_block( 'jstpl_flipscore2', {
                id: type,
                x: 42.2,
                y: 69.4,
                    
                                    
            } ) , 'flipbock_'+type );
        }

        //dojo.query("#flipscore1_"+type).connect('onclick', this, 'onSelect' );
        //dojo.query("#flipscore2_"+type).connect('onclick', this, 'onSelect' );
    }
   

},




/// INIT AND ROLL DICE

initDice: function () {

    
    this.diceElements = [
        document.getElementById('dice1'),
        document.getElementById('dice2'),
        document.getElementById('dice3'),
        document.getElementById('dice4'),
        document.getElementById('dice5')
    ];

    this.faceRotations = {
        1: { x: 0,   y: 0 },
        2: { x: 0,   y: -90 },
        3: { x: 0,   y: -180 },
        4: { x: 0,   y: 90 },
        5: { x: -90, y: 0 },
        6: { x: 90,  y: 0 }
    };

    this.forcedFaces = [this.gamedatas.forcedFaces[0].dice1, this.gamedatas.forcedFaces[0].dice2, this.gamedatas.forcedFaces[0].dice3, this.gamedatas.forcedFaces[0].dice4, this.gamedatas.forcedFaces[0].dice5];       // default faces
    this.shouldRotate = [this.gamedatas.blockdice[0].blockrolldice1, this.gamedatas.blockdice[0].blockrolldice2, this.gamedatas.blockdice[0].blockrolldice3, this.gamedatas.blockdice[0].blockrolldice4, this.gamedatas.blockdice[0].blockrolldice5]; // animation per dice

    this.shouldRotate.forEach((val, index) => {
    if (val == 1) {
        var dice = document.getElementById('blockdice'+(index+1));
        dice.classList.add("block");
    }
    });

    // Initial display of dice faces
    this.diceElements.forEach((dice, index) => {
        const face = this.forcedFaces[index];
        const rotation = this.faceRotations[face];

        // Apply the rotation instantly without animation
        dice.style.transition = "none";
        dice.style.transform = `rotateX(${rotation.x}deg) rotateY(${rotation.y}deg)`;
    });

   


    
},

rollDiceTwice: function () {
    this.rollDice(); // Premier lancer
    setTimeout(() => {
        this.rollDice(); // Deuxième lancer
    }, 100);
    setTimeout(() => {
        this.rollDice(); // 3eme lancer
    }, 200);
},

rollDice: function () {
    

    this.diceElements.forEach((dice, index) => {
        const face = this.forcedFaces[index];
        const target = this.faceRotations[face];

        if (this.shouldRotate[index] == 0) {
            const fullTurnsX = Math.floor(Math.random() * 10 + 10) * 360;
            const fullTurnsY = Math.floor(Math.random() * 10 + 10) * 360;
            const finalX = fullTurnsX + target.x;
            const finalY = fullTurnsY + target.y;

            dice.style.transition = "transform 2s cubic-bezier(0.23, 1, 0.32, 1)";
            dice.style.transform = `rotateX(${finalX}deg) rotateY(${finalY}deg)`;
        } else {
            dice.style.transition = "none";
            dice.style.transform = `rotateX(${target.x}deg) rotateY(${target.y}deg)`;
        }
    });
},
    


/////////////////////////////////////////////////////////////////////////////////  
//         _____  _                       _                  _   _             
//        |  __ \| |                     ( )                | | (_)            
//        | |__) | | __ _ _   _  ___ _ __|/ ___    __ _  ___| |_ _  ___  _ __  
//        |  ___/| |/ _` | | | |/ _ \ '__| / __|  / _` |/ __| __| |/ _ \| '_ \ 
//        | |    | | (_| | |_| |  __/ |    \__ \ | (_| | (__| |_| | (_) | | | |
//        |_|    |_|\__,_|\__, |\___|_|    |___/  \__,_|\___|\__|_|\___/|_| |_|
//                         __/ |                                               
//                        |___/                                                
/////////////////////////////////////////////////////////////////////////////////  
        
                
        onSelect: function(evt)
        {        	 
            // Preventing default browser reaction
             dojo.stopEvent( evt );

            
             
            if( !this.isCurrentPlayerActive() || (!(evt.currentTarget.classList.contains('selectable')) && !(evt.currentTarget.classList.contains('selectable_dice'))))
            {   
                return; 
            }
            
            if(this.isCurrentPlayerActive() && evt.currentTarget.classList.contains('selectable'))
            {
                
                this.bgaPerformAction('actSelect', { arg1: evt.currentTarget.id });
            }

            if(this.isCurrentPlayerActive() && evt.currentTarget.classList.contains('selectable_dice'))
            {
                const id = evt.currentTarget.id; 
                const num = Number(id.match(/\d+/)[0]); 

                const dice = document.getElementById("blockdice" + num);
                if(dice.classList.contains("block"))
                {
                    dice.classList.remove("block");
                    dojo.removeClass( 'block', 'disabled');
                }
                
                else
                {
                    dice.classList.add("block");

                    // Récupère tous les dés
                    const allDice = [
                        document.getElementById('blockdice1'),
                        document.getElementById('blockdice2'),
                        document.getElementById('blockdice3'),
                        document.getElementById('blockdice4'),
                        document.getElementById('blockdice5')
                    ];

                    // Vérifie si TOUS les dés ont la classe "block"
                    const allHaveBlock = allDice.every(dice => dice.classList.contains('block'));

                    if (allHaveBlock) {
                        dojo.addClass( 'block', 'disabled');
                    }
                }
                               
                
            }

        },

        onOpButton: function(evt)
        {
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );
            
            this.bgaPerformAction('actButton', { arg1: evt.currentTarget.id });
            
            

        },

        onOpBlock: function(evt)
        {
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // const rolldice = Array.from(document.querySelectorAll('.blockdice:not(.block)')).map(el => el.id);
            // const numbers = rolldice.map(item => parseInt(item.match(/\d+/)[0], 10));

            const rolldice = Array.from(document.querySelectorAll('.blockdice.block')).map(el => el.id);
            const numbers = rolldice.map(item => parseInt(item.match(/\d+/)[0], 10));
            const blockdice = numbers.join('_');

                    
           this.bgaPerformAction('actBlock', { arg1: evt.currentTarget.id, arg2: blockdice });
            
            

        },

        
///////////////////////////////////////////////////////////////////////////////// 
//       _   _       _   _  __ _           _   _                 
//      | \ | |     | | (_)/ _(_)         | | (_)                
//      |  \| | ___ | |_ _| |_ _  ___ __ _| |_ _  ___  _ __  ___ 
//      | . ` |/ _ \| __| |  _| |/ __/ _` | __| |/ _ \| '_ \/ __|
//      | |\  | (_) | |_| | | | | (_| (_| | |_| | (_) | | | \__ \
//      |_| \_|\___/ \__|_|_| |_|\___\__,_|\__|_|\___/|_| |_|___/
//                                                                 
/////////////////////////////////////////////////////////////////////////////////  

        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'rolldice', this, "notif_rolldice" );
            dojo.subscribe( 'maskdice', this, "notif_maskdice" );
            dojo.subscribe( 'displayblock', this, "notif_displayblock" );
            dojo.subscribe( 'masklock', this, "notif_masklock" );
            dojo.subscribe( 'moveToken', this, "notif_moveToken" );
            dojo.subscribe( 'score', this, "notif_score" );
            dojo.subscribe( 'animScore', this, "notif_animScore" );
            dojo.subscribe( 'pina', this, "notif_pina" );
            dojo.subscribe( 'endhappy', this, "notif_endhappy" );
            dojo.subscribe( 'animScoreHappyLose3', this, "notif_animScoreHappyLose3" );
            dojo.subscribe( 'animScoreHappyWin4', this, "notif_animScoreHappyWin4" );
            dojo.subscribe( 'animScoreHappyOtherWin1', this, "notif_animScoreHappyOtherWin1" );
            dojo.subscribe( 'recupToken', this, "notif_recupToken" );
            dojo.subscribe( 'flip', this, "notif_flip" );
            dojo.subscribe( 'moveTokenHappy', this, "notif_moveTokenHappy" );
            

            // this.notifqueue.setSynchronous( 'rolldice', 2000 );
        },  
        
        notif_rolldice: function( notif )
        {
            if(notif.args.happy == 1)
            {
               var dice2 = document.getElementById('scene_2');
               dice2.style.display = "none";
               var dice3 = document.getElementById('scene_3');
               dice3.style.display = "none";
               var dice4 = document.getElementById('scene_4');
               dice4.style.display = "none";
               var dice5 = document.getElementById('scene_5');
               dice5.style.display = "none";
            }

            var dice = document.getElementById('dice_content');
            dice.style.display = "flex";

            this.forcedFaces = notif.args.roll;
            this.shouldRotate = notif.args.block;

            this.rollDiceTwice();
               
        },

        notif_maskdice: function( notif )
        {

            var dice = document.getElementById('dice_content')
            dice.style.display = "none";

        },

        notif_displayblock: function( notif )
        {
            var dice = document.getElementById('blockdice'+notif.args.dice)
            
            if(notif.args.block == 0)
            {
                dice.classList.remove("block");

            }

            if(notif.args.block == 1)
            {
                dice.classList.add("block");

            }
            
            

        },


        notif_masklock: function( notif )
        {

            for($i = 1; $i <=5; $i++)
            {
                var dice = document.getElementById('blockdice'+$i)
                dice.classList.remove("block");
            }
        },


        notif_moveToken: async function(notif)
        {
            const mobile = document.getElementById("reservetoken_"+notif.args.reserve_token+"_"+notif.args.player_id);
            mobile.id = "token_"+notif.args.bock+"_"+notif.args.player_id; // je change d'id
            mobile.classList.add('tokenhover');

            const enfant = document.getElementById(mobile.id);
            const parent = document.getElementById("score"+notif.args.score_position+"_"+notif.args.bock);

            await this.animationManager.slideAndAttach(enfant, parent);
        },

        notif_moveTokenHappy: async function(notif)
        {
            var dice = document.getElementById('dice_content')  // pour contrer un bug a cause du decallage de la barre des dés (à voir avec Thoun)
            dice.style.display = "none";
            
            const mobile = document.getElementById(notif.args.enfant);
            mobile.id = "token_"+notif.args.bock+"_"+notif.args.player_id; // je change d'id

            const enfant = document.getElementById(mobile.id);
            const parent = document.getElementById(notif.args.parent);

            await this.animationManager.slideAndAttach(enfant, parent);
        },

        
        notif_score: function( notif )
        {
            this.scoreCtrl[ notif.args.player_id].toValue( notif.args.score);
            
        },

        notif_animScore: function( notif )
        {
            dojo.place( this.format_block( 'jstpl_animScore', {
                        score: "+"+notif.args.score,
                                                                    
                    } ) , 'bock_'+notif.args.bock );


            setTimeout(() => 
            {
                            
                document.querySelectorAll('.animScore').forEach(el => el.remove());


            }, "1600");
            
            
        },

        notif_animScoreHappyLose3: function( notif )
        {
            dojo.place( this.format_block( 'jstpl_animScore', {
                        score: "-3",
                                                                    
                    } ) , 'overall_player_board_'+ notif.args.player_id );


            setTimeout(() => 
            {
                            
                document.querySelectorAll('.animScore').forEach(el => el.remove());


            }, "1600");
            
            
        },

        notif_animScoreHappyWin4: function( notif )
        {
            dojo.place( this.format_block( 'jstpl_animScore', {
                        score: "+4",
                                                                    
                    } ) , 'overall_player_board_'+ notif.args.player_id);


            setTimeout(() => 
            {
                            
                document.querySelectorAll('.animScore').forEach(el => el.remove());


            }, "1600");
            
            
        },

        notif_animScoreHappyOtherWin1: function( notif )
        {
            notif.args.players_id.forEach(playerId => {
            dojo.place(
                this.format_block('jstpl_animScore', { score: "+1" }),
                'overall_player_board_' + playerId
            );
            });

            setTimeout(() => 
            {
                            
                document.querySelectorAll('.animScore').forEach(el => el.remove());


            }, "1600");
            
            
        },


        
        
        notif_pina: function( notif )
        {
            dojo.place( this.format_block( 'jstpl_animPina', {
                       
                                                                    
                    } ) , 'board_id' );


            setTimeout(() => 
            {
                            
                document.getElementById('pina').remove();


            }, "4500");
            
            
        },

        notif_endhappy: function( notif )
        {
            
            var dice2 = document.getElementById('scene_2');
            dice2.style.display = "block";
            var dice3 = document.getElementById('scene_3');
            dice3.style.display = "block";
            var dice4 = document.getElementById('scene_4');
            dice4.style.display = "block";
            var dice5 = document.getElementById('scene_5');
            dice5.style.display = "block";
            
               
        },

        notif_recupToken: async function( notif )
        {
            var dice = document.getElementById('dice_content')  // pour contrer un bug a cause du decallage de la barre des dés (à voir avec Thoun)
            dice.style.display = "none";

            const token = document.getElementById(notif.args.mobile);
            token.classList.remove("tokenhover");
            token.id = "reservetoken_"+notif.args.nb+"_"+notif.args.player_id;

            const enfant = document.getElementById("reservetoken_"+notif.args.nb+"_"+notif.args.player_id);
            const parent = document.getElementById("reservetokencontainer_"+notif.args.player_id);

            await this.animationManager.slideAndAttach(enfant, parent);

            const token2 = document.getElementById("reservetoken_"+(notif.args.nb-1)+"_"+notif.args.player_id); /// remettre dans l'ordre: le token qui vient d'arriver revient en premiere position
            parent.insertBefore(enfant, token2);
            
               
        },

        notif_flip: function( notif )
        {
            this.addFlipBock(notif.args.type, notif.args.new_type_arg, notif.args.position);
            dojo.query("#bock_"+notif.args.type).addClass("flipable");
            

            setTimeout(function() {             // 100ms pour attendre que le DOM soit effectif
            dojo.query("#bock_"+notif.args.type).addClass("flip");
            dojo.query("#flipbock_"+notif.args.type).addClass("flip");
            
            }, 100);

            setTimeout(function() {             // 1500ms pour laisser le temps que le flip soit fini
            const card = document.getElementById("bock_"+notif.args.type);
            card.remove();

            const Newcard = document.getElementById("flipbock_"+notif.args.type);
            Newcard.classList.remove("rotate");
            Newcard.classList.remove("flip");
            Newcard.id = "bock_"+notif.args.type;


            const Newflipscore1 = document.getElementById("flipscore1_"+notif.args.type);
            Newflipscore1.id = "score1_"+notif.args.type;

            if(notif.args.new_type_arg == 2)
            {
            const Newflipscore2 = document.getElementById("flipscore2_"+notif.args.type);
            Newflipscore2.id = "score2_"+notif.args.type;
            }


            

            
    
            }, 1500);

            
        },














   });             
});
