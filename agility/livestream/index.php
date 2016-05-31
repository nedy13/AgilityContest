<?php
/*
 videowall/index.php

 Copyright  2013-2016 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

 This program is free software; you can redistribute it and/or modify it under the terms
 of the GNU General Public License as published by the Free Software Foundation;
 either version 2 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 See the GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along with this program;
 if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once(__DIR__."/../server/tools.php");
require_once(__DIR__."/../server/auth/Config.php");
require_once(__DIR__."/../server/auth/AuthManager.php");
$config =Config::getInstance();
/* check for properly installed xampp */
if( ! function_exists('password_verify')) {
    die("Invalid environment: You should have php-5.5.X or higher version installed");
}
if ( intval($config->getEnv('restricted'))!=0) {
    die("Access other than public directory is not allowed");
}
$am=new AuthManager("LiveStream");
if (!$am->allowed(ENABLE_LIVESTREAM)) {
	die("Current license has no permissions to handle livestream related functions");
}
// tool to perform automatic upgrades in database when needed
require_once(__DIR__."/../server/upgradeVersion.php");
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="application-name" content="Agility Contest" />
<meta name="copyright" content="© 2013-2015 Juan Antonio Martinez" />
<meta name="author" lang="en" content="Juan Antonio Martinez" />
<meta name="description"
        content="A web client-server (xampp) app to organize, register and show results for FCI Dog Agility Contests" />
<meta name="distribution" 
	content="This program is free software; you can redistribute it and/or modify it under the terms of the 
		GNU General Public License as published by the Free Software Foundation; either version 2 of the License, 
		or (at your option) any later version." />
<title>AgilityContest (LiveStream)</title>
<link rel="stylesheet" type="text/css" href="/agility/lib/jquery-easyui-1.4.2/themes/<?php echo $config->getEnv('easyui_theme'); ?>/easyui.css" />
<link rel="stylesheet" type="text/css" href="/agility/lib/jquery-easyui-1.4.2/themes/icon.css" />
<link rel="stylesheet" type="text/css" href="/agility/css/style.css" />
<link rel="stylesheet" type="text/css" href="/agility/css/datagrid.css" />
<link rel="stylesheet" type="text/css" href="/agility/css/livestream_css.php" />
<link rel="stylesheet" type="text/css" href="/agility/css/videowall_css.php" />
<script src="/agility/lib/HackTimer/HackTimer.js" type="text/javascript" charset="utf-8" ></script>
<script src="/agility/lib/jquery-1.12.3.min.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/jquery-easyui-1.4.2/jquery.easyui.min.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/jquery-easyui-1.4.2/extensions/datagrid-view/datagrid-groupview.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/jquery-fileDownload-1.4.2.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/scripts/easyui-patches.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/scripts/datagrid_formatters.js.php" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/jquery-chronometer.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/jquery-fittext-1.2.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/sprintf.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/scripts/common.js.php" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/scripts/competicion.js.php" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/scripts/events.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/livestream/livestream.js.php" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/videowall/videowall.js.php" type="text/javascript" charset="utf-8" > </script>


    <style>

        body {
            /* default background from environment */
            font-size: 100%;
            background: <?php echo $config->getEnv('easyui_bgcolor'); ?>;
        }

        /* remove underlines around footer imagelinks */
        a,
        a img {
            text-decoration: none;
            outline: none;
            border: 0px none transparent;
        }

		.datagrid-body .datagrid-group {
			background-color: <?php echo $config->getEnv("vw_hdrbg3"); ?>;
			color: <?php echo $config->getEnv("vw_hdrfg3"); ?>;
			height:40px;
			line-height: 40px;
		}

		.datagrid-body .datagrid-group .datagrid-group-title {
			height:40px;
			line-height: 40px;
			font-weight: bold;
		}

		.datagrid-body .datagrid-group .datagrid-group-expander {
			margin-top:7px;
		}

    </style>

<script type="text/javascript" charset="utf-8">

function initialize() {
	// make sure that every ajax call provides sessionKey
	$.ajaxSetup({
	  beforeSend: function(jqXHR,settings) {
		if ( typeof(ac_authInfo.SessionKey)!=='undefined' && ac_authInfo.SessionKey!=null) {
			jqXHR.setRequestHeader('X-AC-SessionKey',ac_authInfo.SessionKey);
		}
	    return true;
	  }
	});
	loadConfiguration();
	getLicenseInfo();
	getFederationInfo();
}

