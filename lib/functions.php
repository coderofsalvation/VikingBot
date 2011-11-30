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
	$res = fwrite($socket, "{$msg}\r\n") or trigger_error("Broken pipe on write, restarting the bot.");
	if($res) {
		logMsg("<Bot to server> {$msg}");
	}
}

/**
 * Handle serious errors
 */
function errorHandler($errno, $errstr, $errfile, $errline) {

	switch ($errno) {

		//Serious error, like server disconnection. Take a little break before restarting
		case E_USER_WARNING:
			logMsg("Error detected, restarting the bot.");
			sleep(10);
			doRestart();
		break;

		//PHP Warnings, like SSL errors
		case E_WARNING:
			if(strstr($errstr, "OpenSSL Error messages") !== false) {
				logMsg("SSL error detected, restarting the bot. ({$errstr})");
				sleep(10);
				doRestart();
			}		
		break;

                //PHP Notice, ignore it
                case E_NOTICE:
                break;

		//Default error handling, just log it
                default:
                        logMsg("errorHandler: unhandled PHP error {$errno}, {$errstr} from {$errfile}:{$errline}");
                break;
	}
	return false;
}

/**
 * Log data (to console which is piped to log file, for now)
 */
function logMsg($msg) {
	if(!stringEndsWith($msg, "\n")) {
		$msg .= "\n";
	}		
	echo "[".date("t.M.y H:i:s")."] {$msg}";
}

/**
 * Perform restart of the bot
 */
function doRestart() {
	die(exec('sh start.sh > /dev/null &'));
}
