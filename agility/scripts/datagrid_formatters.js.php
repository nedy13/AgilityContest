/*
 datagrid_formatters.js.php

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
require_once(__DIR__."/../server/auth/Config.php");
require_once(__DIR__."/../server/tools.php");
$config =Config::getInstance();
?>

/* formatters generales */

function formatBold(val,row,idx) { return '<span style="font-weight:bold">'+val+'</span>'; }
function formatBoldBig(val,row,idx) { return '<span style="font-weight:bold;font-size:1.5em;">'+val+'</span>'; }
function formatBorder(val,row,idx) { return 'border-left: 1px solid #000;'; }
function formatGrado(val,row,idx) {
    var fed=workingData.federation;
    if (typeof (ac_fedInfo[fed]) === "undefined") return val;
    if (typeof (ac_fedInfo[fed].ListaGradosShort[val]) === "undefined") return val;
    return ac_fedInfo[fed].ListaGradosShort[val];
}
function formatCategoria(val,row,idx) {
    var fed=workingData.federation;
    if (typeof (ac_fedInfo[fed]) === "undefined") return val;
    if (typeof (ac_fedInfo[fed].ListaCategoriasShort[val]) === "undefined") return val;
    return ac_fedInfo[fed].ListaCategoriasShort[val];
}
/* formatters para el datagrid dlg_resultadosManga */
function formatPuesto(val,row,idx) { return '<span style="font-weight:bold">'+((row.Penalizacion>=100)?"-":val)+'</span>'; }
function formatPuestoBig(val,row,idx) { return '<span style="font-size:1.5.em;font-weight:bold">'+((row.Penalizacion>=100)?"-":val)+'</span>'; }
function formatVelocidad(val,row,idx) { return (row.Penalizacion>=200)?"-":toFixedT(parseFloat(val),1); }
function formatTiempo(val,row,idx) { return (row.Penalizacion>=200)?"-":toFixedT(parseFloat(val),ac_config.numdecs); }
function formatPenalizacion(val,row,idx) { return toFixedT(parseFloat(val),ac_config.numdecs); }
function formatEliminado(val,row,idx) { return (row.Eliminado==0)?"":'<?php _e("Elim"); ?>'; }
function formatNoPresentado(val,row,idx) { return (row.NoPresentado==0)?"":'<?php _e("N.P."); ?>'; }

/* formaters para el frm_clasificaciones */
function formatPuestoFinal(val,row,idx) { return '<span style="font-weight:bold">'+((row.Penalizacion>=200)?"-":val)+'</span>'; }
function formatPuestoFinalBig(val,row,idx) {
    return '<span style="font-size:1.5em;font-weight:bold">'+((row.Penalizacion>=400)?"-":val)+'</span>';
}

function formatPenalizacionFinal(val,row,idx) {
    var p=row.Penalizacion;
    if (p>=800) return "-";
    if (p>=400) return p-400;
    return toFixedT(parseFloat(val),ac_config.numdecs);
}

function formatV1(val,row,idx) { return (row.P1>=200)?"-":toFixedT(parseFloat(val),1); }
function formatTP(val,p,idx) {
    if (p>=400) return '-';
    if (p>=200) return '0';
    return toFixedT(parseFloat(val),ac_config.numdecs);
}

function formatT1(val,row,idx) { return formatTP(val,row.P1,idx); }
function formatP1(val,row,idx) { return formatTP(val,row.P1,idx); }
function formatV2(val,row,idx) { return (row.P2>=200)?"-":toFixedT(parseFloat(val),1); }
function formatT2(val,row,idx) { return formatTP(val,row.P2,idx); }
function formatP2(val,row,idx) { return formatTP(val,row.P2,idx); }
function formatTF(val,row,idx) {
    var t=parseFloat(row.T1)+parseFloat(row.T2);
    return (row.Penalizacion>=200)?"-":toFixedT(t,ac_config.numdecs);
}

function formatCatGrad(val,row,idx) {
    var hasGrade=true;
    if (isJornadaEqMejores()) hasGrade=false;
    if (isJornadaEqConjunta()) hasGrade=false;
    if (isJornadaOpen()) hasGrade=false;
    if (!hasGrade) return formatCategoria(val,row,idx);
    // return formatCategoria(row.Categoria,row.idx)+"/"+formatGrado(row.Grado,row,idx);
    return row.Categoria+"-"+formatGrado(row.Grado,row,idx); // not enoght space in column :-(
}

/**
 * Return short name for requested federation. Use to format datagrid cell
 * @param {int} val Federation ID
 * @param {int} row unused
 * @param {int} idx unused
 * @returns {string} requested value or index if not found
 */
function formatFederation(val,row,idx) {
    if (typeof(val)==='undefined') return "";
    var v=parseInt(val);
    if (typeof(ac_fedInfo[v])==="undefined") return val;
    return ac_fedInfo[v].Name;
}

