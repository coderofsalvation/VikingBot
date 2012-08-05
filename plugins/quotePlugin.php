<?php

/**
 * Rss reader plugin, pulls specified RSS feeds
 * at specified intervalls and outputs changes
 * to the specified channel.
**/
class quotePlugin implements pluginInterface {

	var $lastCleanTime;
	var $socket;
	var $started;
	var $todo;
	var $config;
	var $lastMsgSent;
  var $lastMsgWasMe;

  function init($config, $socket) {
		$this->config = $config;
		$this->todo = array();
		$this->rssConfig = $config['plugins']['rssReader'];
		$this->started = time();
		$this->socket = $socket;
    $this->lastMsgWasMe = false;
	}

    function onData($data) {
    }

    function tick() {
      //Start pollings feeds that should be updated after 20 seconds to get the bot in to any channels etc
      if( !$this->lastMsgWasMe && ($this->started + 30) < time()) {
        if( ($this->lastCleanTime + ($this->config['plugins']['quote']['interval'] * 60) ) < time()){
          $this->onMessage("", $this->config['plugins']['quote']['channel'], "!quote");
          $this->lastCleanTime = time();
          $this->lastMsgWasMe  = true;
        }
      }
    }

    function onMessage($from, $channel, $msg) {
      if(stringEndsWith($msg, "{$this->config['trigger']}quote")) {
        $quotes = file_get_contents( dirname(__FILE__)."/../util/quotes.txt" );
        $quotes = explode("---", $quotes);
        $index = rand(0, count($quotes) );
        $quote = $quotes[$index];
        $quote = str_replace( array("\n","\r"), "", $quote);
        sendMessage($this->socket, $channel, "[quote] ".$quote );
      }else $this->lastMsgWasMe = false;
    }

    function destroy() {

    }

}
