<?php

/**
 * Plugin that outputs any content from the file db/fileReaderOutput.db
 * to the channel specified. When the content is read the file is truncated.
 * 
 * If you have SVn commit hooks etc you want to get content from they should
 * pipe their data into this file.
 */
class fileReaderPlugin implements pluginInterface {

	var $socket;
	var $channel = '';
	var $db = 'db/fileReaderOutput.db';
	var $lastCheck;

        function init($config, $socket) {
		$this->channel = $config['plugins']['fileReader']['channel'];
		$this->socket = $socket;
		$this->lastCheck = time();
		if(!is_file($this->db)) {
			touch($this->db);
		}
	}

        function tick() {
		if($this->lastCheck < time()) {
			clearstatcache();
			if(filemtime($this->db) >= $this->lastCheck) {
				$data = file($this->db);
				foreach($data as $row) {
					sendMessage($this->socket, $this->channel, $row);
				}
				$h = fopen($this->db, 'w+');
				fclose($h);
				unset($data);
			}
			$this->lastCheck = time();
		}	
	}

        function onMessage($from, $channel, $msg) {
	}

        function destroy() {
		$this->socket = null;
	}
}
