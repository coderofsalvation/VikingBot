<?php

/**
 * Rss reader plugin, pulls specified RSS feeds
 * at specified intervalls and outputs changes
 * to the specified channel.
**/
class rssPlugin implements pluginInterface {

	var $lastCleanTime;
	var $socket;
	var $started;
	var $todo;
	var $config;

        function init($config, $socket) {
		$this->config = $config;
		$this->todo = array();
		$this->rssConfig = $config['plugins']['rssReader'];
		$this->started = time();
		$this->socket = $socket;
		$this->controlFeedDB();
		$this->cleanFeedDB();
	}

        function tick() {

		//Clean up the RSS database each hour
		if(($this->lastCleanTime + 3600) < time()) {
			logMsg("rssPlugin: Cleaning RSS DB");
			$this->cleanFeedDB();
			$this->lastCleanTime = time();
		}

		//Start pollings feeds that should be updated after 20 seconds to get the bot in to any channels etc
		if(($this->started + 30) < time()) {
			$this->parseFeeds();
		}

		//If we got todo, output one row from it
		if(count($this->todo) > 0) {
			$row = array_pop($this->todo);
	                sendMessage($this->socket, $row[0], $row[1]);
		}

        }

        function onMessage($from, $channel, $msg) {

        }

        function destroy() {

        }

	/**
	 * Makes sure that the RSS database is sane
	 */
	function controlFeedDB() {
		if(is_file("db/rssPlugin.db") == false) {
			$h = fopen("db/rssPlugin.db", 'w+') or die("db folder is not writable!");
			fclose($h);	
		}
	}

	/**
	 l Parses RSS feeds for new content
	 */
	function parseFeeds() {
		foreach($this->rssConfig as $feed) {
			if(!isset($this->lastCheck[$feed['url']]) || ($this->lastCheck[$feed['url']] + ($feed['pollInterval'] *60) < time())) {
				$this->lastCheck[$feed['url']] = time();
				logMsg("rssPlugin: Checking RSS: {$feed['url']}");
				try {
					$content = file_get_contents($feed['url']);
					$x = new SimpleXmlElement($content);
				
					//RSS feed format
					if(isset($x->channel)) {
						foreach($x->channel->item as $entry) { 
							$this->saveEntry($feed['title'], $feed['channel'], $entry->title, $entry->link);
						}
					} else {
						//Atom feed format
						if(isset($x->entry)) {
							foreach($x->entry as $entry) {
								$this->saveEntry($feed['title'], $feed['channel'], $entry->title, $entry->link->attributes()->href);
							}
						}
					}
					$content = null;	
					$x = null;
				}catch(Exception $e) {
					logMsg($e->getMessage());
				}
			}
		}
	}

	/**
	 * Saves (if needed) RSS entries
	 */
	function saveEntry($feedTitle, $feedChannel, $elementTitle, $elementLink) {

		//nl2br wont kill all linebreaks, "magic.."
		$elementTitle = preg_replace('/[\r\n]+/', '', $elementTitle);

		$hash = md5($elementTitle.$elementLink);
		$data = file("db/rssPlugin.db");
		foreach($data as $row) {
			$bits = explode("\t", $row);
			if($hash == @md5($bits[2].$bits[3])) {
				return false; //Already saved
			}
		}
		$data = null;
		$newRow = $feedTitle."\t{$feedChannel}\t{$elementTitle}\t{$elementLink}\t{$hash}\n";
		$h = fopen("db/rssPlugin.db", 'a');
		fwrite($h, $newRow);
		fclose($h);
		$this->todo[]= array($feedChannel, "[{$feedTitle}] {$elementTitle} - {$elementLink}");
		$newRow = null;
	}

	/**
	 * Removes old content from the RSS DB
	 */
	function cleanFeedDB() {
		$data = file("db/rssPlugin.db");
		$data = array_reverse($data);
		if(count($data) > 1000) {
			$newData = array();
			$counter = 0;
			foreach($data as $d) {
				$newData[] = $d;
				if($counter == 1000) {
					break;
				}
			}
			$h = fopen("db/rssPlugin.db", 'w+') or die("db folder is not writable!");
			foreach($newData as $d) {
				fwrite($h, $d."\n");
			}
			fclose($h);
			$newData = null;
		}
		$data = null;
	}
}
