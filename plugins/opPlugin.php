<?php

class opPlugin implements pluginInterface {

	var $config;
	var $socket;

        function init($config, $socket) {
		$this->config = $config;
		$this->socket = $socket;
        }

        function tick() {
        }

        function onData($data) {
        }

        function onMessage($from, $channel, $msg) {
                if(stringStartsWith($msg, "{$this->config['trigger']}op")) {
                        $bits = explode(" ", $msg);
                        $pass = @$bits[3];
			$who = @$bits[1];
			$where = @$bits[2];

                        if(strlen($this->config['adminPass']) > 0 && $pass != $this->config['adminPass']) {
                                sendMessage($this->socket, $channel, "{$from}: Wrong password");
                        } else {
				if(strlen($who) == 0 || strlen($where) == 0) {
                                	sendMessage($this->socket, $channel, "{$from}: Syntax: {$this->config['trigger']}op nick #channel [adminPassword]");
				} else {
                                        sendData($this->socket, "MODE {$where} +o {$who}");
					sendMessage($this->socket, $channel, "{$from}: OP has been attempted given, note that i must be OP myself to do it.");
                                }
                        }
                }
        }

        function destroy() {

        }
}
