<?php

/**
 * Plugin that responds to "!uptime" messages with information
 * about how long the bot has been running.
 */
class uptimePlugin implements pluginInterface {

	var $socket;
	var $startTime;
	var $config;

        function init($config, $socket) {
		$this->startTime = new DateTime();
		$this->socket = $socket;
		$this->config = $config;
	}

        function onData($data) {
        }

        function tick() {

	}

        function onMessage($from, $channel, $msg) {
		if(stringEndsWith($msg, "{$this->config['trigger']}uptime")) {
			sendMessage($this->socket, $channel, $from.": I have been running for ".$this->makeNiceTimeString($this->startTime->diff(new DateTime())));
		}
	}

        function destroy() {
		$this->socket = null;
	}

	function makeNiceTimeString($r) {
		if($r->m > 0) {
			return "{$r->m} months, {$r->d} days, {$r->h} hours, {$r->i} minutes & {$r->s} seconds";
		} else if($r->d > 0) {
			return "{$r->d} days, {$r->h} hours, {$r->i} minutes & {$r->s} seconds";
		} else if($r->h > 0) {
			return "{$r->h} hours, {$r->i} minutes & {$r->s} seconds";
		} else if($r->i > 0) {
			return "{$r->i} minutes & {$r->s} seconds";
		} else {
			return "{$r->s} seconds";
		}
	}
}
