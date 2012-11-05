<?php
class Server{
	public static $sockets;
	public static $clients;
	
	private static $server;

	public static function run()
	{
		$context = stream_context_create();
		$handshake = false;
		self::$server = stream_socket_server("tcp://localhost:8634", $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context );
		self::$sockets[] = self::$server;
		Client::setServer($this);
		while(true){
			$allSockets = self::$sockets;
			@stream_select($allSockets, $write = null, $except = null, 0, 200000);		//	読み込み可能になったものを返すよう設定
			foreach($allSockets as $socket){
				if($socket == self::$server){
					if(($clientSocket = stream_socket_accept(self::$server)) === FALSE){
				        //接続入れないエラー
				        Log::ERROR('Socket error: ' . socket_strerror(socket_last_error($socket)));
				        continue;
				    }

			    	$client = new Client($clientSocket);
			    	
			    	self::$clients[(int)$clientSocket] = $client;
			    	self::$sockets[] = $clientSocket;

				}else{
					$thisClient = self::$clients[(int)$socket];
					if(!is_object($thisClient))
					{
						unset(self::$clients[(int)$socket]);
						continue;
					}
					$data = Client::getSocketBuffer($socket);
					
					
					$bytes = strlen($data);
					
					if($bytes === 0)
					{
						//normal closure
						Log::NOTICE('normal Closure');
						unset(self::$clients[$thisClient->getSocket()]);
						$index = array_search($thisClient->getSocket(), self::$sockets);
						unset(self::$sockets[$index], $thisClient);
						continue;
					}
					elseif($data === false)
					{
						//Timeout or Error
						Log::NOTICE('TimeOut?');
						continue;
					}
					//elseif($client->waitingForData === false && $this->_checkRequestLimit($client->getClientId()) === false)
					//{
					//	$client->onDisconnect();
					//}
					else
					{
						foreach(self::$clients as $client){
							if($thisClient == $client){
								$client->onLine($data);
							}else{
								$client->onLine($data);
							}
						}
					}
				}
			}
		}
	}
	
	public static function getSocketInfo($socket){
		
	}
}