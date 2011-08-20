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
	echo "<Bot to server> PRIVMSG {$channel} :{$msg}\n";
	sendData($socket, "PRIVMSG {$channel} :{$msg}");
}

/**
 * Sends data to server
 */
function sendData($socket, $msg) {
	fwrite($socket, "{$msg}\r\n");
	echo "<Bot to server> {$msg}\n";
}
