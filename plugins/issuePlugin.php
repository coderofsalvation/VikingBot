<?php

/**
 * Plugin that can send tickets to the roundup tracker (great software)
 */
class issuePlugin implements pluginInterface {

	var $socket;
	var $config;

  function init($config, $socket) {
		$this->config = $config;
		$this->socket = $socket;
	}

  function tick() {

	}

  function onData($data) {
  }

  /* TODO: replace hardcoded strings to configfile */
  function onMessage($from, $channel, $msg) {

		if(strstr($msg, "{$this->config['trigger']}issue ") && !stringEndsWith($msg, "{$this->config['trigger']}issue") ) {
      $token = "issue";
      $query = substr( $msg, strpos( $msg, $token)+strlen($token)+1 );
      $to        = "issue@foo.com";
      $headers   = array();
      $headers[] = "MIME-Version: 1.0";
      $headers[] = "Content-type: text/plain; charset=iso-8859-1";
      $headers[] = "From: Vikingbot <vikingbot@foo.com>";
      //$headers[] = "Bcc: JJ Chong <bcc@domain2.com>";
      $headers[] = "Reply-To: Foo <foo@foo.com>";
      $headers[] = "Subject: {$query}";
      $headers[] = "X-Mailer: PHP/".phpversion();
      $subject = $query;
      $email   = "this issue (see title) was indirectly pushed into the system by {$from} using vikingbot";
      $ok = mail($to, $subject, $email, implode("\r\n", $headers));
      file_put_contents("/tmp/flop", $to."|".$subject."|".implode("\r\n",$headers) );
      sendMessage($this->socket, $channel, $ok ? "issue saved" : "issue saving failed :(" );
		}
	}

  function destroy() {
		$this->socket = null;
		$this->config = null;
	}
}
