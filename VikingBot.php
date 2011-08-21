<?php

set_time_limit(0);
error_reporting(E_ALL);
date_default_timezone_set('GMT');
declare(ticks = 1);

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

		//Add signal handlers to shut down the bot correctly if its getting killed
		pcntl_signal(SIGTERM, array($this, "signalHandler"));
		pcntl_signal(SIGINT, array($this, "signalHandler"));

		$this->config = $config;
		ini_set("memory_limit", $this->config['memoryLimit']."M");
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
			$plugin->init($this->config, $this->socket);
		}
	}

	function login() {
		sendData($this->socket, "PASS {$this->config['pass']}");
		sendData($this->socket, "NICK {$this->config['nick']}");
		sendData($this->socket, "USER {$this->config['name']} 0 *: {$this->config['name']}");
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
				sendData($this->socket, "PONG {$bits[1]}"); //Ping? Pong!
			}

			$from = getNick($bits[0]);
			$chan = trim($bits[2]);

			if($chan[0] != '#') {
				$chan = $from;
			}

			if(isset($bits[3])) {
				$cmd = trim($bits[3]);
				switch($cmd) {
					case ':!exit':
						$this->shutdown($bits[4], $from, $chan);
					break;

					case ':!restart':
						$this->restart($bits[4], $from, $chan);
					break;
				}
			}

			if($bits[1] == 'PRIVMSG') {

				$msg = substr($bits[3], 1);
				for($i=4; $i<count($bits); $i++) {
					$msg .= ' '.$bits[$i];
				}
				$msg = trim($msg);
				foreach($this->plugins as $plugin) {
					$plugin->onMessage($from, $chan, $msg);	
				}
			}

			unset($bits);
			unset($from);
			unset($msg);
			unset($bits);
		}
		unset($data);
		
		//Move along
		$this->main();
	}

	function joinChannel($channel) {
		echo "Joining channel {$channel}\n";
		if(is_array($channel)) {
			foreach($channel as $chan) {
				sendData($this->socket, "JOIN {$chan}");
			}
		} else {
			sendData($this->socket, "JOIN {$channel}");
		}
	}
	

	function restart($pass, $from, $chan) {
		if(!$this->correctAdminPass($pass)) {
                        sendMessage($this->socket, $chan, "{$from}: Wrong password");
                        return false;
                }
		sendMessage($this->socket, $chan, "{$from}: Restarting...");
		$this->prepareShutdown();
		die(exec('sh start.sh > /dev/null &'));
	}
	
	function prepareShutdown() {
                sendData($this->socket, "QUIT :VikingBot - https://github.com/Ueland/VikingBot");
                foreach($this->plugins as $plugin) {
                        $plugin->destroy();
                }
	}

	function shutdown($pass, $from, $chan) {

		if(!$this->correctAdminPass($pass)) {
			sendMessage($this->socket, $chan, "{$from}: Wrong password");
			return false;
		}
		sendMessage($this->socket, $chan, "{$from}: Shutting down...");
		$this->prepareShutdown();
		exit;
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

	function signalHandler($signal) {
		sendData($this->socket, "QUIT :Caught signal {$signal}, shutting down");
		echo "Caught {$signal}, shutting down\n";
		exit();
	}
}

//Start the bot
$bot = new VikingBot($config);
