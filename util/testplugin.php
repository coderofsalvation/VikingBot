<?
/*
 * small offline testing environment for plugin development
 */
include_once(dirname(__FILE__)."/../lib/pluginInterface.php");
include_once(dirname(__FILE__)."/../lib/functions.php");
$args =$_SERVER['argv'];

function checkArgs(){
  global $args;
  if( count($args) < 2 )  die("Usage: testplugin <phpfile> [command]\nExample: php testplugin.php ../plugins/helpPlugin.php !help");
  if( !is_file($args[1]) ) die("Cannot find file {$args[1]}");
  return true;
}

function main(){
  if( checkArgs() ) test();
}

function printx($msg){
  print("[x] {$msg}\n");
}

function test(){
  global $args;
  $cmd = count($args) == 3 ? $args[2] : "!foo";
  $classname = str_replace( array(".php"), "", basename($args[1]) );
  printx("trying to create class {$classname}");
  include( $args[1] );
  $plugin = new $classname();
  printx("bot startup: calling init()");
  $plugin->init(null,null);
  printx("bot heartbeat : calling tick() 4 times");
  $plugin->tick();
  printx("bot event: user 'matt' says '{$cmd}' in channel '#foo'..");
  $plugin->onMessage( "matt", "#foo", "{$cmd}" );
  printx("bot event: user 'matt' says '{$cmd} arg1' in channel '#foo'..");
  $plugin->onMessage( "matt", "#foo", "{$cmd} arg1" );
  printx("bot event: user 'matt' says '{$cmd} looooong input!' in channel '#foo'..");
  $plugin->onMessage( "matt", "#foo", "{$cmd} looooong input!" );
  printx("bot destroy: calling destroy()");
  $plugin->destroy();
}

main();

?>