/**
 * Common rowStyler function for AgilityContest datagrids
 * @param {int} idx Row index
 * @param {object} row Row data
 * @return {string} proper row style for given idx
 */
function myRowStyler(idx,row) {
	var height=(ac_config.vwc_simplified==0)?40:50;
	var res="height:"+height+"px;line-height:"+height+"px;background-color:";
	var c1='<?php echo $config->getEnv('vw_rowcolor1'); ?>';
    var c2='<?php echo $config->getEnv('vw_rowcolor2'); ?>';
	if ( (idx&0x01)==0) { return res+c1+";"; } else { return res+c2+";"; }
}

function myTransparentRowStyler(idx,row) {
    var res="background-color:";
    var c1='<?php echo $config->getEnv('easyui_rowcolor1'); ?>';
    var c2='<?php echo $config->getEnv('easyui_rowcolor2'); ?>';
    if ( (idx&0x01)==0) { return res+c1+";opacity:0.9"; }
    else { return res+c2+";opacity:0.9"; }
}
/* same as above, but tracks tanda and team information */
function myLlamadaRowStyler(idx,row) {
	var height=(ac_config.vwc_simplified==0)?40:50;
	var res="height:"+height+"px;line-height:"+height+"px;background-color:";
    var c1='<?php echo $config->getEnv('easyui_rowcolor1'); ?>';
    var c2='<?php echo $config->getEnv('easyui_rowcolor2'); ?>';
    var tnd='<?php echo $config->getEnv('vw_hdrbg2'); ?>';
    var eqp='<?php echo $config->getEnv('vw_hdrbg3'); ?>';
    if (parseInt(row.Orden)==-1) return res+tnd+";";
    if (parseInt(row.Orden)==0) return res+eqp+";";
    if ( (idx&0x01)==0) { return res+c1+";"; } else { return res+c2+";"; }
}

</script>
</head>

<body style="margin:0;padding:0;background-color:blue;font-size:100%" onload="initialize();">
<div id="vw_contenido" style="width:inherit;height:inherit;margin:0;padding:0">

<!--  CUERPO PRINCIPAL DE LA PAGINA (se modifica con el menu) -->

<div id="selvw-dialog" class="easyui-dialog" style="position:relative;width:500px;height:220px;padding:20px 20px">
	<form id="selvw-Selection">
    	<div class="fitem">
       		<label for="Prueba"><?php _e('Select Session/Ring'); ?>:</label>
       		<select id="selvw-Session" name="Session" style="width:200px"></select>
    	</div>
    	<div class="fitem">
       		<label for="Vista"><?php _e('Select View'); ?>:</label>
       		<select id="selvw-Vista" name="Vista" style="width:200px">
                <optgroup label="<?php _e('Embedded Video');?> ">
                    <!-- videowall -->
					<option value="0"><?php _e('Live Stream'); ?></option>
					<option value="1"><?php _e('Partial scores'); ?></option>
					<option value="2"><?php _e('Starting order'); ?></option>
                </optgroup>
                <optgroup label="<?php _e('Chroma Key');?> ">
                    <!-- livestream -->
                    <option value="3"><?php _e('On Screen Display'); ?></option>
                    <option value="4"><?php _e('Partial scores'); ?></option>
                    <option value="5"><?php _e('Starting order'); ?></option>
                </optgroup>
       		</select>
    	</div>
    	
	</form>
</div> <!-- Window -->

<div id="selvw-Buttons" style="text-align:right">
   	<a id="selvw-okBtn" href="#" class="easyui-linkbutton" 
   	   	data-options="iconCls: 'icon-ok'" onclick="vw_accept()"><?php _e('Accept'); ?></a>
</div>	<!-- botones -->

</div> <!-- contenido -->

<script type="text/javascript">

//add 'callback' property to store interval references
$.extend($.fn.window.defaults,{callback:null});

$('#selvw-dialog').dialog({
	title: '<?php _e('View to deploy'); ?>',
	collapsible: false,
	minimizable: false,
	maximizable: false,
	closable: true,
	closed: false,
	shadow: true,
	modal: true,
	buttons: '#selvw-Buttons' 
});

$('#selvw-form').form();

addTooltip($('#selvw-okBtn').linkbutton(),'<?php _e("Use selected as working session"); ?>');

