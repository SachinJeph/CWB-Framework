<?php

namespace CWB\Core;

use CWB\Config\App;
use CWB\Lib\Register;

/**
* Where init the framework
* with the static method Run()
*
* @package
**/
final class Start{
	/** Check for Magic Quotes and remove them **/
	final private static function stripSlashesDeep($value){
		$value = is_array($value) ? array_map(self::stripSlashesDeep, $value) : stripslashes($value);
		return $value;
	}
	
	/** Check for Magic Quotes and remove them **/
	final private static function removeMagicQuotes(){
		if(get_magic_quotes_gpc()){
			if(isset($_GET)){
				$_GET = self::stripcSlashesDeep($_GET);
			}
			
			if(isset($_POST)){
				$_POST = self::stripcSlashesDeep($_POST);
			}
			
			if(isset($_COOKIE)){
				$_COOKIE = self::stripcSlashesDeep($_COOKIE);
			}
		}
	}

	/** Check register globals and remove them **/
	public static function unregisterGlobals(){
		if(ini_get('register_globals')){
			$array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
			foreach($array as $var){
				foreach($GLOBALS[$var] as $key => $value){
					if($value === $GLOBALS[$key]){
						unset($GLOBALS[$key]);
					}
				}
			}
		}
	}
	
	/** initiate a custom safe session **/
	public static function initiateSession(){
		// Set a custom session name
		$session_name = 'sec_session_id';
		$secure = App::SITE_SECURE;
		
		// This stops JavaScript being able to access the session id
		$httpOnly = true;
		
		// Forces sessions to only use cookies
		if(ini_set('session.use_only_cookies', 1) === FALSE){
			// Error : Could not initiate a safe session
			header("Location: /error.php?errorCode=1000");
			exit();
		}
		
		// Get current cookies params
		$cookieParams = session_get_cookie_params();
		session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], App::SITE_SECURE, $httpOnly);
		
		// Set the session name to the one set above
		session_name($session_name);
		
		// Start the PHP Session
		session_start();
		
		// Regenerated the session, delete the old one
		session_regenerate_id();
	}
	
	/**
	* Set the configs defaults for the framework
	* the configs in the \Config\App
	**/
	final private static function setDefaults(){
		// if change headers
		if(App::CHANGE_HEADERS){
			foreach(App::$headers as $header){
				header($header);
			}
		}
		
		// set custom enviroment variable to the server
		// replace configs with ini_set();
		if(App::CHANGE_INI_SET){
			/** Set/Check if environment is development and display errors  **/
			foreach(App::iniConfig() as $var => $value){
				if($value !== ''){
					ini_set((string)$var, (string)$value);
				}
			}
		}
		
		foreach(App::$autoload as $key => $value){
			Register::set(new $value, $key);
		}
		
		// remove the magic quotes
		self::removeMagicQuotes();
		
		// unregister globals
		self::unregisterGlobals();
		
		// initiate session
		self::initiateSession();
		
	}
	
	/**
	* Init the app
	* load the routes and config App, DB, etc into framework.
	* Seart for Controller\Method called on URL,
	* case hasn't called the Controller\Method the defaults
	**/
	final public static function Main(){
		// Default Configs
		self::setDefaults();
		
		$_SERVER['PATH_INFO'] = isset($_GET['_route']) ? $_GET['_route'] : '';
		
		// Load the Router
		$r = new Router();
		call_user_func_array(array(new $r->controller, (string)$r->method), (array)$r->args);
	}
}
