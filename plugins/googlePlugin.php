<?php

/**
 * Plugin that responds with bot memory usage information
 */
class googlePlugin implements pluginInterface {

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
		if(strstr($msg, "{$this->config['trigger']}google")) {
      $token = "google";
      $query = substr( $msg, strpos( $msg, $token)+strlen($token)+1 );

      $g = json_decode(file_get_contents('http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q='.urlencode($query)), true);
      if (!isset($g['responseData']) || !isset($g['responseData']['results']) || !is_array($g['responseData']['results'])){
        sendMessage($this->socket, $channel, $from.": Google doesnt like that");
        return ;
      }
      $text = array();
      foreach ($g['responseData']['results'] as $res) $text[] = $res['url'];
      $text = implode(' , ', $text);
      $url = 'http://google.com/search?q='.urlencode($query);
      sendMessage($this->socket, $channel, "{$url} gave: {$text}");
		}
	}

  function destroy() {
		$this->socket = null;
		$this->config = null;
	}
}