/* stylers para formateo de celdas especificas */
function formatOk(val,row,idx) { return (parseInt(val)==0)?"":"&#x2714;"; }
function formatNotOk(val,row,idx) { return (parseInt(val)!=0)?"":"&#x2714;"; }
function formatCerrada(val,row,idx) { return (parseInt(val)==0)?"":"&#x26D4;"; }
function formatRing(val,row,idx) { return (val==='-- Sin asignar --')?"":val; }
function formatCelo(val,row,idx) { return (parseInt(val)==0)?" ":"&#x2665;"; }
function checkPending(val,row,idx) { return ( parseInt(row.Pendiente)!=0 )? 'color: #f00;': ''; }
function competicionRowStyler(idx,row) { return (row.Dorsal=='*')? myRowStyler(-1,row) : myRowStyler(idx,row); }
function formatOrdenSalida(val,row,idx) { return '<span style="font-size:1.5em;font-weight:bold;height:40px;line-height:40px">'+(1+idx)+'</span>'; }
function formatDorsal(val,row,idx) { return '<span style="font-size:1.5em;font-weight:bold;height:40px;line-height:40px">'+val+'</span>'; }

function formatOrdenLlamadaPista(val,row,idx) { if (val<=0) return ""; return '<span style="font-weight:bold;font-size:1.5em;">'+val+'</span>'; }
function formatLlamadaGuia(val,row,idx) { if (row.Orden>0) return val; return '<span style="font-weight:bold;font-size:1.4em;">'+val+'</span>'; }

/**
 * Return logo matching requested cell value
 * @param val logo name
 * @param row row data
 * @param idx row index
 * @returns {string} html string to be inserted 
 */
function formatLogo(val,row,idx) {
    // TODO: no idea why idx:0 has no logo declared
    if (typeof(val)==='undefined') val="empty.png";
    var fed=workingData.federation;
    return '<img src="/agility/images/logos/getLogo.php?Fed='+fed+'&Logo='+val+'" width="30" height="30" alt="'+val+'"/>\n';
}

/**
 * Return list of logos matching requested team cell value
 * Iterate competitor list searching for team members. add their logo team if not yet done
 * @param val not used. just for compatibility with datagrid formatters
 * @param row row data
 * @param idx row index
 * @returns {string} html string to be inserted 
 */
function formatTeamLogos(val,row,idx) {
    var logos=[];
    if (typeof (workingData.individual) === 'undefined' ) return "logo logo logo logo";
    for(var n=0; n<workingData.individual.length;n++) {
        var competitor=workingData.individual[n];
        if (competitor['Equipo']!=row.ID) continue;
        if ($.inArray(competitor['LogoClub'],logos)<0) logos.push(competitor['LogoClub']);
        if (logos.length>=4) break; // TODO: replace with maxdogs
    }
    var str="";
    var fed=workingData.federation;
    for (n=0;n<logos.length;n++) {
        str +='<img src="/agility/images/logos/getLogo.php?Fed='+fed+'&Logo='+logos[n]+'" width="30" height="30" alt="'+logos[n]+'"/>\n';
    }
    return str;
}

/* comodity function to set up round SCT unit based on SCT type */
function round_setUnit(tipo,dest) {
    if (tipo==0) $(dest).val('s'); // fixed SCT: set unit to seconds
    if (tipo==6) $(dest).val('m'); // Velocity instead of time/percent: set unit to mts/sec
}

function formatTeamResults( name,value , rows ) {
    // todo: check eq3 or eq4 contest and eval time and penalization
    var time=0.0;
    var penal=0.0;
    var logos="";
    // var width=($('#header-combinadaFlag').text()==='true')?500:1000;
    var width= 0.9 * parseInt($(name).css('width').replace('px',''));
    var mindogs=getMinDogsByTeam();
    function addLogo(logo) {
        if (logos.indexOf(logo)>=0) return;
        logos = logos + '&nbsp;<img height="40px" src="/agility/images/logos/'+ logo + '"/>';
    }
    for (var n=0;n<mindogs;n++) {
        if ( typeof(rows[n])==='undefined') {
            penal+=400.0;
            addLogo('null.png');
        } else {
            penal+=parseFloat(rows[n].Penalizacion);
            time+=parseFloat(rows[n].Tiempo);
            addLogo(rows[n].LogoClub);
        }
    }
    var width=toPercent($(name).datagrid('getPanel').panel('options').width,90);
    // return "Equipo: "+value+" Tiempo: "+time+" Penalizaci&oacute;n: "+penal;
    return '<div class="vw_equipos3" style="width:'+width+'px">'+
        '<span style="width:'+toPercent(width,20)+'px;text-align:left;">'+logos+'</span>'+
        '<span style="width:'+toPercent(width,45)+'px;text-align:right;">'+value+'</span>' +
        '<span style="width:'+toPercent(width,10)+'px;text-align:right;">T: '+toFixedT(time,ac_config.numdecs)+'</span>' +
        '<span style="width:'+toPercent(width,10)+'px;text-align:right;">P:'+toFixedT(penal,ac_config.numdecs)+'</span>'+
        '<span style="width:'+toPercent(width,10)+'px;text-align:right;">'+(workingData.teamCounter++)+'</span>'+
        '</div>';
}

