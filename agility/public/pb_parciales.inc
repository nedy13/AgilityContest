<!-- 
pb_parciales.inc

Copyright 2013-2015 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 -->


<!-- Presentacion de resultados parciales -->

<div id="pb_parciales-window">
	<div id="pb_selector" style="float:right;padding:20px">
		<select id="pb_enumerateParciales" style="width:200px"></select>
	</div>
	<div id="pb_parciales-Resultados" class="easyui-panel"
		data-options="noheader:true,border:false,closable:false,collapsible:false,collapsed:false">
			<span id="pb_resultadosParciales"></span>
	</div>
</div> <!-- pb_parciales-window -->

<script type="text/javascript">

$('#pb_enumerateParciales').combogrid({
	panelWidth: 300,
	panelHeight: 150,
	idField: 'ID',
	textField: 'Nombre',
	url: '/agility/server/database/resultadosFunctions.php',
	method: 'get',
	required: true,
	multiple: false,
	fitColumns: true,
	singleSelect: true,
	editable: false,  // to disable tablet keyboard popup
	selectOnNavigation: true, // let use cursor keys to interactive select
	columns: [[
	   	    {field:'ID',hidden:true},
			{field:'Nombre',title:'Resultados disponibles',width:50,align:'right'},
			{field:'Prueba',hidden:true},
			{field:'Jornada',hidden:true},
			{field:'Manga',hidden:true},
			{field:'TipoManga',hidden:true},
			{field:'Mode',hidden:true},
	]],
	onBeforeLoad: function(param) { 
		param.Operation='enumerateResultados';
		param.Prueba= workingData.prueba;
		param.Jornada= workingData.jornada;
		param.Manga= 1; // fake data to get Resultados constructor working
		return true;
	},
	onChange:function(value){
		var row=$('#pb_enumerateParciales').combogrid('grid').datagrid('getSelected');
		if (!row) return;
		workingData.manga=row.Manga;
		workingData.tanda=0; // fake tanda. use manga+mode to evaluate results
		workingData.mode=row.Mode;
		alert(JSON.stringify(workingData));
		pb_updateResults();
	}
});

$('#pb_parciales-window').window({
	fit:true,
	noheader:true,
	border:false,
	closable:false,
	collapsible:false,
	collapsed:false,
	resizable:true,
	// 1 minute poll is enouth for this, as no expected changes during a session
	onOpen: function() {
		// call once and then fire as timed task
		pb_updateResults();
		var a=setInterval(pb_updateResults,60000);
		$(this).window.defaults.callback=a; 
	},
	onClose: function() { 
		clearInterval($(this).window.defaults.callback);
	}
});

</script>