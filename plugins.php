<?php

//RSS Reader
$config['plugins']['rssReader'] = array(
        array('title'=> 'cryptoGlance', 'url'=>'http://www.reddit.com/r/cryptoglance/.rss',     'pollInterval' => 15,     'channel'=> $config['channel'][0]),
);

//File reader -- not used
//$plugins['fileReader'] = array(
//        'channel'       => '#cryptoglance',
//);

//Auto Op -- not used
//$plugins['autoOp'] = array(
//        'mode'    =>  '0',              // autop mode, 0 = disabled, 1 = only configured users, 2 = autoop everyone
//        'channel' =>  array(
//               '#channel1'    => array('nick1','nick2','nick3','nick4','nick5','nick6','nick7','nick8'),
//               '#channel2'    => array('ueland','ernini')
//        ),
//);
