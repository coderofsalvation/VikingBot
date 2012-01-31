<?php

require("config.php");
require("lib/functions.php");
require("lib/pluginInterface.php");

set_time_limit(0);
error_reporting(E_ALL);
date_default_timezone_set('GMT');
declare(ticks = 1);

if(!is_file("config.php")) {
	die("You have not created a config.php yet.\n");
}

set_error_handler("errorHandler");

class VikingBot {

	var $socket;
	var $inChannel = false;
	var $startTime;
	var $plugins;
	var $config;
	var $lastMemCheckTime;
	var $floodDb; //Internal database over user activity to keep flodders away

	function __construct($config) {

		//Add signal handlers to shut down the bot correctly if its getting killed
		pcntl_signal(SIGTERM, array($this, "signalHandler"));
		pcntl_signal(SIGINT, array($this, "signalHandler"));

		$this->config = $config;
		$this->lastMemCheckTime = 0;
		$this->startTime = time();

		ini_set("memory_limit", $this->config['memoryLimit']."M");
		$this->socket = stream_socket_client("".$config['server'].":".$config['port']) or die("Connection error!");
		stream_set_blocking($this->socket, 0);
		stream_set_timeout($this->socket, 600);
		$this->login();
		$this->loadPlugins();
		$this->main($config);
	}

	function loadPlugins() {
		$this->plugins = array();
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
		if(strlen($this->config['pass']) > 0) {
			sendData($this->socket, "PASS {$this->config['pass']}");
		}
		sendData($this->socket, "NICK {$this->config['nick']}");
		sendData($this->socket, "USER {$this->config['name']} 0 *: {$this->config['name']}");
	}

	function main() {

		while(true) {
			//Sleep a bit, no need to hog all CPU resources
			usleep(100000);

			//Join channels if not already joined
			if( !$this->inChannel && (time() - $this->config['waitTime']) > $this->startTime ) {
        	                $this->joinChannel($this->config['channel']);
                	        sleep(2);
                		$this->inChannel = true;
			}
	
			//Run scheduled memory check
			if(time() - 600 > $this->lastMemCheckTime) {
				$this->lastMemCheckTime = time();
				$this->doMemCheck();	
			}
		
			//Tick plugins
			foreach($this->plugins as $plugin) {
				$plugin->tick();
			}

			//Load data from IRC server
			$data = fgets($this->socket, 256);
			if(strlen($data) > 0) {
				logMsg("<Server to bot> ".$data);
				$bits = explode(' ', $data);
				if($bits[0] == 'PING') {
					sendData($this->socket, "PONG {$bits[1]}"); //Ping? Pong!
				} else if($bits[0] == 'ERROR') {
					logMsg("Error from server, trying to reconnect in 2 minutes");
					$this->prepareShutdown("");
					sleep(120);
					doRestart();
				}
				$from = getNick($bits[0]);

				if($this->antiFlood($from)) {

					if(isset($bits[2])) {
						$chan = trim($bits[2]);
					}
			
					if(isset($chan[0]) && $chan[0] != '#') {
						$chan = $from;
					}
	
					if(isset($bits[3])) {
						$cmd = trim($bits[3]);
						switch($cmd) {
							case ":{$this->config['trigger']}exit":
								$this->shutdown($bits[4], $from, $chan);
							break;
		
							case ":{$this->config['trigger']}restart":
								$this->restart($bits[4], $from, $chan);
							break;
						}
						$cmd = null;
					}

					if($bits[1] == 'PRIVMSG' || $bits[1] == 'JOIN' || $bits[1] == 'PART') {
		
						$msg = @substr($bits[3], 1);
						for($i=4; $i<count($bits); $i++) {
								$msg .= ' '.$bits[$i];
						}
						$msg = trim($msg);
						foreach($this->plugins as $plugin) {
							$plugin->onMessage($from, $chan, $msg);	
						}
						$msg = null;
					}
				} else {
					logMsg("Ignoring {$from} due to flooding");
				}
				$bits = null;
				$from = null;
				$chan = null;
				$bits = null;
			}
			$data = null;
		}
	}

	function doMemCheck() {

		//Run garbage collection
		gc_collect_cycles();
		$memFree = ((($this->config['memoryLimit']*1024)*1024) - memory_get_usage());
		if($memFree < (($this->config['memoryRestart']*1024)*1024)) {
			$this->prepareShutdown("Out of memory, restarting...");
			doRestart();
		}
	}

	function joinChannel($channel) {
		logMsg("Joining channel {$channel}");
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
		$this->prepareShutdown("");
		doRestart();
	}
	
	function prepareShutdown($msg) {
		if(strlen($msg) == 0) {
			$msg = "VikingBot - https://github.com/Ueland/VikingBot";
		}
                sendData($this->socket, "QUIT :{$msg}");
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
		$this->prepareShutdown("");
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
		logMsg("Caught {$signal}, shutting down\n");
		exit();
	}

	function antiFlood($user) {
		$interval = substr(date('i'), 0, 1);
		if(isset($this->floodDb[$interval])) {
			$floodData = $this->floodDb[$interval];
		} else {
			$floodData = array();
		}
		if(isset($floodData[$user])) {
			$floodData[$user]++;
			if($floodData[$user] > $this->config['maxPerTenMin']) {
				return false;
			}
		} else {
			$floodData[$user] = 1;
		}
		$this->floodDb = array($interval=>$floodData);
		return true;
	}
}

//Start the bot
$bot = new VikingBot($config);
