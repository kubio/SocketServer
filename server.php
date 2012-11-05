<?php
ob_implicit_flush(true);	//定期的にサーバーからデータを送ってブラウザからの切断を防ぐ

require("./classes/Server.php");
require("./classes/Client.php");
require("./classes/Log.php");
require("./classes/Socket.php");

$sv = new Server();
$sv->run();