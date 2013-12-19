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
	'memoryLimit'	=>	'128',			//Max memory the bot can use, in MB
        'memoryRestart' =>      '10',                   //Min memory usage, in MB. (The bot will try to clear RAM or restart if reached)
	'trigger'	=>	'!',			//What character should be used as bot command prefixes
	'maxPerTenMin'	=>	50			//Max messgages a user can send per 10 minutes before beeing ignored for that time
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

//File reader
$config['plugins']['fileReader'] = array(
        'channel'       => '#vikingbot',
);
<<<<<<< HEAD
=======

//Auto Op
$config['plugins']['autoOp'] = array(
        'mode'    =>  '1',              // autop mode, 0 = disabled, 1 = only configured users, 2 = autoop everyone
        'channel' =>  array(
               '#channel1'    => array('nick1','nick2','nick3','nick4','nick5','nick6','nick7','nick8'),
               '#channel2'    => array('ueland','ernini')
        ),
);
>>>>>>> proj1/master
