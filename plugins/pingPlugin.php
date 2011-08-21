<?php

/**
 * Plugin that responds to "!ping" messages with a pong
 * simply to verify that the bot is alive
 */
class pingPlugin implements pluginInterface {

	var $socket;

        function init($config, $socket) {
		$this->socket = $socket;
	}

        function tick() {

	}

        function onMessage($from, $channel, $msg) {
		if(stringEndsWith($msg, '!ping')) {
			sendMessage($this->socket, $channel, $from.": Pong");
		}
		unset($msg);
	}

        function destroy() {
		$this->socket = null;
	}
}
