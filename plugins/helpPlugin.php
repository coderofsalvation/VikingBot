<?php

/**
 * Plugin that responds with bot memory usage information
 */
class helpPlugin implements pluginInterface {

	var $socket;
	var $config;

  function init($config, $socket) {
		$this->config = $config;
		$this->socket = $socket;
	}

  function tick() {

	}

  function onData($data) {
  }

  function onMessage($from, $channel, $msg) {
		if(stringEndsWith($msg, "{$this->config['trigger']}help")) {
      $help = file_get_contents( dirname(__FILE__)."/../help.txt" );
      $lines = explode("\n", $help );
      foreach( $lines as $line )
        sendMessage($this->socket, $channel, $line );
		}
	}

  function destroy() {
		$this->socket = null;
		$this->config = null;
	}
}
