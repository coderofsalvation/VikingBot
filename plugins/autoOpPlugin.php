<?php

class autoOpPlugin implements pluginInterface {

	var $config;
	var $socket;

        function init($config, $socket) {
    		$this->config = $config;
    		$this->socket = $socket;
            $this->autoOpConfig = $config['plugins']['autoOp'];
        }

        function tick() {

        }

        function onMessage($from, $channel, $msg) {

        }

        function onData($data) {

            if ($this->autoOpConfig['mode']) {

                if (strpos($data,'JOIN :') !== false) {
                    $bits = explode(" ", $data);
                    $nick = getNick(@$bits[0]);
                    $channel = trim(str_replace(":", '', @$bits[2]));

                    if ($this->autoOpConfig['mode'] == 1) {
                        if (in_array($nick, $this->autoOpConfig['channel'][$channel])) {
                            sendData($this->socket, "MODE {$channel} +o {$nick}");
                        }
                    } elseif ($this->autoOpConfig['mode'] == 2) {
                        sendData($this->socket, "MODE {$channel} +o {$nick}");
                    }

                }
            }
        }

        function destroy() {
                $this->socket = null;
        }

}
