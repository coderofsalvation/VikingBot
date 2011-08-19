<?php

set_time_limit(0);
error_reporting(E_ALL);

if(!is_file("config.php")) {
	die("You have not created a config.php yet.\n");
}
require("config.php");
require("lib/functions.php");
require("lib/pluginInterface.php");

class VikingBot {

	var $socket;
	var $inChannel = false;
	var $startTime;
	var $plugins;
	var $config;

	function __construct($config) {
		$this->config = $config;
		$this->socket = stream_socket_client("".$config['server'].":".$config['port']) or die("Connection error!");
		stream_set_blocking($this->socket, 0);
		stream_set_timeout($this->socket, 600);
		$this->login();
		$this->loadPlugins();
		$this->startTime = time();
		$this->main($config);
	}

	function loadPlugins() {
		$handle = opendir('plugins');
		while (false !== ($file = readdir($handle))) {
			if(stringEndsWith($file, '.php')) {
				require('plugins/'.$file);
				$pName = str_replace('.php', '', $file);
				$this->plugins[] = new $pName();
			}
		}
		foreach($this->plugins as $plugin) {
			$plugin->init($this->socket);
		}
	}

	function login() {
		$this->sendData('PASS', $this->config['pass']);
		$this->sendData('NICK', $this->config['nick']);
		$this->sendData('USER', $this->config['name']." 0 *: ".$this->config['name']);
	}

	function main() {

		//Sleep a bit, no need to hog all CPU resources
		usleep(500000);

		//Join channels if not already joined
		if( !$this->inChannel && (time() - $this->config['waitTime']) > $this->startTime ) {
                        $this->joinChannel($this->config['channel']);
                        sleep(2);
                	$this->inChannel = true;
		}
	
		//Tick plugins
		foreach($this->plugins as $plugin) {
			$plugin->tick();
		}

		//Load data from IRC server
		$data = fgets($this->socket, 256);
		if(strlen($data) > 0) {
			echo "<Server to bot> ".$data;	
			$bits = explode(' ', $data);
			if($bits[0] == 'PING') {
				$this->sendData('PONG', $bits[1]); //Ping? Pong!
			}

			if(isset($bits[3])) {
				$cmd = trim($bits[3]);
				switch($cmd) {
					case ':!exit':
						$this->shutdown($bits);
					break;

					case ':!restart':
						$this->restart($bits);
					break;
				}
			}

			if($bits[1] == 'PRIVMSG') {

				$msg = substr($bits[3], 1);
				for($i=4; $i<count($bits); $i++) {
					$msg .= ' '.$bits[$i];
				}
				$msg = trim($msg);

				$from = getNick($bits[0]);
				$chan = trim($bits[2]);

				if($chan[0] != '#') {
					$chan = $from;
				}
			
				foreach($this->plugins as $plugin) {
					$plugin->onMessage($from, $chan, $msg);	
				}
			}
		}
		
		//Move along
		$this->main();
	}

	function sendData($cmd, $msg = null) {
		if($msg == null) {
			fwrite($this->socket, $cmd."\r\n");
			echo '<Bot to server> '.$cmd."\n";
		} else {
			fwrite($this->socket, $cmd.' '.$msg."\r\n");
			echo '<Bot to server> '.$cmd.' '.$msg."\n";
		}
	}

	function joinChannel($channel) {
		echo "Joining channel {$channel}\n";
		if(is_array($channel)) {
			foreach($channel as $chan) {
				$this->sendData('JOIN', $chan);
			}
		} else {
			$this->sendData('JOIN', $channel);
		}
	}
	

	function restart($args) {
		if(!$this->correctAdminPass($args[4])) {
                        $this->privMsg(getNick($args[0]), "Wrong password");
                        return false;
                }
		$this->privMsg(getNick($args[0]), "Restarting...");
		$this->prepareShutdown();
		die(exec('sh start.sh > /dev/null &'));
	}
	
	function prepareShutdown() {
                $this->sendData('QUIT', 'VikingBot - https://github.com/Ueland/VikingBot');
                foreach($this->plugins as $plugin) {
                        $plugin->destroy();
                }
	}

	function shutdown($args) {

		if(!$this->correctAdminPass($args[4])) {
			$this->privMsg(getNick($args[0]), "Wrong password");
			return false;
		}
		$this->privMsg(getNick($args[0]), "Shutting down...");
		$this->prepareShutdown();
		exit;
	}

	function privMsg($user, $msg) {
		$this->sendData("PRIVMSG {$user} :".$msg);
	}

	function correctAdminPass($pass) {
		$pass = trim($pass);
		if(strlen($this->config['adminPass']) > 0) {
			if($pass == $this->config['adminPass']) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
}

//Start the bot
$bot = new VikingBot($config);
