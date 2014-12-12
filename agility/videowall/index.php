<?php 
require_once(__DIR__."/../server/auth/Config.php");
$config =new Config()
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
<title>Agility Contest</title>
<link rel="stylesheet" type="text/css" href="/agility/lib/jquery-easyui-1.4.1/themes/<?php echo $config->getEnv('easyui_theme'); ?>/easyui.css" />
<link rel="stylesheet" type="text/css" href="/agility/lib/jquery-easyui-1.4.1/themes/icon.css" />
<link rel="stylesheet" type="text/css" href="/agility/css/style.css" />
<link rel="stylesheet" type="text/css" href="/agility/css/datagrid.css" />
<link rel="stylesheet" type="text/css" href="/agility/css/videowall.css" />
<script src="/agility/lib/jquery-easyui-1.4.1/jquery.min.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/jquery-easyui-1.4.1/jquery.easyui.min.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/jquery-fileDownload-1.4.2.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/jquery-chronometer.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/lib/jquery-fittext-1.2.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/scripts/common.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/scripts/competicion.js" type="text/javascript" charset="utf-8" > </script>
<script src="/agility/videowall/videowall.js" type="text/javascript" charset="utf-8" > </script>
<style>
body { font-size: 100%;	background: <?php echo $config->getEnv('easyui_bgcolor'); ?>; }
</style>
</head>

<body style="margin:0;padding:0;background-color:blue;font-size:100%">
<div id="vw_contenido" style="width:inherit;height:inherit;margin:0;padding:0">

<!--  CUERPO PRINCIPAL DE LA PAGINA (se modifica con el menu) -->

<div id="selvw-dialog" class="easyui-dialog" style="position:relative,width:500px;height:220px;padding:20px 20px">
	<form id="selvw-Selection">
    	<div class="fitem">
       		<label for="Prueba">Selecciona Sesi&oacute;n:</label>
       		<select id="selvw-Session" name="Session" style="width:200px"></select>
    	</div>
    	<div class="fitem">
       		<label for="Vista">Selecciona Vista:</label>
       		<select id="selvw-Vista" name="Vista" style="width:200px">
       		<option value="0">Listado de Inscritos</option>
       		<option value="1">Orden de Salida</option>
       		<option value="2">Llamada a pista</option>
       		<option value="3">Resultados Provisionales</option>
       		<option value="4">Clasificaciones</option>
       		<option value="5">Live Stream OSD</option>
       		<option value="6">Vista Combinada</option>
       		</select>
    	</div>
    	
	</form>
</div> <!-- Window -->

<div id="selvw-Buttons" style="text-align:right">
   	<a id="selvw-okBtn" href="#" class="easyui-linkbutton" 
   	   	data-options="iconCls: 'icon-ok'" onclick="vw_accept()">Aceptar</a>
</div>	<!-- botones -->

<script type="text/javascript">
$('#selvw-dialog').dialog({
	title: 'Datos de la Vista a desplegar',
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

addTooltip($('#selvw-okBtn').linkbutton(),"Trabajar con la sesión seleccionada");

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
		{ field:'Nombre',		width:'25%', sortable:false,   align:'center',  title: 'Nombre' },
		{ field:'Comentario',	width:'60%', sortable:false,   align:'left',  title: 'Observaciones' },
		{ field:'LiveStream',	hidden:true }
	]],
	onBeforeLoad: function(param) { 
		param.Operation='enumerate'
		return true;
	}
});

function vw_accept() {
	// si prueba invalida cancelamos operacion
	var s=$('#selvw-Session').combogrid('grid').datagrid('getSelected');
	if ( s===null ) {
		// indica error
		$.messager.alert("Error","Debe indicar una sesion v&aacute;lidas","error");
		return;
	}
	// clear selection to make sure next time gets empty
	$('#selvw-Session').combogrid('setValue','');
	$('#selvw-Jornada').combogrid('setValue','');
	
	// store selected data into global structure
	workingData.sesion=s.ID;
	workingData.nombreSesion=s.Nombre;
	initWorkingData(s.ID);
	var page="'/agility/client/frm_notavailable.php";
	var n=parseInt($('#selvw-Vista').val());
	switch (n){
	case 0: //Listado de Inscritos
		page="/agility/videowall/vw_inscripciones.inc";
		break;
	case 1: // Ordenes de Salida
		page="/agility/videowall/vw_ordensalida.inc";
		break;
	case 2: // Llamada a pista
		page="/agility/videowall/vw_llamada.inc";
		break;
	case 3: // Resultados Parciales
		page="/agility/videowall/vw_parciales.inc";
		break;
	case 4: // Clasificaciones
		page="/agility/videowall/vw_clasificaciones.inc";
		break;
	case 5: // Live Stream OSD
		page="/agility/videowall/vw_livestream.inc";
		break;
	case 6: // Vista Combinada
		page="/agility/videowall/vw_combinada.inc";
		break;
	}
	$('#selvw-dialog').dialog('close');
	$('#vw_contenido').load(	
			page,
			function(response,status,xhr){
				if (status=='error') $('#vw_contenido').load('/agility/client/frm_notavailable.php');
				else { 
					// if LiveStream is present load and play assigned session's livestream url
					var video=$('#vwls_video')[0];
					if (!video) return;
					video.src = s.LiveStream;
					video.load();
					video.play();
				}
			}
		);
}

/*
//handle auto-resize font
function vwls_setOSDScale() {
	var factor = 0.25;
	var height = $('#vwls_LiveStream').height();
	var max = 600;
	var min = 30;
	var size = factor * height; //Multiply the width of the body by the scaling factor:
	if (size > max) size = max;
	if (size < min) size = min; //Enforce the minimum and maximums
	$('#vwls_common').css('font-size',size+'%');
}

$(document).ready(function() {
	// trigger resize event
	$(window).resize(function(){ vwls_setOSDScale(); });
});
*/

</script>
</div> <!-- contenido -->
</body>
</html> 