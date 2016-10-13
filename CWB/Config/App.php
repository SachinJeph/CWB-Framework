<?php

namespace CWB\Config;

/**
* Class App
* Get All the Information About the System
*
* @package CWB
**/
class App {
	/**
	* Base URL
	* @var string
	**/
	const BASE_URL = '';
	
	const SITE_SECURE = false;
	
	const DEVELOPER_MODE = false;
	
	/** Key to encrypt the password **/
	//const SECRET_KEY = 'bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3';
	const SECRET_KEY = 'bcb04b7e103a0cd8b';
	
	/** Cache Directory **/
	const CACHE_PATH = '/CWB/Cache';
	
	/**
	* Classes to autoload
	* use 'nameToCall' => '\CWB\Namespace\To\Class',
	* to call use the \CWB\Lib\Register class
	**/
	public static $autoload = array(
		//'View' => '\CWB\Lib\View',
	);
	
	const DB_NEED = true;
	const DB_HOST = 'localhost';
	const DB_USER = 'root';
	const DB_PASS = '';
	const DB_NAME = 'CWB';
	
	/**
	* type of request
	* you can use
	* 'PATH_INFO' -> Uses the PATH_INFO
	* 'REQUEST' -> - Uses the global REQUEST variable
	*			|--- use index: 'controller' to controller without \CWB\Application\Controller\
	*			|--- use index: 'method' to action
	*			|--- use index: 'args' to arguments of function. Can be a array or string separated by '/'
	* 'ORIG_PATH_INFO' -> Uses the ORGI_PATH_INFO
	*
	* @var string type of request
	**/
	const TYPE_REQUEST = 'PATH_INFO';
	
	/**
	* Change the headers
	* @var boolean
	**/
	const CHANGE_HEADERS = true;
	
	public static $headers = array();
	
	/**
	* Change the ini_set()
	**/
	const CHANGE_INI_SET = true;
	
	public static function iniConfig(){
		$ini_settings = array();
		
		$ini_settings['expose_php'] = 'Off';
		$ini_settings['memory_limit'] = '128M';
		$ini_settings['max_execution_time'] = 360;
		$ini_settings['error_reporting'] = E_ALL;
		$ini_settings['date.timezone'] = 'Asia/Calcutta';
		
		if(self::DEVELOPER_MODE){
			$ini_settings['error_log'] = '';
			$ini_settings['display_errors'] = 'On';
		}else{
			$ini_settings['display_errors'] = 'Off';
			$ini_settings['log_errors'] = 'On';
			$ini_settings['error_log'] = ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error-' . date('Y-m-d') . '.txt';
		}

		return $ini_settings;
	}
	
	public static function getAppDir(){
		return CWB_DIR . 'Application' . DS;
	}
}