function formatVwTeamResults(value,rows) { return formatTeamResults('#vw_parciales-datagrid',value,rows); }
function formatPbTeamResults(value,rows) { return formatTeamResults('#pb_parciales-datagrid',value,rows); }

function formatTeamResultsConsole( value , rows ) {
    // todo: check eq3 or eq4 contest and eval time and penalization
    var time=0.0;
    var penal=0.0;
    var mindogs=getMinDogsByTeam();
    for (var n=0;n<mindogs;n++) {
        if ( typeof(rows[n])==='undefined') {
            penal+=400.0;
        } else {
            penal+=parseFloat(rows[n].Penalizacion);
            time+=parseFloat(rows[n].Tiempo);
        }
    }
    // return "Equipo: "+value+" Tiempo: "+time+" Penalizaci&oacute;n: "+penal;
    return '<div class="vw_equipos3" style="width:640px">'+
        '<span style="width:35%;text-align:left;"><?php _e('Team'); ?>: '+value+'</span>' +
        '<span style="width:25%;text-align:right;"><?php _e('Time'); ?>: '+toFixedT(time,ac_config.numdecs)+'</span>' +
        '<span style="width:25%;text-align:right;"><?php _e('Penal'); ?>.:'+toFixedT(penal,ac_config.numdecs)+'</span>'+
        '<span style="width:10%;text-align:right;font-size:1.5em">'+(workingData.teamCounter++)+'</span>'+
        '</div>';
}

function formatTeamClasificaciones(dgname,value,rows) {
    var logos="";
    var mindogs=getMinDogsByTeam();
    function sortResults(a,b) {
        return (a.penal== b.penal)? (a.time - b.time) : (a.penal - b.penal);
    }
    function addLogo(logo) {
        if (logos.indexOf(logo)>=0) return;
        logos = logos + '&nbsp;<img height="40px" src="/agility/images/logos/'+ logo + '"/>';
    }
    // cogemos y ordenamos los datos de cada manga
    var manga1={ time:0.0, penal:0.0, perros:[] };
    var manga2={ time:0.0, penal:0.0, perros:[] };
    for (var n=0;n<4;n++) {
        if (typeof(rows[n]) === 'undefined') {
            manga1.perros[n] = {time: parseFloat(0.0), penal: parseFloat(400.0)};
            manga2.perros[n] = {time: parseFloat(0.0), penal: parseFloat(400.0)};
            addLogo('null.png');
        } else {
            manga1.perros[n] = {time: parseFloat(rows[n].T1), penal: parseFloat(rows[n].P1)};
            manga2.perros[n] = {time: parseFloat(rows[n].T2), penal: parseFloat(rows[n].P2)};
            addLogo(rows[n].LogoClub);
        }
    }
    // ordenamos ahora las matrices de resultados
    (manga1.perros).sort(sortResults);
    (manga2.perros).sort(sortResults);
    // y sumamos los dos/tres/cuatro primeros ( en funcion del tipo de competicion de equipos ) resultados
    for (n=0;n<mindogs;n++) {
        manga1.time +=parseFloat(manga1.perros[n].time);
        manga1.penal +=parseFloat(manga1.perros[n].penal);
        manga2.time +=parseFloat(manga2.perros[n].time);
        manga2.penal +=parseFloat(manga2.perros[n].penal);
    }
    // el resultado final es la suma de las mangas
    var time=manga1.time+manga2.time;
    var penal=manga1.penal+manga2.penal;
    var m1="T1: "+toFixedT((manga1.time),ac_config.numdecs)+" -- P1: "+toFixedT((manga1.penal),ac_config.numdecs);
    var m2="T2: "+toFixedT((manga2.time),ac_config.numdecs)+" -- P2: "+toFixedT((manga2.penal),ac_config.numdecs);
    var mf="<?php _e('Time');?>: "+toFixedT(time,ac_config.numdecs)+" -- <?php _e('Penal');?>: "+toFixedT(penal,ac_config.numdecs);

    var width=toPercent($(dgname).datagrid('getPanel').panel('options').width,90); // let expand button to exist
    // return "Equipo: "+value+" Tiempo: "+time+" Penalizaci&oacute;n: "+penal;
    return '<div class="vw_equipos3" style="width:'+width+'px">'+
        '<span style="width:'+toPercent(width,15)+'px;text-align:left;">'+logos+'</span>'+
        '<span style="width:'+toPercent(width,20)+'px;text-align:left;">'+value+'</span>' +
        '<span style="width:'+toPercent(width,15)+'px;text-align:left;">'+m1+'</span>' +
        '<span style="width:'+toPercent(width,15)+'px;text-align:left;">'+m2+'</span>'+
        '<span style="width:'+toPercent(width,25)+'px;text-align:right;">'+mf+'</span>'+
        '<span style="width:'+toPercent(width,5)+'px;text-align:right;font-size:1.25vw;">'+(workingData.teamCounter++)+'</span>'+
        '</div>';
}

function formatVwTeamClasificaciones(value,rows) { return formatTeamClasificaciones('#vwcf_clasificacion-datagrid',value,rows); }
