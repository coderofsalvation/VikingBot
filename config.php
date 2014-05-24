<?php

// Global Config
$config = array(
	'server'	=>	'chat.freenode.net',		//Server name, prefix it with "ssl://" in order to use SSL server
	'port'		=>	6667,			//Server port
	'channel'	=>	array('#cryptoGlance'),		//Channel to join, use array('channel1', 'channel2') for multiple channels
	'name'		=>	'cryptoGlance',		//Name of the bot
	'nick'		=>	'cryptoGlance',		//Nick of the bot
	'pass'		=>	'',			//Server password
	'waitTime'	=>	10,			//How many seconds to wait before joining channel after connecting to server
	'adminPass'	=>	'',		//Bot admin password, used for commands like !exit (!exit vikingbot)
	'memoryLimit'	=>	'128',			//Max memory the bot can use, in MB
    'memoryRestart' =>  '10',                   //Min memory usage, in MB. (The bot will try to clear RAM or restart if reached)
	'trigger'	=>	'!',			//What character should be used as bot command prefixes
	'maxPerTenMin'	=>	50			//Max messgages a user can send per 10 minutes before beeing ignored for that time
);