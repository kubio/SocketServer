<?php
class Client{
	private $_socket;
	private $_handshake;
	
	private $_ip;
	private $_port;
	
	function __construct($socket){
		$this->_socket = $socket;
		
		// set some client-information:				
		$socketName = stream_socket_get_name($socket, true);
		$tmp = explode(':', $socketName);		
		$this->_ip = $tmp[0];
		$this->_port = $tmp[1];
		
		Log::INFO("Connected:".$socketName);
	}
	
	public function getSocket(){
		return $this->_socket;
	}
	
	public function setConnection($data){
		
		//handshake
    	$lines = preg_split("/\r\n/", $data);
    	
    	// generate headers array:
		$headers = array();
        foreach($lines as $line)
		{
            $line = chop($line);
            if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
                $headers[$matches[1]] = $matches[2];
            }
        }
        
        // do handyshake: (hybi-10)
        
		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$response = "HTTP/1.1 101 Switching Protocols\r\n";
		$response.= "Upgrade: websocket\r\n";
		$response.= "Connection: Upgrade\r\n";
		$response.= "Sec-WebSocket-Accept: " . $secAccept . "\r\n";
		if(isset($headers['Sec-WebSocket-Protocol']) && !empty($headers['Sec-WebSocket-Protocol']))
		{
			$response.= "Sec-WebSocket-Protocol: " . substr($path, 1) . "\r\n";
		}
		$response.= "\r\n";
		
		if(false === @fwrite($this->_socket, $response)){
			Log::Error('書き込み失敗');
		}else{
			Log::INFO('書き込み成功');
		}
		
		$this->_handshake = true;
		
		return true;
	}
	
	public function onLine($data){
		if(!$this->_handshake){
			$this->setConnection($data);
		}else{
			$this->send(Socket::hybi10Decode($data));
		}
	}
	
	public function send($data){
		if(!(is_array($data)&&isset($data['type'])&&isset($data['payload']))){
			Log::ERROR('Failed Send Request');
			return false;
		}
		
		if(empty($data['payload'])){
			Log::ERROR('Empty Parameter');
			return false;
		}

		if(false === $this->write(Socket::hybi10Encode($data['payload'], $data['type'], false))){
			Log::Error('書き込み失敗');
		}else{
			Log::INFO('書き込み成功');
		}
		
		
	}
	
	private function write($data){
		if(false === ( @fwrite($this->_socket, $data, strlen($data)) ) )
		{
			//return false;
			Log::Error('書き込み失敗');
		}else{
			
			Log::INFO('書き込み成功');
		}
	}
	
	
	/**
	 * Static ---------------------------------------
	 */
	private static $_server;
	public static function setServer(&$server){
		self::$_server = $server;
	}
	public static function getServer(){
		return self::$_server;
	}
	
	public static function getSocketBuffer($socket){
		//ReadBuffer
		$buffer = '';
		$buffsize = 8192;
		$metadata['unread_bytes'] = 0;
		
		while(!feof($socket)){
			$res = fread($socket, $buffsize);
			if($res === false || feof($socket))
			{
				return false;
			}				
			$buffer .= $res;				
			$metadata = stream_get_meta_data($socket);			
			$buffsize = ($metadata['unread_bytes'] > $buffsize) ? $buffsize : $metadata['unread_bytes'];
			if($metadata['unread_bytes'] <= 0){
				break;
			}
		}
		
		return $buffer;
	}
}