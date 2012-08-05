<?php

/**
 * Plugin that responds with bot memory usage information
 */
class phpPlugin implements pluginInterface {

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
    $token = "php";
		if(strstr($msg, "{$this->config['trigger']}{$token} ")) {
      $query = substr( $msg, strpos( $msg, $token)+strlen($token)+1 );
      ob_start();
      system( dirname(__FILE__)."/../util/phpsearch {$query}" );
      $text = ob_get_contents();
      ob_end_clean(); 
      if( strlen($text) )
      $lines = explode("\n", $text );
      foreach($lines as $line )
        sendMessage($this->socket, $channel, $line );
		}
	}

  function destroy() {
		$this->socket = null;
		$this->config = null;
	}
}