$('#selvw-Session').combogrid({
	panelWidth: 500,
	panelHeight: 150,
	idField: 'ID',
	textField: 'Nombre',
	url: '/agility/server/database/sessionFunctions.php',
	method: 'get',
	mode: 'remote',
	required: true,
	rownumber: true,
	multiple: false,
	fitColumns: true,
	singleSelect: true,
	editable: false, // avoid keyboard deploy
	columns: [[
	    { field:'ID',			width:'5%', sortable:false, align:'center', title:'ID' }, // Session ID
		{ field:'Nombre',		width:'25%', sortable:false,   align:'center',  title: '<?php _e('Name'); ?>' },
		{ field:'Comentario',	width:'60%', sortable:false,   align:'left',  title: '<?php _e('Comments'); ?>' },
        { field:'Prueba',	    hidden:true },
        { field:'Jornada',	    hidden:true },
		{ field:'Background',	hidden:true },
		{ field:'LiveStream2',	hidden:true },
		{ field:'LiveStream3',	hidden:true }
	]],
	onBeforeLoad: function(param) { 
		param.Operation='selectring';
		param.Hidden=0;
		return true;
	},
	onLoadSuccess: function(data) {
		var vs=$('#selvw-Session');
		var def= vs.combogrid('grid').datagrid('getRows')[0].ID; // get first ID ( usually ring 1 )
		vs.combogrid('setValue',def);
	},
    onSelect: function(index,row) {setupWorkingData(row.Prueba,row.Jornada,(row.manga>0)?row.manga:1);}
});

function vw_accept() {
	// si prueba invalida cancelamos operacion
	var s=$('#selvw-Session').combogrid('grid').datagrid('getSelected');
	if ( s===null ) {
		// indica error
		$.messager.alert("Error",'<?php _e("You should select a valid session"); ?>',"error");
		return;
	}
	// clear selection to make sure next time gets empty
	$('#selvw-Session').combogrid('setValue','');
	$('#selvw-Jornada').combogrid('setValue','');
	
	// store selected data into global structure
	workingData.sesion=s.ID;
	workingData.nombreSesion=s.Nombre;
	initWorkingData(s.ID,videowall_eventManager);
	ac_config.vw_combined=0;
	var page="'/agility/console/frm_notavailable.php";
	var n=parseInt($('#selvw-Vista').val());
	var title="AgilityContest LiveStream ";
	switch (n) {
		case 0: // Embedded OSD
			page = "/agility/livestream/vwls_osdvideo.php?combined=1";
			ac_config.vw_combined=1;
			title +="( Overlay )";
			break;
		case 1: // Orden de salida
			page = "/agility/livestream/vwls_parciales.php?combined=1";
			ac_config.vw_combined=1;
			title +="( Partial scores )";
			break;
		case 2: // Resultados Parciales
			page = "/agility/livestream/vwls_ordensalida.php?combined=1";
			ac_config.vw_combined=1;
			title +="( Starting order )";
			break;
		case 3: // Live Stream OSD
			page = "/agility/livestream/vwls_osdvideo.php?combined=0";
			ac_config.vw_combined=0;
			title +="( Overlay )";
			break;
		case 4: // resultados parciales con livestream
			page = "/agility/livestream/vwls_parciales.php?combined=0";
			ac_config.vw_combined=0;
			title +="( Partial scores )";
			break;
		case 5: // Resultados Parciales
			page = "/agility/livestream/vwls_ordensalida.php?combined=0";
			ac_config.vw_combined=0;
			title +="( Starting order )";
			break;
	}
	$('#selvw-dialog').dialog('close');
	$('#vw_contenido').load(	
			page,
			function(response,status,xhr){
				document.title=title;
				if (status=='error') {
					$('#vw_contenido').load('/agility/console/frm_notavailable.php');
					return;
				}
				if (ac_config.vw_combined==0) return; // do not embedd video, just declare chroma key
				var bg=workingData.datosSesion.Background;
				var ls1=workingData.datosSesion.LiveStream;
				var ls2=workingData.datosSesion.LiveStream2;
				var ls3=workingData.datosSesion.LiveStream3;
				if ( bg !== '' ) $('#vwls_video').attr('poster', bg);
				if ( ls1!== '' ) $('#vwls_videomp4').attr('src', ls1); else $('#vwls_videomp4').remove();
				if ( ls2!== '' ) $('#vwls_videoogv').attr('src', ls2); else $('#vwls_videoogv').remove();
				if ( ls3!== '' ) $('#vwls_videowebm').attr('src', ls3); else $('#vwls_videowebm').remove();
				// if LiveStream is present load and play assigned session's livestream url
				var video=$('#vwls_video')[0];
				if (!video) return; // no video tag found
				video.load();
				video.play();
			}
		);
}

</script>
</body>
</html> 
