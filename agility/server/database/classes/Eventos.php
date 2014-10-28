<?php

// How often to poll, in microseconds (1,000,000 μs equals 1 s)
define('EVENT_POLL_MICROSECONDS', 500000);
// How long to keep the Long Poll open, in seconds
define('EVENT_TIMEOUT_SECONDS', 30);
// Timeout padding in seconds, to avoid a premature timeout in case the last call in the loop is taking a while
define('EVENT_TIMEOUT_SECONDS_BUFFER', 5);

require_once("DBObject.php");

class Eventos extends DBObject {
	
	static $event_list = array (
		0  => 'null',		// null event: no action taken
		1  => 'open',		// operator starts tablet application
		2  => 'datos',		// actualizar datos (si algun valor es -1 o nulo se debe ignorar)
		8  => 'llamada',	// operador abre panel de entrada de datos
		9  => 'salida',		// juez da orden de salida ( crono 15 segundos )
		10 => 'cronomanual',// value: timestamp
		11 => 'cronoauto',  // value: timestamp
		12 => 'aceptar',	// operador pulsa aceptar
		13 => 'cancelar'	// operador pulsa cancelar
	);
	
	protected $sessionID;
	protected $sessionFile;
	
	/**
	 * Constructor
	 * @param {string} $file caller for this object
	 * @param {integer} $id Session ID
	 * @throws Exception if cannot contact database or invalid Session ID
	 */
	function __construct($file,$id) {
		parent::__construct($file);
		if ( $id<=0 ) {
			$this->errormsg="$file::construct() invalid Session:$id ID";
			throw new Exception($this->errormsg);
		}
		$this->sessionID=$id;
		$this->sessionFile=__DIR__."/../../../../logs/events.$id";
		// nos aseguramos de que el fichero de sesion exista
		if ( ! file_exists($this->sessionFile) ) touch($this->sessionFile);
	}
	
	/**
	 * Insert a new event into database
	 * @return {string} "" if ok; null on error
	 */
	function putEvent($data) {
		$this->myLogger->enter();
		$sid=$this->sessionID;
		
		// prepare statement
		$sql = "INSERT INTO Eventos ( TimeStamp,Session, Source, Type, Data ) VALUES (?,$sid,?,?,?)";
		$stmt=$this->conn->prepare($sql);
		if (!$stmt) return $this->error($this->conn->error);
		$res=$stmt->bind_param('ssss',$timestamp,$source,$type,$evtdata);
		if (!$res) return $this->error($this->conn->error);
		
		// iniciamos los valores
		$timestamp= date('Y-m-d G:i:s');
		$source=$data['Source'];
		$type=$data['Type'];
		$evtdata=json_encode($data);
		
		// invocamos la orden SQL y devolvemos el resultado
		$res=$stmt->execute();
		if (!$res) return $this->error($this->conn->error);
		
		// retrieve EventID on newly create event
		$data['TimeStamp']=$timestamp;
		$data['ID']=$this->conn->insert_id;
		$stmt->close();
		
		// and save content to event file
		$str=json_encode($data);
		file_put_contents($this->sessionFile,$str."\n", FILE_APPEND | LOCK_EX);
		
		// that's all.
		$this->myLogger->leave();
		return ""; 
	}
	
	/** 
	 * (Server side implementation of LongCall ajax)
	 * Ask for events
	 * If no new events, wait for event available until timeout
	 * @see http://www.nolithius.com/game-development/comet-long-polling-with-php-and-jquery
	 * @see http://www.abrandao.com/2013/05/11/php-http-long-poll-server-push/
	 */
	function getEvents($data) { 
		$this->myLogger->enter();
		
		// Close the session prematurely to avoid usleep() from locking other requests
		// notice that cannot call http_request after this item
		session_write_close();
		
		// Automatically die after timeout (plus buffer)
		set_time_limit(EVENT_TIMEOUT_SECONDS+EVENT_TIMEOUT_SECONDS_BUFFER);
		
		// retrieve timestamp from file and request
		$current=filemtime($this->sessionFile);
		$last=$data['TimeStamp'];
		
		// Counter to manually keep track of time elapsed 
		// (PHP's set_time_limit() is unrealiable while sleeping)
		$counter = EVENT_TIMEOUT_SECONDS;
		$res=null;
		
		// Poll for messages and hang if nothing is found, until the timeout is exhausted
		while($counter > 0)	{
			// $this->myLogger->info("filemtime:$current lastquery:$last" );
			if ( $current > $last ) {
				// new data has arrived: get it
				$res=$this->listEvents($data);
				if ( is_array($res)) $res['TimeStamp']=$current; // data received: store timestamp in response
				break;
			}
			// Otherwise, sleep for the specified time, after which the loop runs again
			usleep(EVENT_POLL_MICROSECONDS);
			// clear stat cache to ask for real mtime
			clearstatcache();
			$current =filemtime($this->sessionFile);
			// Decrement seconds from counter (the interval was set in μs, see above)
			$counter -= EVENT_POLL_MICROSECONDS / 1000000;
		}
		// if no new events (timeout) create an empty result
		if ($res===null) $res=array( 'total'=>0, 'rows'=>array(), 'TimeStamp' => $current );
		$this->myLogger->leave();
		return $res;
	}
	
	/**
	 * As getEvents() but don't wait for new events, just list existing ones
	 * @param {array} $data requested event info
	 * @return available events for session $data['Session'] with id greater than $data['ID']
	 */
	function listEvents($data) {
		if ($data['Session']<=0) return $this->error("No Session ID specified");
		$this->myLogger->enter();
		$result=$this->__select(
				/* SELECT */ "*",
				/* FROM */ "Eventos",
				/* WHERE */ "( Session = {$data['Session']} ) AND ( ID > {$data['ID']} )",
				/* ORDER BY */ "ID",
				/* LIMIT */ ""
		);
		$this->myLogger->leave();
		return $result;
	}
	
	/**
	 * Retrieve last "open" event with provided Session ID
	 * Used for clients to retrieve event ID index
	 * SELECT * from Eventos
	 *		WHERE  ( Session = {$data['Session']} ) AND ( Type = 'open' )
	 *		ORDER BY ID DESC LIMIT 1
	 * @param {array} $data requested event info
	 * @return {array} data about last "open" event with provided session id
	 */
	function connect($data) {
		if ($data['Session']<=0) return $this->error("No Session ID specified");
		$this->myLogger->enter();
		$result=$this->__select(
				/* SELECT */ "*",
				/* FROM */ "Eventos",
				/* WHERE */ "( Session = {$data['Session']} ) AND ( Type = 'open' )",
				/* ORDER BY */ "ID DESC",
				/* LIMIT */ "0,1"
						);
		$this->myLogger->leave();
		return $result;
	}
}
?>