<?php

/*
mangaFunctions.php

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


require_once(__DIR__."/../logging.php");
require_once(__DIR__."/../tools.php");
require_once(__DIR__."/../auth/AuthManager.php");
require_once(__DIR__."/classes/Mangas.php");

try {
	$result=null;
	$prueba=http_request("Prueba","i",0);
	$jornada=http_request("Jornada","i",0);
	$operation=http_request("Operation","s",null);
	$manga=http_request("Manga","i",0);
	if ($operation===null) throw new Exception("Call to mangaFunctions without 'Operation' requested");
	$mangas= new Mangas("mangaFunctions",$jornada);
	$am= new AuthManager("mangaFunctions");
	switch ($operation) {
		// no direct "insert", as created/destroyed from jornadaFunctions
		case "update": $am->access(PERMS_OPERATOR); $result=$mangas->update($manga); break;
		case "sharejuez": $am->access(PERMS_OPERATOR); $result=$mangas->shareJuez(); break;
		// no direct delete as created/destroyed from jornadaFunctions
		case "enumerate": 	$result=$mangas->selectByJornada(); break;
		case "swap": 	$result=$mangas->swapMangas($manga); break;
		case "getbyid":		$result=$mangas->selectByID($manga); break;
		default: throw new Exception("mangaFunctions:: invalid operation: $operation provided");
	}
	if ($result===null) 
		throw new Exception($mangas->errormsg);
	if ($result==="") 
		echo json_encode(array('success'=>true,'insert_id'=>0,'affected_rows'=>0));
	else echo json_encode($result);
} catch (Exception $e) {
	do_log($e->getMessage());
	echo json_encode(array('errorMsg'=>$e->getMessage()));
}

?>