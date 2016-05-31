<?php
require_once(__DIR__."/../server/tools.php");
require_once(__DIR__."/../server/auth/Config.php");
require_once(__DIR__."/../server/auth/AuthManager.php");
$config =Config::getInstance();
$am = new AuthManager("Public::inscripciones_equipos");
if ( ! $am->allowed(ENABLE_PUBLIC)) { include_once("unregistered.php"); return 0;}
?>

<!--
pb_inscripciones_eq3.inc

Copyright  2013-2016 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 -->

<!-- Presentacion de las inscripciones de la jornada -->
<div id="pb_inscripciones-window">
	<div id="pb_inscripciones-layout" style="width:100%">
		<div id="pb_inscripciones-Cabecera" data-options="region:'north',split:false" style="height:10%;" class="pb_floatingheader">
            <a id="pb_header-link" class="easyui-linkbutton" onClick="pb_updateInscripciones_eq3();" href="#" style="float:left">
                <img id="pb_header-logo" src="/agility/images/logos/agilitycontest.png" width="50" />
            </a>
		    <span style="float:left;padding:10px" id="pb_header-infocabecera"><?php _e('Header'); ?></span>
			<span style="float:right;" id="pb_header-texto"><?php _e('Inscription list'); ?></span>
		</div>
		<div id="pb_inscripciones-data" data-options="region:'center'" >
			<table id="pb_inscripciones_eq3-datagrid"></table>
		</div>
        <div id="pb_inscripciones-footer" data-options="region:'south',split:false" style="height:10%;" class="pb_floatingfooter">
            <span id="pb_footer-footerData"></span>
        </div>
	</div>
</div> <!-- pb_inscripciones-window -->

<script type="text/javascript">

// fire autorefresh if configured
// var rtime=parseInt(ac_config.web_refreshtime);
// if (rtime!=0) setInterval(pb_updateInscripciones_eq3,1000*rtime);

addTooltip($('#pb_header-link').linkbutton(),'<?php _e("Update inscription list"); ?>');
$('#pb_inscripciones-layout').layout({fit:true});
$('#pb_inscripciones-window').window({
	fit:true,
	noheader:true,
	border:false,
	closable:false,
	collapsible:false,
	collapsed:false,
	resizable:false,
	callback: null, 
	// 1 minute poll is enouth for this, as no expected changes during a session
	onOpen: function() {
        // generate header
        pb_getHeaderInfo();
        // generate footer
        pb_setFooterInfo();
	},
	onClose: function() { 
        // do not auto-refresh in inscriptions
		// clearInterval($(this).window.defaults.callback);
	}
});

// datos de la tabla de equipos
$('#pb_inscripciones_eq3-datagrid').datagrid({
    fit: true,
    url: '/agility/server/database/equiposFunctions.php',
    queryParams: { Operation:'select', Prueba:workingData.prueba, Jornada:workingData.jornada, where:''	},
    loadMsg: "<?php _e('Updating inscriptions');?> ...",
    method: 'get',
    mode: 'remote',
    multiSort: true,
    remoteSort: true,
    idField: 'ID',
    columns: [[
        { field:'ID',			hidden:true },
        { field:'Prueba',		hidden:true },
        { field:'Jornada',		hidden:true },
        { field:'Orden',		hidden:true },
        { field:'Nombre',		width:20, sortable:true,	title: '<?php _e('Team');?>' },
        { field:'Categorias',	width:10, sortable:true,	title: '<?php _e('Cat');?>.' },
        { field:'Observaciones',width:65, sortable:true,	title: '<?php _e('Comments');?>'},
        { field:'Miembros',		hidden:true },
        { field:'DefaultTeam',	width:5, sortable:false,	align: 'center', title: 'Def', formatter:formatOk }
    ]],
    pagination: false,
    fitColumns: true,
    singleSelect: true,
    view: scrollview,
    pageSize: 50,
    rowStyler: function(idx,row){ // set proper colors on team rows
        // TODO study why this doesn't work
        // return {'class':'pb_inscripciones_eq3_teamrow'};
        return "background-color:<?php echo $config->getEnv('vw_hdrbg2')?>;"+
            "color:<?php echo $config->getEnv('vw_hdrfg2')?>;"+
            "font-weight:bold;"+
            "height:40px;"+
            "line-height: 40px;";
    },
    // especificamos un formateador especial para desplegar la tabla de inscritos por equipo
    detailFormatter:function(idx,row){
        return '<div style="padding:2px"><table id="pb_inscripciones_eq3-datagrid-' + replaceAll(' ','_',row.ID) + '"></table></div>';
    },
    onExpandRow: function(idx,row) {
        showInscripcionesByTeam(idx,row);
    },
    onLoadSuccess: function(data) {
        /*
        function fireUp(index) {
            setTimeout(function() {dg.datagrid('expandRow',index);},1000*index);
        }
        var dg = $('#pb_inscripciones_eq3-datagrid');
        var count = dg.datagrid('getRows').length;
        for(var i=0; i<count; i++) fireUp(i);
        */
    }
});

