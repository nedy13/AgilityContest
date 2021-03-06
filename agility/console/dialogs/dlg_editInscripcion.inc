<!-- 
dlg_editInscripcion.inc

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

<?php
require_once(__DIR__ . "/../../server/tools.php");
require_once(__DIR__ . "/../../server/auth/Config.php");
$config =Config::getInstance();
?>

<!-- FORMULARIO DE EDICION DE INSCRIPCIONES -->
<div id="edit_inscripcion-dialog" style="width:550px;height:475px;padding:5px">
	<div class="easyui-panel" data-options="fit:true">
		<div class="dialog_forms">
        <form id="edit_inscripcion-form" method="get" novalidate="novalidate">
			<input id="edit_inscripcion-ID" type="hidden" name="ID"/>
        	<input id="edit_inscripcion-Prueba" type="hidden" name="Prueba"/>
        	<input id="edit_inscripcion-Jornadas" type="hidden" name="Jornadas"/>
        	<input id="edit_inscripcion-Operation" type="hidden" name="Operation" value="update"/>
            <input id="edit_inscripcion-LOE_RRC" name="LOE_RRC" type="hidden" />
            <input id="edit_inscripcion-Raza" name="Raza" type="hidden" />
           	<input id="edit_inscripcion-Perro" name="Perro" type="hidden" />
           	<span  id="edit_inscripcion-dogInfo" 	class="ftitle"><?php _e('Dog information'); ?>:</span>
           	<span  id="edit_inscripcion-inscInfo" 	class="ftitle" style="text-align:center"><?php _e('Inscription data'); ?>:</span>
           	<label id="edit_inscripcion-Nombrelbl"	for="edit_inscripcion-Nombre"><?php _e('Name'); ?>:</label>
           	<input id="edit_inscripcion-Nombre" 	name="Nombre" type="text" disabled="disabled" />
        	<label id="edit_inscripcion-Licencialbl" for="edit_inscripcion-Licencia"><?php _e('License'); ?>: </label>
        	<input id="edit_inscripcion-Licencia"	name="Licencia" type="text" disabled="disabled"/>
        	<label id="edit_inscripcion-Categorialbl" for="edit_inscripcion-Categoria"><?php _e('Category'); ?>: </label>
            <input id="edit_inscripcion-Categoria" 	name="Categoria" type="text" style="text-align:center" disabled="disabled"/>
        	<label id="edit_inscripcion-Gradolbl"	for="edit_inscripcion-Grado"><?php _e('Grade'); ?>: </label>
            <input id="edit_inscripcion-Grado" 		name="Grado" type="text" style="text-align:center" disabled="disabled" />
            <label id="edit_inscripcion-NombreGuialbl"	for="edit_inscripcion-NombreGuia"><?php _e('Handler'); ?>: </label>
            <input id="edit_inscripcion-NombreGuia" name="NombreGuia" type="text" disabled="disabled"/>
            <label id="edit_inscripcion-NombreClublbl" for="edit_inscripcion-NombreClub"><?php _e('Club'); ?>: </label>
           	<input id="edit_inscripcion-NombreClub" name="NombreClub" type="text" disabled="disabled" />
			<input type="hidden" name="J1" value="0"/> <!-- stupid form handling of val==0 -->
           	<input id="edit_inscripcion-J1" 		name="J1" class="easyui-checkbox" type="checkbox" value="1" onclick="doMoney(this);"/>
           	<label id="edit_inscripcion-J1lbl" 		for="edit_inscripcion-J1">J1</label>
			<input type="hidden" name="J2" value="0"/>
            <input id="edit_inscripcion-J2" 		name="J2" class="easyui-checkbox" type="checkbox" value="2" onclick="doMoney(this);"/>
            <label id="edit_inscripcion-J2lbl" 		for="edit_inscripcion-J2">J2</label>
			<input type="hidden" name="J3" value="0"/>
            <input id="edit_inscripcion-J3"			name="J3" class="easyui-checkbox" type="checkbox" value="4" onclick="doMoney(this);"/>
            <label id="edit_inscripcion-J3lbl"		for="edit_inscripcion-J3">J3</label>
			<input type="hidden" name="J4" value="0"/>
            <input id="edit_inscripcion-J4"			name="J4" class="easyui-checkbox" type="checkbox" value="8" onclick="doMoney(this);"/>
 	        <label id="edit_inscripcion-J4lbl"		for="edit_inscripcion-J4">J4</label>
			<input type="hidden" name="J5" value="0"/>
            <input id="edit_inscripcion-J5"			name="J5" class="easyui-checkbox" type="checkbox" value="16" onclick="doMoney(this);"/>
            <label id="edit_inscripcion-J5lbl" 		for="edit_inscripcion-J5">J5</label>
			<input type="hidden" name="J6" value="0"/>
            <input id="edit_inscripcion-J6" 		name="J6" class="easyui-checkbox" type="checkbox" value="32" onclick="doMoney(this);"/>
            <label id="edit_inscripcion-J6lbl" 		for="edit_inscripcion-J6">J6</label>
			<input type="hidden" name="J7" value="0"/>
            <input id="edit_inscripcion-J7" 		name="J7" class="easyui-checkbox" type="checkbox" value="64" onclick="doMoney(this);"/>
            <label id="edit_inscripcion-J7lbl" 		for="edit_inscripcion-J7">J7</label>
			<input type="hidden" name="J8" value="0"/>
            <input id="edit_inscripcion-J8" 		name="J8" class="easyui-checkbox" type="checkbox" value="128" onclick="doMoney(this);"/>
            <label id="edit_inscripcion-J8lbl" 		for="edit_inscripcion-J8">J8</label>
            <label id="edit_inscripcion-Celolbl"	for="edit_inscripcion-Celo2"><?php _e('Heat'); ?>:</label>
           	<input id="edit_inscripcion-Celo" 		name="Celo" type="hidden" value="0"/>
            <input id="edit_inscripcion-Celo2"		name="Celo2" class="easyui-checkbox" type="checkbox" value="1" />
	        <label id="edit_inscripcion-Observacioneslbl" for="edit_inscripcion-Observaciones"><?php _e('Comments'); ?>:</label>
	        <input id="edit_inscripcion-Observaciones" type="text" name="Observaciones"/>
	        <label id="edit_inscripcion-Pagadolbl"	for="edit_inscripcion-Pagado"><?php _e('Paid out'); ?>: </label>
 	  		<input id="edit_inscripcion-Pagado" 	name="Pagado" type="text" style="text-align:right;padding-right:5px"/>
 	  		<span id="edit_inscripcion-PagadoEuro">&euro;</span>
        </form> 
    	</div>
	</div>
