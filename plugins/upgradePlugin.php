<?php

/**
 * Plugin that performs upgrade of the bot, then
 * restarts it so it can start up with the new version
 * This plugin requires access to "git pull" so all files
 * should be owned by the user that is running the bot
 * 
 * You can start an upgrade via the command !upgrade [admin password]
 */
class upgradePlugin implements pluginInterface {

	var $config;
	var $socket;

        function init($config, $socket) {
		$this->config = $config;
		$this->socket = $socket;
        }

        function tick() {

        }

        function onMessage($from, $channel, $msg) {
                if(stringStartsWith($msg, '!upgrade')) {
			$bits = explode(" ", $msg);
			$pass = $bits[1];
			if(strlen($this->config['adminPass']) > 0 && $pass != $this->config['adminPass']) {
				sendMessage($this->socket, $channel, "{$from}: Wrong password");
			} else {
				sendMessage($this->socket, $channel, "{$from}: Starting upgrade...");
				$response = trim( shell_exec("git pull") );
				if($response == 'Already up-to-date.') {
					sendMessage($this->socket, $channel, "{$from}: The bot is already up to date, not restarting.");
				} else {
					sendMessage($this->socket, $channel, "{$from}: {$response}");
					sendMessage($this->socket, $channel, "{$from}: Restarting...");
					sendData($this->socket, 'QUIT :Restarting due to upgrade');
					die(exec('sh start.sh > /dev/null &'));	
				}
			}
                }
        }

        function destroy() {

        }
}
