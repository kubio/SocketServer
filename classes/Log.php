<?php

class Log{
	public static function INFO($message){
		self::displayMessage('INFO',self::getDate(),$message);
	}
	
	public static function WARNING($message){
		self::displayMessage('WARNING',self::getDate(),$message);
	}

	public static function NOTICE($message){
		self::displayMessage('NOTICE',self::getDate(),$message);
	}
	
	public static function ERROR($message){
		self::displayMessage('ERROR',self::getDate(),$message);
	}

	public static function EXCEPTION($message){
		self::displayMessage('EXCEPTION',self::getDate(),$message);
	}
		
	private static function getDate(){
		return date('Y/m/d H:i:s');
	}
	
	private static function displayMessage($type, $date, $message){
		$br = '
';
		if(is_array($message)){
			echo sprintf('[%s] %s ==============='.$br, $type, $date);
			var_dump($message);
			echo '==========================';
		}else{
			echo sprintf('[%s] %s %s'.$br, $type, $date, $message);
		}
	}
}