<?php

/**
 * Checks to see if a string ends with substring
 */
function stringEndsWith($whole, $end) {
    return @(strpos($whole, $end, strlen($whole) - strlen($end)) !== false);
}

/**
 * Checks to see if a string starts with substring
 */
function stringStartsWith($whole, $end) {
	if(substr($whole, 0, strlen($end)) == $end) {
		return true;
	}
	return false;
}

/**
 * gets the nick name from a ident
 */
function getNick($in) {
	$in = str_replace(":", '', $in);
	$bits = explode("!", $in);
	return $bits[0];
}


/**
 * Write a message to channel/user
 */
function sendMessage($socket, $channel, $msg) {
	if(strlen($msg) > 2) { //Avoid sending empty lines to server, since all data should contain a line break, 2 chars is minimum
		sendData($socket, "PRIVMSG {$channel} :{$msg}");
	}
}

/**
 * Sends data to server
 */
function sendData($socket, $msg) {
	fwrite($socket, "{$msg}\r\n");
	echo "<Bot to server> {$msg}\n";
}

/**
 * Handle serious errors
 *
 */
function errorHandler($errno, $errstr, $errfile, $errline) {

	switch ($errno) {
		case E_USER_WARNING:
			//Serious error, like server disconnection. Take a little break before restarting	
			echo "Error detected, restarting the bot.\n";
			sleep(5);
			die(exec('sh start.sh > /dev/null &'));		
		break;
	}
	return false;
}
