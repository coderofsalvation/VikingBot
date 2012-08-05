<?php

/**
 * Plugin that responds with bot memory usage information
 */
class manualPlugin implements pluginInterface {

	var $socket;
	var $config;
  var $cmds;

  function init($config, $socket) {
		$this->config = $config;
		$this->socket = $socket;
    $this->cmds   = array();
    $data = file_get_contents( dirname(__FILE__)."/../util/manual.txt" );
    $lines = explode("\n", $data );
    foreach( $lines as $line ){
       $cmd = substr($line, 0, strpos($line," ") );
       $txt = substr($line, strlen($cmd)+1 );
       if( strlen($txt) ) $this->cmds[ $cmd ] = $txt;
    }
	}

  function tick() {

	}

  function onData($data) {
  }

  function onMessage($from, $channel, $msg) {
		if(stringEndsWith($msg, "{$this->config['trigger']}guide")) {
      sendMessage($this->socket, $channel, "available guide commands: ".implode( ", ", array_keys($this->cmds) ) );
		}else if( isset($this->cmds[ $msg ] ) ){
      sendMessage($this->socket, $channel, $this->cmds[ $msg ] );
    }
	}

  function destroy() {
		$this->socket = null;
		$this->config = null;
	}
}
