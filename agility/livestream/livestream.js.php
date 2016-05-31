/*
livestream.js.php

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

<?php
require_once(__DIR__."/../server/tools.php");
require_once(__DIR__."/../server/auth/Config.php");
$config =Config::getInstance();
?>

function vwls_enableOSD(val) {
	var title=document.title;
	var str="-"
	if (val==0) {
		str=" - OSD:OFF";
		$('#vwls_common').css('display','none');
	} else {
		str=" - OSD:ON";
		$('#vwls_common').css('display','initial');
	}
	document.title=title.replace(/ -.*/,"")+str;
}

function vwls_showRoundInfo(val) {
	if (val==0) $('#vwls_mangasInfo').css('display','none');
	else $('#vwls_mangasInfo').css('display','initial');
}

function vwls_showCompetitorInfo(val) {
	if (val==0) $('#vwls_competitorInfo').css('display','none');
	else $('#vwls_competitorInfo').css('display','initial');
}

function vwls_showResultsInfo(val) {
	var disp=(val==0)?'none':'initial';
	if (parseInt(ac_config.vw_dataposition)==0) disp='none';
	$('#vwls_resultadosInfo').css('display',disp);
}

function vwls_keyBindings() {
	// capture <space> key to switch OSD on/off
	$(document).keydown(function(e) {
		if (e.which != 32) return true; // not space
		var div=$('#vwls_common');
		var state=div.css('display');
		setTimeout(function(){vwls_enableOSD( (state==='none')?1:0);},0 );
		return true; // to allow continue event chain
	});
}

function vwls_showData(data) {
	var perro=$('#vwls_Perro').html();
	var vwls_tiempo=$('#vwls_Tiempo');
	var dorsal=data['Dorsal'];
	var numero=data['Numero'];
	var celo=parseInt(data['Celo']);
	if (perro!==data['Perro']) {
		// if datos del participante han cambiado actualiza
		$.ajax({
			type: "GET",
			url: "/agility/server/database/dogFunctions.php",
			data: {
				'Operation' : 'getbyidperro',
				'ID'	: data['Perro']
			},
			async: true,
			cache: false,
			dataType: 'json',
			success: function(res){
				$('#vwls_Numero').html(numero);
				$('#vwls_Logo').attr("src","/agility/images/logos/"+res['LogoClub']);
				$('#vwls_Dorsal').html(dorsal );
				$('#vwls_Perro').html(res["ID"]);
				$('#vwls_Nombre').html(res["Nombre"]);
				$('#vwls_NombreGuia').html(res["NombreGuia"]);
				$('#vwls_Cat').html(res["Categoria"]);
                $('#vwls_Categoria').html(toLongCategoria(res["Categoria"],res['Federation']));
                // hide "Grado" Information if not applicable
                $('#vwls_Grado').html(hasGradosByJornada(workingData.datosJornada)?res["NombreGrado"]:"");
                // on Team events, show Team info instead of Club
                var eq=workingData.teamsByJornada[data["Equipo"]].Nombre;
                // como en el videowall no tenemos datos de la jornada, lo que hacemos es
                // contar el numero de equipos de esta para saber si es prueba por equipos o no
                $('#vwls_NombreClub').html((Object.keys(workingData.teamsByJornada).length>1)?eq:res["NombreClub"]);
				$('#vwls_Celo').html((celo==1)?'<span class="blink">Celo</span>':'');
			},
			error: function(XMLHttpRequest,textStatus,errorThrown) {
				alert("error: "+textStatus + " "+ errorThrown );
			}
		});
	}
	// actualiza resultados del participante
	$('#vwls_Faltas').html(data["Faltas"]);
	$('#vwls_Tocados').html(data["Tocados"]);
	$('#vwls_Rehuses').html(data["Rehuses"]);
	$('#vwls_TIntermedio').html(data["TIntermedio"]);
	var e=parseInt(data["Eliminado"]);
	if (e>=0) {
		$('#vwls_Eliminado').html(e);
		$('#vwls_EliminadoLbl').html((e==0)?'':'<span class="blink" style="color:red"><?php _e('Elim');?>.</span>');
	}
	var n=parseInt(data["NoPresentado"]);
	if (n>=0) {
		$('#vwls_NoPresentado').html(n);
		$('#vwls_NoPresentadoLbl').html((n==0)?'':'<span class="blink" style="color:red"><?php _e('NoPr');?>.</span>');
	}
	vwls_tiempo.html(data["Tiempo"]);
}

/**
 * evaluate and display position for this dog
 * @param {boolean} flag: true:evaluate, false:clear
 * @param {float} tiempo datatime from chronometer
 */
function vwls_displayPuesto(flag,time) {
	// if requested, turn off data
	var perro=$('#vwls_Perro').text();
	if (!flag || (perro==0) ) { $('#vwls_PuestoLbl').html(''); return; }
	// use set timeout to make sure data are already refreshed
	setTimeout(function(){
		// phase 1 retrieve results
		// use text() instead of html() avoid extra html code
		var datos= {
			'Perro':	perro,
			'Categoria':$('#vwls_Cat').text(),
			'Grado':	$('#vwls_Grado').text(),
			'Faltas':	$('#vwls_Faltas').text(),
			'Tocados':	$('#vwls_Tocados').text(),
			'Rehuses':	$('#vwls_Rehuses').text(),
			'Eliminado':$('#vwls_Eliminado').text(),
			'NoPresentado':$('#vwls_NoPresentado').text(),
			'Tiempo':	time
		};
		// phase 2: do not call server if eliminado or not presentado. Also don't display anything (already done)
		if ( (datos.NoPresentado=="1") || (datos.Eliminado=="1")) { $('#vwls_PuestoLbl').html(''); return; }
		// phase 3: call server to evaluate partial result position
		getPuestoFinal(datos,function(data,resultados){
			$('#vwls_PuestoLbl').html('- '+Number(resultados.puesto).toString()+' -');
		});
	},0);
}
