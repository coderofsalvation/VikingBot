<?php

/**
 * Plugin that responds with bot memory usage information
 */
class translatePlugin implements pluginInterface {

	var $socket;
	var $config;
  var $usage = "usage: !translate en|hu someword";

  function init($config, $socket) {
		$this->config = $config;
		$this->socket = $socket;
	}

  function tick() {

	}

  function onData($data) {
  }

  function curl($url,$params = array(),$is_coockie_set = false)
  {
  
    if(!$is_coockie_set){
    /* STEP 1. let’s create a cookie file */
    $ckfile = tempnam ("/tmp", "CURLCOOKIE");
  
    /* STEP 2. visit the homepage to set the cookie properly */
    $ch = curl_init ($url);
    curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec ($ch);
    }
  
    $str = ''; $str_arr= array();
    foreach($params as $key => $value)
    {
      $str_arr[] = urlencode($key)."=".urlencode($value);
    }
    if(!empty($str_arr))
    $str = '?'.implode('&',$str_arr);
  
    /* STEP 3. visit cookiepage.php */
  
    $Url = $url.$str;
  
    $ch = curl_init ($Url);
    curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
  
    $output = curl_exec ($ch);
    return $output;
  }

  function Translate($word,$from,$to)
  {
    if( strlen($from) != 2 || strlen($to) != 2 ) return array( $this->usage );
    $word = urlencode($word);
    $url = "http://translate.google.com/translate_a/t?client=t&text='.$word.'&hl=en&sl={$from}&tl={$to}&multires=1&otf=2&pc=1&ssel=0&tsel=0&sc=1";
    $result = $this->curl($url);
    // poor coding starts now :)
    $result = str_replace( array("[","]",'"','\\','.',"'"), "", $result);
    $result = explode(',',$result);
    $found  = array();
    foreach( $result as $k => $v ){
      $v = trim($v);
      if( strstr($word, $v ) || $v == trim($word) || $v == $from || $v == $to || $v == 'noun' || isset($found[$v]) || is_numeric($v) || !strlen($v) || $v == '""' ) unset($result[$k]);
      else{
        $found[$v] = true;
        $result[$k] = str_replace( '"','', trim($v) );
      }
    }
    return $result;
  }

  function onMessage($from, $channel, $msg) {
    $token = "translate";
		if(stringEndsWith($msg, "{$this->config['trigger']}{$token}"))
      return sendMessage($this->socket, $channel, $this->usage);
		if( strstr( $msg, "{$this->config['trigger']}{$token} ")) {
      try{
        $query = substr( $msg, strpos( $msg, $token)+strlen($token)+1 );
        $languages = explode("|", substr( $query, 0, 5 ) );
        $word = substr($query, 6 );
        $result = $this->Translate( $word, $languages[0], $languages[1] );
        if( count($result) > $this->config['plugins']['translate'] ) 
          $result = array_slice( $result, 0, $this->config['plugins']['translate'] );
        sendMessage($this->socket, $channel, "[translation] ".utf8_encode( implode(", ", $result) ) );
      }catch (Exception $e){ sendMessage($this->socket, $channel, $this->usage); }
    }
	}

  function destroy() {
		$this->socket = null;
		$this->config = null;
	}
}
