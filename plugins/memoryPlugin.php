<?php

/**
 * Plugin that responds with bot memory usage information
 */
class memoryPlugin implements pluginInterface {

	var $socket;
	var $config;

        function init($config, $socket) {
		$this->config = $config;
		$this->socket = $socket;
	}

        function tick() {

	}

        function onMessage($from, $channel, $msg) {
		if(stringEndsWith($msg, '!memory')) {
			$usedMem = round(((memory_get_usage() / 1024) / 1024),2);
			$freeMem = round(($this->config['memoryLimit'] - $usedMem),2);
			sendMessage($this->socket, $channel, $from.": Memory status: {$usedMem} MB used, {$freeMem} MB free.");
			$usedMem = null;
			$freeMem = null;
		}
	}

        function destroy() {
		$this->socket = null;
		$this->config = null;
	}
}