</div>

    
    <!-- BOTONES DE ACEPTAR / CANCELAR DEL CUADRO DE DIALOGO -->
	<div id="edit_inscripcion-dlg-buttons" style="width:100%;display:inline-block;padding:5px">
		<span style="float:left;">
	   		<a id="edit_inscripcion-editDogBtn" href="#" class="easyui-linkbutton"
	   			data-options="iconCls:'icon-dog'"><?php _e('Modify'); ?></a>
	   	</span>
	   	<span style="float:right;">  	
			<a id="edit_inscripcion-cancelBtn" href="#" class="easyui-linkbutton"
				data-options="iconCls:'icon-cancel'" onclick="$('#edit_inscripcion-dialog').dialog('close');"><?php _e('Cancel'); ?></a>
       		<a id="edit_inscripcion-okBtn" href="#" class="easyui-linkbutton" onclick="saveInscripcion(true)" 
       			data-options="iconCls:'icon-save'"><?php _e('Accept'); ?></a>
       	</span>
    </div>
    
    <script type="text/javascript">

    $('#edit_inscripcion-dialog').dialog({
    	iconCls:'icon-notes',
    	closed:true,
    	modal:true,
    	buttons:'#edit_inscripcion-dlg-buttons',
    	title:'<?php _e('Edit inscription'); ?>',
		onClose:function() {
			// update row and scroll to it
			var form=$('#edit_inscripcion-form').form();
			var dg=$('#inscripciones-datagrid');
			var id=$('#edit_inscripcion-ID').val();
			var idx=dg.datagrid('getRowIndex',id);
			if (idx<0) return;
			var frm=formToObject(form);
			dg.datagrid('updateRow',{ index:idx , row:frm } );
			dg.datagrid('scrollTo',idx);
		}
    });
    
    // declaracion del formulario
    $('#edit_inscripcion-form').form({
        onLoadSuccess: function(data) {
            // retrieve info on jornadas
        	var jornadas=$('#inscripciones-jornadas').datagrid('getData')['rows'];
            // evaluate celo checkbox status
        	$('#edit_inscripcion-Celo2').prop('checked',(data.Celo != 0));
            // evaluate checked status of jornadas
        	$('#edit_inscripcion-J1').prop('checked',(data.Jornadas&0x0001)?true:false);
        	$('#edit_inscripcion-J2').prop('checked',(data.Jornadas&0x0002)?true:false);
        	$('#edit_inscripcion-J3').prop('checked',(data.Jornadas&0x0004)?true:false);
        	$('#edit_inscripcion-J4').prop('checked',(data.Jornadas&0x0008)?true:false);
        	$('#edit_inscripcion-J5').prop('checked',(data.Jornadas&0x0010)?true:false);
        	$('#edit_inscripcion-J6').prop('checked',(data.Jornadas&0x0020)?true:false);
        	$('#edit_inscripcion-J7').prop('checked',(data.Jornadas&0x0040)?true:false);
        	$('#edit_inscripcion-J8').prop('checked',(data.Jornadas&0x0080)?true:false);
        	// fix text for jornadas
        	$('#edit_inscripcion-J1lbl').text('J1 - '+jornadas[0].Nombre);
        	$('#edit_inscripcion-J2lbl').text('J2 - '+jornadas[1].Nombre);
        	$('#edit_inscripcion-J3lbl').text('J3 - '+jornadas[2].Nombre);
        	$('#edit_inscripcion-J4lbl').text('J4 - '+jornadas[3].Nombre);
        	$('#edit_inscripcion-J5lbl').text('J5 - '+jornadas[4].Nombre);
        	$('#edit_inscripcion-J6lbl').text('J6 - '+jornadas[5].Nombre);
        	$('#edit_inscripcion-J7lbl').text('J7 - '+jornadas[6].Nombre);
        	$('#edit_inscripcion-J8lbl').text('J8 - '+jornadas[7].Nombre);
        	// evaluate enabled/disabled status by mean of check "Cerrada" and "-- Sin asignar --"
        	$('#edit_inscripcion-J1').prop('disabled', ! canInscribe(jornadas[0]) );
        	$('#edit_inscripcion-J2').prop('disabled', ! canInscribe(jornadas[1]) );
        	$('#edit_inscripcion-J3').prop('disabled', ! canInscribe(jornadas[2]) );
        	$('#edit_inscripcion-J4').prop('disabled', ! canInscribe(jornadas[3]) );
        	$('#edit_inscripcion-J5').prop('disabled', ! canInscribe(jornadas[4]) );
        	$('#edit_inscripcion-J6').prop('disabled', ! canInscribe(jornadas[5]) );
        	$('#edit_inscripcion-J7').prop('disabled', ! canInscribe(jornadas[6]) );
        	$('#edit_inscripcion-J8').prop('disabled', ! canInscribe(jornadas[7]) );
        }
    });
    
    // tooltips
    addTooltip($('#edit_inscripcion-editDogBtn').linkbutton(),'<?php _e("Change dog data"); ?>'+"<br/>"+'<?php _e("into database"); ?>');
    addTooltip($('#edit_inscripcion-okBtn').linkbutton(),'<?php _e("Accept changes"); ?>'+"<br/>"+'<?php _e("Update inscription data"); ?>');
    addTooltip($('#edit_inscripcion-cancelBtn').linkbutton(),'<?php _e("Cancel changes. Close window"); ?>');
    
 	// acciones de los botones

    $('#edit_inscripcion-editDogBtn').bind('click',function() {
        editInscribedDog();
    });
    
    // campos del formulario
    
    function doMoney(obj) {
        // current values
    	var jornadas=$('#edit_inscripcion-Jornadas').val();
        var money=Number($('#edit_inscripcion-Pagado').val());
        // evaluate jornadas
    	if (obj.checked) $('#edit_inscripcion-Jornadas').val(jornadas | obj.value);
    	else $('#edit_inscripcion-Jornadas').val(jornadas & ~obj.value);
    	// evaluate money
        if (obj.checked) $('#edit_inscripcion-Pagado').val(money+12);
        else $('#edit_inscripcion-Pagado').val(money-12);
    }

    // layout
    var dg= {'cols':8, 'rows':16}; // declare base datagrid
	doLayout(dg,'#edit_inscripcion-dogInfo',		0,	0,	3,	1	);
	doLayout(dg,"#edit_inscripcion-inscInfo",		3,	0,	4,	1	);
	doLayout(dg,"#edit_inscripcion-Nombrelbl",		0,	1,	2,	1	);
	doLayout(dg,"#edit_inscripcion-Nombre",			0,	2,	3,	1	);
	doLayout(dg,"#edit_inscripcion-Licencialbl",	0,	3,	2,	1	);
	doLayout(dg,"#edit_inscripcion-Licencia",		0,	4,	3,	1	);
	doLayout(dg,"#edit_inscripcion-Categorialbl",	0,	5,	1,	1	);
	doLayout(dg,"#edit_inscripcion-Categoria",		0,	6,	1,	1	);
	doLayout(dg,"#edit_inscripcion-Gradolbl",		2,	5,	1,	1	);
	doLayout(dg,"#edit_inscripcion-Grado",			2,	6,	1,	1	);
	doLayout(dg,"#edit_inscripcion-NombreGuialbl",	0,	7,	2,	1	);
	doLayout(dg,"#edit_inscripcion-NombreGuia",		0,	8,	3,	1	);
	doLayout(dg,"#edit_inscripcion-NombreClublbl",	0,	9,	2,	1	);
	doLayout(dg,"#edit_inscripcion-NombreClub",		0,	10,	3,	1	);
	doLayout(dg,"#edit_inscripcion-J1",				3,	1,	1,	1	);
	doLayout(dg,"#edit_inscripcion-J1lbl",			4,	1,	3,	1	);
	doLayout(dg,"#edit_inscripcion-J2",				3,	2,	1,	1	);
	doLayout(dg,"#edit_inscripcion-J2lbl",			4,	2,	3,	1	);
	doLayout(dg,"#edit_inscripcion-J3",				3,	3,	1,	1	);
	doLayout(dg,"#edit_inscripcion-J3lbl",			4,	3,	3,	1	);
	doLayout(dg,"#edit_inscripcion-J4",				3,	4,	1,	1	);
	doLayout(dg,"#edit_inscripcion-J4lbl",			4,	4,	3,	1	);
	doLayout(dg,"#edit_inscripcion-J5",				3,	5,	1,	1	);
	doLayout(dg,"#edit_inscripcion-J5lbl" ,			4,	5,	3,	1	);
	doLayout(dg,"#edit_inscripcion-J6",				3,	6,	1,	1	);
	doLayout(dg,"#edit_inscripcion-J6lbl",			4,	6,	3,	1	);
	doLayout(dg,"#edit_inscripcion-J7" ,			3,	7,	1,	1	);
	doLayout(dg,"#edit_inscripcion-J7lbl",			4,	7,	3,	1	);
	doLayout(dg,"#edit_inscripcion-J8",				3,	8,	1,	1	);
	doLayout(dg,"#edit_inscripcion-J8lbl",			4,	8,	3,	1	);
	doLayout(dg,"#edit_inscripcion-Celolbl",		4,	10,	1,	1	);
	doLayout(dg,"#edit_inscripcion-Celo2",			5,	10,	1,	1	);
	doLayout(dg,"#edit_inscripcion-Pagadolbl",		4,	11,	1,	1	);
	doLayout(dg,"#edit_inscripcion-Pagado",			5,	11,	1,	1	);
	doLayout(dg,"#edit_inscripcion-PagadoEuro",		6,	11,	1,	1	);
	doLayout(dg,"#edit_inscripcion-Observacioneslbl",0,	11,	3,	1	);
	doLayout(dg,"#edit_inscripcion-Observaciones",	0,	12,	7,	1	);
    </script>