//mostrar las inscripciones agrupadas por equipos
function showInscripcionesByTeam(index,team){
    // - sub tabla de participantes asignados a un equipo
    var mySelf='#pb_inscripciones_eq3-datagrid-'+replaceAll(' ','_',team.ID);
    $(mySelf).datagrid({
        width: '100%',
        height: 'auto',
        pagination: false,
        rownumbers: false,
        fitColumns: true,
        singleSelect: true,
        loadMsg: '<?php _e('Updating inscriptions');?> ...',
        url: '/agility/server/database/inscripcionFunctions.php',
        queryParams: { Operation: 'inscritosbyteam', Prueba:workingData.prueba, Jornada:workingData.jornada, Equipo: team.ID },
        method: 'get',
        autorowheight:true,
        columns: [[
            { field:'ID',		hidden:true }, // inscripcion ID
            { field:'Prueba',	hidden:true }, // prueba ID
            { field:'Jornadas',	hidden:true }, // bitmask de jornadas inscritas
            { field:'Perro',	hidden:true }, // dog ID
            { field:'Equipo',	hidden:true }, // only used on Team contests
            { field:'Pagado', 	hidden:true }, // to store if handler paid :-)
            { field:'Guia', 	hidden:true }, // Guia ID
            { field:'Club',		hidden:true }, // Club ID
            { field:'LOE_RRC',	hidden:true }, // LOE/RRC
            { field:'Club',		hidden:true }, // Club ID
            { field:'Dorsal',	    width:'5%',        sortable:false, align: 'center',	title: '<?php _e('Dorsal'); ?>',formatter:formatDorsal },
            { field:'LogoClub',     width:'7%',        sortable:false, align: 'center',	title: '',formatter:formatLogo },
            { field:'Nombre',	    width:'15%',       sortable:false, align: 'center',	title: '<?php _e('Name'); ?>',formatter:formatBoldBig },
            { field:'Raza',	        width:'15%',        sortable:false, align: 'center',title: '<?php _e('Breed');    ?>' },
            { field:'Licencia',	    width:'7%',        sortable:false, align: 'center',title: '<?php _e('Lic');    ?>' },
            { field:'Categoria',    width:'3%',        sortable:false, align: 'center',title: '<?php _e('Cat');    ?>',formatter:formatCategoria },
            // { field:'Grado',	width:6,        sortable:false, align: 'center',title: '<?php _e('Grade');  ?>',formatter:formatGrado },
            { field:'NombreGuia',	width:'18%',   sortable:false, align: 'right',	title: '<?php _e('Handler'); ?>' },
            { field:'NombreClub',	width:'18%',   sortable:false, align: 'right',	title: '<?php _e('Club');   ?>' },
            { field:'NombreEquipo',	hidden:true },
            { field:'Observaciones',width:'7%',                                   title: '<?php _e('Comments');?>' },
            { field:'Celo',		    width:'5%', align:'center', formatter: formatCelo,	title: '<?php _e('Heat');   ?>' }
        ]],
        // colorize rows. notice that overrides default css, so need to specify proper values on datagrid.css
        rowStyler:myRowStyler,
        // on double click fireup editor dialog
        onResize:function(){
            $('#pb_inscripciones_eq3-datagrid').datagrid('fixDetailRowHeight',index);
        },
        onLoadSuccess:function(){
            setTimeout(function(){
                $('#pb_inscripciones_eq3-datagrid').datagrid('fixDetailRowHeight',index);
            },0);
        }
    }); // end of inscritos-by-team_team_id
    $('#pb_inscripciones_eq3-datagrid').datagrid('fixDetailRowHeight',index);
} // end of showPerrosByTeam

</script>