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
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.pinacoladice", ebg.core.gamegui, {
        constructor: function(){
            console.log('pinacoladice constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

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

                       
            // TODO: Set up your game interface here, according to "gamedatas"

            this.players = gamedatas.players; // A RAJOUTER POUR MOTEUR (UTILITY METHODS)
            
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            this.setupBoard();
            this.initDice();

            
            //// CONNECTIONS CLICK
            dojo.query(".carre").connect('onclick', this, 'onSelect' )
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
                for( var sid in this.args.selectable)
                {
                    if(this.isCurrentPlayerActive())
                    {
                        dojo.query("#"+this.args.selectable[sid]).addClass("selectable");
                    
                    }
                }

                for( var sid in this.args.selected)
                {
                    if(this.isCurrentPlayerActive())
                    {
                        dojo.query("#"+this.args.selected[sid]).addClass("selected");
                    }
                }

                console.warn(this.args.selectable_dice)

                for( var sid in this.args.selectable_dice)
                {
                    if(this.isCurrentPlayerActive())
                    {
                        dojo.query("#"+this.args.selectable_dice[sid]).addClass("selectable_dice");
                    
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

            dojo.query(".selectable").removeClass("selectable");
            dojo.query(".selected").removeClass("selected");
            dojo.query(".selectable_dice").removeClass("selectable_dice");
            
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
                                if(args.buttons[nb] == "pass")
                                {
                                this.addActionButton( 'pass', _("Pass") ,'onOpButton', null, null, 'red' );
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
                                if(args.buttons[nb] == "continue") 
                                {
                                this.addActionButton( 'continue', _("Continue") ,'onOpButton', null, null, 'blue' );
                                }
                                if(args.buttons[nb] == "roll") 
                                {
                                this.addActionButton( 'roll', _("Roll the dice") ,'onOpButton', null, null, 'blue' );
                                }
                                if(args.buttons[nb] == "block") 
                                {
                                this.addActionButton( 'block', _("Roll the dice") ,'onOpBlock', null, null, 'blue' );
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

// TIMER sur bouton

startActionTimer: function(buttonId, time, pref, autoclick = false) {
    var button = $(buttonId);
    var isReadOnly = this.isReadOnly();
    if (button == null || isReadOnly || pref == 2) {
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

    
    if(this.gamedatas.showdice == 1)
    {
        var dice = document.getElementById('dice_content')
        dice.style.display = "flex";
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
        },  
        
        notif_rolldice: function( notif )
        {
            var dice = document.getElementById('dice_content')
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













   });             
});
