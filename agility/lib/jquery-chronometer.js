/* ------------------------------------------------------------------------
 * 
 * Reference: https://github.com/20tab/jquery-chron
 * 
------------------------------------------------------------------------- */
(function( $ ) {


	var running = false;
	var paused = false;
	var startTime = 0;
    var localTime = 0; // to avoid time offset problems
	var stopTime = 0;
	var pauseTime = 0;
	
	var config = {
		// initial values
		days			: 0,
		hours			: 0,
		minutes			: 0,
		seconds			: 0,
		mseconds		: 0,
		
		// HTML element ID's to work with (display objects)
		days_sel		: "#days",
		hours_sel		: "#hours",
		minutes_sel		: "#minutes",
		seconds_sel		: "#seconds",
		mseconds_sel	: "#mseconds",

		// HTML element ID's to work with (button objects)
		start			: "#start",
		stop			: "#stop",
		reset			: "#reset",
		pause			: "#pause",
		resume			: "#resume",
		
		// methods to be called before action taken. on return false skip action
		onBeforeStart	: function(){ return true; },
		onBeforeStop	: function(){ return true; },
		onBeforeReset	: function(){ return true; },
		onBeforeResync	: function(){ return true; },
		onBeforePause	: function(){ return true; },
		onBeforeResume	: function(){ return true; },
		onUpdate		: function(tstamp,running,paused){ return true; }, // action to do on display new timestamp

        triggerEvents   : false,    // on true triggerEvents
		target			: "*", 		//selectors for the events target
		auto			: true,		//true if plugin generate html chronometer
		interval		: 100,		// polling interval (msecs) default: 0.1 second
		showMode		: 0         // 0: use hh:mm:ss.xxx format else use decimal seconds format with provided precision
	};
	
	var methods= {
		init: function(options) { $.extend(config,options); },
		start: function(timestamp) {
			var check = config.onBeforeStart();
			if(check != false){
				$(config.start).attr('disabled',true);
				$(config.stop).attr('disabled',false);
				$(config.resume).attr('disabled',true);
				$(config.pause).attr('disabled',false);
				localTime=Date.now();
				if(typeof timestamp === 'undefined') startTime=localTime;
				else startTime=timestamp;
				stopTime=startTime;
				pauseTime=startTime;
				running = true;
				paused = false;
				run_chrono();
			}
			if (config.triggerEvents) $(config.target).trigger('chronostart');
		},
		stop: function(timestamp){
			var check = config.onBeforeStop();
			if(check != false){
				$(config.start).attr('disabled',false);
				$(config.stop).attr('disabled',true);
				$(config.resume).attr('disabled',true);
				$(config.pause).attr('disabled',true);
				if(typeof timestamp === 'undefined') stopTime=Date.now();
				else stopTime=timestamp;
				running = false;
				paused = false;
			}
            if (config.triggerEvents) $(config.target).trigger('chronostop');
		},
		pause: function(timestamp){
			var check = config.onBeforePause();
			if(check != false){
				$(config.start).attr('disabled',false);
				$(config.stop).attr('disabled',true);
				$(config.resume).attr('disabled',false);
				$(config.pause).attr('disabled',true);
				if(typeof timestamp === 'undefined') pauseTime=Date.now();
				else pauseTime=timestamp;
				running = false;
				paused = true;
			}
            if (config.triggerEvents) $(config.target).trigger('chronopause');
		},
		resume : function(){
			var check = config.onBeforeResume();
			if(check != false){
				$(config.start).attr('disabled',true);
				$(config.stop).attr('disabled',false);
				$(config.resume).attr('disabled',true);
				$(config.pause).attr('disabled',false);
				running = true;
				paused = false;
				run_chrono();
			}
            if (config.triggerEvents) $(config.target).trigger('chronoresume');
		},
		resync: function (timestop,timestart) {
			var check = config.onBeforeResync();
			if(check != false){
				var elapsed=timestop-startTime; // time of manual chrono
				startTime=timestart-elapsed; // adjust start time on auto chrono
			}
			if (config.triggerEvents) $(config.target).trigger('chronoresync');

		},
		reset : function(){
			var check = config.onBeforeReset();
			if(check != false){
				// set elapsed time to 0. dont stop/pause if running
				startTime =0;
				stopTime  =0;
				localTime =0;
			}
            if (config.triggerEvents) $(config.target).trigger('chronoreset');
		},
		started : function() {
			return ( running || paused );
		},
		getValue : function() {
			if (paused) return pauseTime-startTime; // paused: show intermediate time
			if (!running) return stopTime-startTime; // stopped: show last stored time mark
			return Date.now()-localTime; // running. may be not accurate
		}
	};

	function run_chrono(){
        var now=Date.now();
		var elapsed=0;
		if (startTime==0) startTime=now;
        if (localTime==0) localTime=now;
		if (stopTime==0) stopTime=now;
		if (pauseTime==0) pauseTime=now;
		if (paused) {
			elapsed		= pauseTime-startTime; // use localTime to evaluate time lapse
			config.mseconds	= elapsed % 1000;
			config.seconds	= Math.floor(elapsed / 1000);
			config.minutes	= Math.floor(config.seconds / 60);
			config.seconds	= config.seconds % 60;
			config.hours 	= Math.floor(config.minutes / 60);
			config.minutes	= config.minutes % 60;
			config.days		= Math.floor(config.hours / 24);
			config.hours    = config.hours % 24;
		} else if (running ) {
			elapsed		= now-localTime; // use localTime to evaluate time lapse
			config.mseconds	= elapsed % 1000;
			config.seconds	= Math.floor(elapsed / 1000);
			config.minutes	= Math.floor(config.seconds / 60);
			config.seconds	= config.seconds % 60;
			config.hours 	= Math.floor(config.minutes / 60);
			config.minutes	= config.minutes % 60;
			config.days		= Math.floor(config.hours / 24);
			config.hours    = config.hours % 24;
			setTimeout(run_chrono,config.interval);
		} else { // chrono stopped; show data at least once
			elapsed		= stopTime-startTime; // use real startTime instead of localTime
			config.mseconds	= elapsed % 1000;
			config.seconds	= Math.floor(elapsed / 1000);
			config.minutes	= Math.floor(config.seconds / 60);
			config.seconds	= config.seconds % 60;
			config.hours 	= Math.floor(config.minutes / 60);
			config.minutes	= config.minutes % 60;
			config.days		= Math.floor(config.hours / 24);
			config.hours    = config.hours % 24;
		}
		view_chrono(elapsed);
	}
	
	function view_chrono(elapsed){
		if (elapsed<0) elapsed=0; // to avoid bogus data in screen
		if (! config.onUpdate(elapsed,running,paused)) return;
		var digits=config.showMode;
		var extra="";
		if (running && config.showMode>0) {
			digits=config.showMode-1;
			extra="&nbsp;"; // add an space
		}
		if (config.showMode==0) {
			$(config.days_sel).html(view_format(config.days));
			$(config.days_sel).data('days',config.days);
			$(config.hours_sel).html(view_format(config.hours));
			$(config.hours_sel).data('hours',config.hours);
			$(config.minutes_sel).html(view_format(config.minutes));
			$(config.minutes_sel).data('minutes',config.minutes);
			$(config.seconds_sel).html(view_format(config.seconds));
			$(config.seconds_sel).data('seconds',config.seconds);
			$(config.mseconds_sel).html(view_format(config.mseconds));
			$(config.mseconds_sel).data('mseconds',config.mseconds);
		} else {
			// ignore all fields but "seconds" and show in floating point with provided precision
			$(config.days_sel).html("");
			$(config.days_sel).data('days',0);
			$(config.hours_sel).html("");
			$(config.hours_sel).data('hours',0);
			$(config.minutes_sel).html("");
			$(config.minutes_sel).data('minutes',0);
			$(config.seconds_sel).html(""+toFixedT(parseFloat(elapsed/1000.0),digits)+extra);
			$(config.seconds_sel).data('seconds', parseFloat(elapsed/1000.0) );
			$(config.mseconds_sel).html("");
			$(config.mseconds_sel).data('mseconds',0);			
		}
	}
			
	function view_format(value){ return (value<10)? "0" + value: value; }
	
	function format_selector(value){ return value.replace("#","").replace(".","");	}
		
	$.fn.Chrono = function( args ){
		if ( methods[args]){
			// vemos si lo hemos invocado con un metodo como parametro
			return methods[ args ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( (typeof args === 'object' ) || (!args) ) {
			// init con argumentos de configuracion
			methods.init.apply( this, arguments );
			return this.each(function(){
				// if automatic mode is set, consider this object as an HTML element and expand proper html code
				if(config.auto){
					$(this).html(
							'<span id="'+format_selector(config.days_sel)+'" data-days = "'+config.days+'">'+view_format(config.days)+
					'</span>:<span id="'+format_selector(config.hours_sel)+'" data-hours = "'+config.hours+'">'+view_format(config.hours)+
					'</span>:<span id="'+format_selector(config.minutes_sel)+'" data-minutes = "'+config.minutes+'">'+view_format(config.minutes)+
					'</span>:<span id="'+format_selector(config.seconds_sel)+'" data-seconds = "'+config.seconds+'">'+view_format(config.seconds)+
					'</span>'
					);
				}
				if( config.showMode<0) config.showMode=0;
				if( config.days != 0 || config.hours != 0 || config.minutes != 0 || config.seconds != 0 || config.mseconds != 0 ){
					// if initial data are not null assume clock assume chrono in "started" state
					$(config.start).attr('disabled',true);
					$(config.pause).attr('disabled',true);
				} else {
					// else assume clock is created in "stopped" state
					$(config.stop).attr('disabled',true);
					$(config.pause).attr('disabled',true);
					$(config.resume).attr('disabled',true);
				}
				// set up events handlers
				$(config.start).on('click',methods.start);
				$(config.stop).on('click',methods.stop);
				$(config.reset).on('click',methods.reset);
				$(config.pause).on('click',methods.pause);
				$(config.resume).on('click',methods.resume);
				
			});
		} else {
			// error
			$.error( 'Method ' +  args + ' does not exist on jQuery.Chrono' );
		}
	};
}( jQuery ) );
