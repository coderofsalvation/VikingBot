<?php

$config = array(
	'server'	=>	'servername',		//Server name, prefix it with "ssl://" in order to use SSL server
	'port'		=>	6666,			//Server port
	'channel'	=>	'#vikingbot',		//Channel to join, use array('channel1', 'channel2') for multiple channels
	'name'		=>	'vikingbot',		//Name of the bot
	'nick'		=>	'vikingbot',		//Nick of the bot
	'pass'		=>	'',			//Server password
	'waitTime'	=>	10,			//How many seconds to wait before joining channel after connecting to server
	'adminPass'	=>	'vikingbot',		//Bot admin password, used for commands like !exit (!exit vikingbot)
);

//=====================================
//Plugin specific configuration
//=====================================

//RSS Reader
$config['plugins']['rssReader'] = array(
        array('title'=> 'VG',           'url'=>'http://www.vg.no/rss/nyfront.php?frontId=1',    'pollInterval'=>15,     'channel'=>'#vikingbot'),
        array('title'=> 'BBC News',     'url'=>'http://feeds.bbci.co.uk/news/rss.xml',          'pollInterval'=>15,     'channel'=>'#vikingbot'),
        array('title'=> 'CNN',          'url'=>'http://rss.cnn.com/rss/edition.rss',            'pollInterval'=>15,     'channel'=>'#vikingbot'),
);
