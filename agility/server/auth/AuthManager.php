<?php
/*
 AccessControl.php

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
require_once (__DIR__."/../logging.php");
require_once (__DIR__."/../tools.php");
require_once (__DIR__."/../database/classes/Sesiones.php");
require_once (__DIR__."/../database/classes/Eventos.php");
require_once (__DIR__."/Config.php");

// permisos de acceso
define ("PERMS_ROOT",0);
define ("PERMS_ADMIN",1);
define ("PERMS_OPERATOR",2);
define ("PERMS_ASSISTANT",3);
define ("PERMS_GUEST",4);
define ("PERMS_NONE",5);
define ("PERMS_CHRONO",6);

// permisos de ejecucion
define ("ENABLE_IMPORT",1);		// permite importar datos desde ficheros excel
define ("ENABLE_TEAMS",2);		// permite gestionar pruebas de equipos 4
define ("ENABLE_KO",4);			// permite gestionar pruebas K.O
define ("ENABLE_SPECIAL",8);	// permite gestionar pruebas de mangas multiples
define ("ENABLE_VIDEOWALL",16); // permite acceso desde videomarcador
define ("ENABLE_PUBLIC",32);    // permite acceso publico web
define ("ENABLE_CHRONO",64);    // permite gestion desde cronometro
define ("ENABLE_ULIMIT",128);   // permite numero de inscripciones ilimitadas
define ("ENABLE_LIVESTREAM",256);// permite funciones de live-streaming y chroma-key

// datos de registro
define('AC_REGINFO_FILE' , __DIR__."/registration.info");
define('AC_REGINFO_FILE_BACKUP' , __DIR__."/registration.info.old");
define('AC_REGINFO_FILE_DEFAULT' , __DIR__."/registration.info.default");

class AuthManager {
	
	protected $myLogger;
	protected $mySessionKey=null;
	protected $level=PERMS_NONE;
	protected $operador=0;
	protected $mySessionMgr;
    protected $myGateKeeper=null;
	protected $registrationInfo=null;

	// due to a bug in php-5.5 (solved in php-5.6 )
	// we cannot concatenate strings in class properties
	// So must construct this by hand
	function getPK() {
		$str=
			"MIIEIjANBgkqhkiG9w0BAQEFAAOCBA8AMIIECgKCBAEAzgeD27TXHKde3iNMtQSq\n".
			"yAFoeZYVOoPPjGQkFcNamxfGR8rFmgvGrJn28u2bq1dVnIduF9Lj4sPMt9cs/rT+\n".
			"IOnFD3uACZbqku+e39gyrKyu7aZ2t+XTR4IUFKhGYuwr1TRmb/46iXJF7V7ZU9ci\n".
			"q5/A7vCU8XJ9IyInd52yFeSgZdVl4TB/+qqlVJNiyvfpxwHakx/qM+JqjsgXoS+B\n".
			"6Xr9ZChtTh5gljGMxmLQlLLnScwZ7Ku7pnYkZnP/Nb2jT0gaNHBM1af1mpdwpDRD\n".
			"3igilVchrydcknKoF1LRbApgoIbLbzX9QufiJbfKA3ZWtB4c6YL8sSbgIgeHDguS\n".
			"i0obvVFESynMs4WKYtzIIJZEw3G7jXtVHPLoJ56udAT0Moxw6oiVhdGvSejKz/Ik\n".
			"95uPYRMOnlOle+y18UVduHuudKPqoCKFmnub+z8eVLm/aP1SHBpo1l87tDM3w3aT\n".
			"tHwkmAGhQn+udRMCrb0f897XlR7ReNdwppBLLmLR5sdWpAIjpNatw4N2YJG3uehf\n".
			"rHYwlIYovjHVZUbz8D0NxUVJHXv99QB9f37D6D40aiYSA5YnHgkKCq9dkSHeq7GU\n".
			"79fzrCEUihx2d2Nn7tLC3rR/45XI5WU7T61R0N6PPAl8slrOOEnYBs5YlSLH8Sdt\n".
			"Pi4f2+65qK8jVM1fTly4YOvsuA8mPtimTAgMBa0ys0I9RLezwmlbQbXoYwrx7avC\n".
			"SjAe7o3i8g8uGEW+WTPsT87KZdJKRrlr48lMTnonuAbU59phK+b8IAVS3q6rHOyc\n".
			"uwtPRofqBh9Qok3PYYf0Tobc3R8OKi8cX1rXjPGodxFQfcINIAlmLEJxWyMJlM8l\n".
			"rD33czqk2Opx1kYmjkU7zn1x046w0IaUgZqrBCO6K0qMsrfV0VfHf4lAn3WGDY+h\n".
			"2oZlQqmExBcEdLu2aeNwTAq8G1zwS4atF62r2uR4ZEBJNxWfM32kDHKDEEg8selk\n".
			"iD8lxxyGvUgc0/6EboY2JoN0n8QTJbJzC57cqYhYSxh7FekAGZ+xAxA5Ujy+e16H\n".
			"MBKVhRbAj5+Dk803kvej+F6mOx69VekeEVr3C0xlzMslI7wvXY+IZHD+EgLgTFaO\n".
			"TY5ilQ7AjX4id7cXiUHvPpkYsxr9A+ImM3JzdjFkWaKAwmPK6rTbtxA4j86dMktF\n".
			"bKuGHf0lnGPLLNIVrBYfUXa+rdvBlxuZ6dLDaHoq3+3AH0s+5+b3HJCYLY/VMSgV\n".
			"qwCr9Xj8P28JFYziX0aic/0O7Q5Hkp39I7PikB4EJB73NUaYd/UK8EJ1c5zSz2tI\n".
			"LHN0iN/VSKutDHrfZ0om7krDSEyY6TZ/rVDewnFQmbIiIORgig7mjH0EXBUiXBJF\n".
			"VQIDAQAB\n";
		return $str;
	}

	function __construct($file) {
		$config=Config::getInstance();
		$this->myLogger=new Logger($file,$config->getEnv("debug_level"));
		$this->mySessionMgr=new Sesiones("AuthManager");
		/* try to retrieve session token */
		$hdrs= getAllHeaders();
		if (!array_key_exists("X-AC-SessionKey",$hdrs)) {
			// $this->myLogger->info("No sessionKey found in request");
			// no key found: assume anonymous login
			$this->level=PERMS_GUEST;
			return;
		}
		/* if found evaluate for expiration and level */
		$sk=$hdrs['X-AC-SessionKey'];
		$obj=$this->getUserByKey($sk);
		$this->myLogger->info("Username:{$obj->Login} Perms:{$obj->Perms}");
		$this->level=$obj->Perms;
		$this->mySessionKey=$sk;
		$this->operador=$obj->ID;
	}
	
	/**
	 * Localiza al usuario que tiene la SessionKey indicada
	 * @param {string} $sk SessionKey
	 * @return object throw exception
	 * @throws Exception on invalid session key
	 */
	function getUserByKey($sk) {
		$obj=$this->mySessionMgr->__selectObject("*","Sesiones,Usuarios","(Usuarios.ID=Sesiones.Operador) AND ( SessionKey='$sk')");
		if (!$obj) throw new Exception ("Invalid session key: '$sk'");
		$userid=$obj->Operador;
		$this->myLogger->info("SessionKey:'$sk' belongs to userid:'$userid'");
	/*	
		// if token expired throw exception 
		// TODO: write
		// $lastModified=$obj->LastModified;
		// else retrieve permission level
		$obj=$this->mySessionMgr->__getObject("Usuarios",$userid);
		if (!$obj) throw new Exception("Provided SessionKey:'$sk' gives invalid User ID: '$userid'");
	*/
		return $obj;
	}
	
	function checkRegistrationInfo( $file = AC_REGINFO_FILE ) {
		$pub_key="-----BEGIN PUBLIC KEY-----\n" . $this->getPK() . "-----END PUBLIC KEY-----\n";
		if (md5($pub_key)!="ff430f62f2e112d176110b704b256542") return null;
		$fp=fopen ($file,"rb"); $data=fread ($fp,8192); fclose($fp);
		$key=openssl_get_publickey($pub_key);
		if (!$key) { /* echo "Cannot get public key";*/	return null; }
		$res=openssl_public_decrypt(base64_decode($data),$decrypted,$key);
		openssl_free_key($key);
		if (!$res) return null; // faile to decode
        $data=json_decode($decrypted,true);
        if (($data['info']!="") && ($this->myGateKeeper==null))
            $this->myGateKeeper= create_function('$a,$b', $data['info']);
        return $data;
	}

	function getSessionKey() {
		return $this->mySessionKey;
	}

	/**
	 * Retrieve only non-critical subset of registration info stored data
	 * @return {array} NULL
	 */
	function getRegistrationInfo() {
		// singleton retrieve registration data
		if ($this->registrationInfo==null) {
			$this->registrationInfo=$this->checkRegistrationInfo();
		}
		if ($this->registrationInfo==null) return null;
		// now parse information and fix what's is to be exposed
		$data=array();
		foreach ($this->registrationInfo as $key => $value) {
			$data[ucfirst($key)]=$value; // upercase first char
		}
		unset($data["Extra"]); // should not to be exposed
		unset($data["Extra2"]); // should not to be exposed
		$data["Info"]=""; // do not unset, just hide
		$data["User"]=$data["Name"]; // stupid historic naming change
		$data["Expired"]=( strcmp( $data['Expires'] , date("Ymd") ) <0 )?"1":"0";
		return $data;
	}
	
	function registerApp() {
		$this->myLogger->enter();
		// extraemos los datos de registro
		$data=http_request("Data","s",null);
		if (!$data) return array("errorMsg" => "registerApp(): No registration data received");
		if (!preg_match('/data:([^;]*);base64,(.*)/', $data, $matches)) {
			return array("errorMsg" => "registerApp(): Invalid received data format");
		}
		// $type=$matches[1]; // 'application/octet-stream', or whatever. Not really used
		$regdata=base64_decode( $matches[2] ); // decodes received data
		// cogemos los datos de registro y los guardamos en un fichero temporal
		$tmpname = tempnam(sys_get_temp_dir(), 'reginfo');
		$fd=fopen($tmpname,"w");
		fwrite($fd,$regdata);
		fclose($fd);
		// comprobamos que el fichero temporal contiene datos de registro validos
		$info=$this->checkRegistrationInfo($tmpname); // notice: check(), not get()
		if (!$info) return array("errorMsg" => "registerApp(); Invalid registration data");
		umask(002);
		// ok: fichero de registro correcto. copiamos a su ubicacion final
		copy(AC_REGINFO_FILE,AC_REGINFO_FILE_BACKUP);
		rename($tmpname,AC_REGINFO_FILE);
		// guardamos como "activos" y retornamos datos del nuevo registro
		$this->registrationInfo=$info;
		$result=$this->getRegistrationInfo();
		// $result['filename']=$tmpname;
		$this->myLogger->leave();
		return $result;
	}

	/**
	 * Authenticate user.
	 * On Login success create session and if needed send login event
	 * @param {string} $login user name
	 * @param {string} $password user password
	 * @param {integer} $sid requested session id to join to
	 * @throws Exception if something goes wrong
	 * @return {array} errorMessage or result data
	 */
	function login($login,$password,$sid=0,$nosession=false) {
		/* access database to check user credentials */
		$this->myLogger->enter();
		$obj=$this->mySessionMgr->__selectObject("*","Usuarios","(Login='$login')");
		if (!$obj) throw new Exception("Login: Unknown user: '$login'");
		$pw=$obj->Password;
		if (strstr('--UNDEF--',$pw)!==FALSE)
			throw new Exception("Seems that AgilityContest has not been properly configured. Please reinstall");
		else if (strstr('--LOCK--',$pw)!==FALSE)
			throw new Exception("Account '$login' is LOCKED");
		else if (strstr('--NULL--',$pw)===FALSE) { // --NULL-- means no password required
			// unencode stored password
			$pass=base64_decode($pw);
			if (!password_verify($password,$pass)) // check password against stored one
				throw new Exception("Login: invalid password for account '$login'");
		}
		/* Arriving here means login success */
		// get & store permission level
		$this->level=$obj->Perms;
		//create a random session key
		$sk=random_password(16);
		// compose data for a new session
		$data=array (
				// datos para el gestor de sesiones
				'Operador'	=>	$obj->ID,
				'SessionKey'=>  $sk,
				'Nombre' 	=> 	http_request("Nombre","s",""),
				'Prueba' 	=> 	http_request("Prueba","i",0),
				'Jornada'	=>	http_request("Jornada","i",0),
				'Manga'		=>	http_request("Manga","i",0),
				'Tanda'		=>	http_request("Tanda","i",0),
				'Perro'		=>	http_request("Perro","i",0),
				// informacion de respuesta a la peticion
				'UserID'	=>	$obj->ID,
				'Login' 	=> 	$obj->Login,
				'Password'	=>	'', // don't send back password :-)
				'Gecos'		=>	$obj->Gecos,
				'Phone'		=>	$obj->Phone,
				'Email'		=>	$obj->Email,
				'Perms'		=>	$obj->Perms,
				// required for event manager
				'Type'		=>  'init', /* ¿perhaps evtType should be 'login'¿ */
				'Source' 	=> 	http_request("Source","s","AuthManager"),
				'TimeStamp' => 	date('Y-m-d G:i:s')
		);
		// if "nosession" is requested, just check password, do not create any session
		if ($nosession==true) return $data;
		// create/join to a session
		if ($sid<=0) { //  if session id is not defined, create a new session
			// remove all other console sessions from same user
			$str="DELETE FROM Sesiones WHERE ( Nombre='Console' ) AND ( Operador={$obj->ID} )";
			$this->mySessionMgr->query($str);
			// insert new session
			$data['Nombre']="Console";
			$data['Comentario']=$obj->Login." - ".$obj->Gecos;
			$this->mySessionMgr->insert($data);
			// and retrieve new session ID
			$data['SessionID']=$this->mySessionMgr->conn->insert_id;
		} else {
			// to join to a named session we need at least Assistant permission level
			$this->access(PERMS_ASSISTANT); // on fail throw exception
			unset($data['Nombre']); // to avoid override Session Name
			// TODO: check and alert on busy session ID
			// else join to declared session
			$data['SessionID']=$sid;
			$this->mySessionMgr->update($sid,$data);
			// and fire 'login' event
			$evtMgr=new Eventos("AuthManager",$sid,$this);
			$event=array(
					// datos identificativos del evento
					"ID" => 0, 							// Event ID
					"Session" => $sid,  				// Session (Ring) ID
					"TimeStamp" => $data['TimeStamp'],	// TimeStamp - event time
					"Type" => $data['Type'], 			// Event Type
					"Source" => $data['Source'],		// Event Source
					// datos asociados al contenido del evento
					"Pru" => $data['Prueba'],	// Prueba,
					"Jor" => $data['Jornada'],	// Jornada,
					"Mng" => $data['Manga'],	// Manga,
					"Tnd" => $data['Tanda'],	// Tanda,
					"Dog" => $data['Perro'],	// Perro,
					"Drs" => 0,					// Dorsal,
					"Hot" => 0,					// Celo,
					"Flt" => -1,				// Faltas,
					"Toc" => -1,				// Tocados,
					"Reh" => -1,				// Rehuses,
					"NPr" => -1,				// NoPresentado,
					"Eli" => -1,				// Eliminado,
					"Tim" => -1,				// Tiempo,
					// marca de tiempo en los eventos de crono
					"Value" => 0				// Value
			);
			$evtMgr->putEvent($event);
		}
		// That's all. Return generated result data
		// $this->myLogger->info(json_encode($data));
		$this->myLogger->leave();
		$info=$this->getRegistrationInfo();
		return array_merge($data,$info);
	}
	
	function checkPassword($user,$pass) {
		return $this->login($user,$pass,0,true);	
	}
	
	/*
	 * closes current session
	 */
	function logout() {
		// remove console sessions for this user
		$str="DELETE FROM Sesiones WHERE ( Nombre='Console' ) AND ( Operador={$this->operador} )";
		$this->mySessionMgr->query($str);
		// clear session key  on named sessions
		$str="UPDATE Sesiones 
			SET SessionKey=NULL, Operador=1, Prueba=0, Jornada=0, Manga=0, Tanda=0 
			WHERE ( SessionKey='{$this->mySessionKey}' )";
		$this->mySessionMgr->query($str);
        return "";
	}
	
	/**
	 * change password for requested user ID
	 * @param {integer} $id user id to change password to
	 * @param {string} $pass old password
	 * @throws Exception on error
	 * @return string "" on success; else error message
	 */
	function setPassword($id,$pass,$sk) {
		$this->myLogger->enter();
		$u=$this->getUserByKey($sk);
		switch ($u->Perms) {
			case 5:
			case 4: throw new Exception("Guest accounts cannot change password");
				// no break needeed
			case 3:
                // no break
			case 2:	// comprobamos el user id
				if ($id!=$this->operador) throw new Exception("User can only change their own password");
				// comprobamos que la contrasenya antigua es correcta
				$obj=$this->mySessionMgr->__selectObject("*","Usuarios","(ID=$id)");
				if (!$obj) throw new Exception("SetPassword: Unknown userID: '$id'");
				$pw=$obj->Password;
				if (strstr('--LOCK--',$pw)!==FALSE)
					throw new Exception("Cuenta bloqueada. Solo puede desbloquearla un usuario administrador");
				if ( (strstr('--UNDEF--',$pw)!==FALSE) && (strstr('--NULL--',$pw)!==FALSE) ) {
					// unencode stored password
					$op=base64_decode($pw);
					if (!password_verify($pass,$op)) // check password against stored one
						throw new Exception("SetPassword: la contrase&ntilde;a anterior no es v&aacute;lida");
				}
				// no break
			case 1:
			case 0:
				// compare passwors
				$p1=http_request("NewPassword","s","");
				$p2=http_request("NewPassword2","s","");
				if ($p1!==$p2) throw new Exception("Las contrase&ntilde;as no coinciden");
				// and store it for requested user
				$p=base64_encode(password_hash($p1,PASSWORD_DEFAULT));
				$str="UPDATE Usuarios SET Password='$p' WHERE (ID=$id)";
				$res=$this->mySessionMgr->query($str);
				if ($res===FALSE) return $this->mySessionMgr->conn->error;
                $this->myLogger->leave();
				return "";
			default: throw new Exception("Internal error: invalid permission level");
		}
	}
	
	/*
	 * return permissions for provided session token
	 */
	function getPerms() { return $this->level; }
	
	function setPerms($level) { $this->level=$level; }
	
	/* 
	 * check permissions on current session token against required level
	 */
	function access($requiredlevel) {
		if ($requiredlevel==PERMS_CHRONO) {
			// TODO: Chrono operation requires specical id handling
			return true;
		}
		if ($requiredlevel>=$this->level) return true;
		throw new Exception("Insufficient credentials:({$this->level}) required: $requiredlevel");
	}

    function allowed($feature) {
        // retrieve registration data
        $res=$this->getRegistrationInfo();
		if ($res==null) return 0; // invalid license
		if ( $res['Expired']==="1" ) return 0; // license has expired
        // extract and declare inner functions
        $opts=$res['Options'];
		// $this->myLogger->trace("opts:$opts feature:$feature");
        if ($res['Info']==="") return bindec($opts) & $feature; // old style licenses
		/*
        return $this->myGateKeeper($res,$feature);
        $info=str_replace("__OPTS__",$opts,$res['info']);
        return $res;
		*/
		return 1; // TODO: implement new style license coding
    }

    function getUserLimit() {
        $res=$this->getRegistrationInfo();
		if ($res==null) return 75; // invalid license
		if ( $res["Expired"]==="1" ) return 75; // license has expired
        if ($res['Serial']==="00000000") return 75; // unregistered app
        if (bindec($res['Options']) & ENABLE_ULIMIT ) return 9999; // "unlimited"
        return 200; // registered app, but no "unlimited" flag
    }
}

?>
