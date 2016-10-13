<?php

namespace CWB\Core;

/**
* class to autoloader classes
**/
class Autoloader {
	/**
	* All names of class
	* @var array
	**/
	private static $pathsController = null;
	
	/**
	* path to Application folder
	* @var string
	**/
	private static $beforeDir = '';
	
	/**
	* mapper the Controller classes into app directory
	* this build the array with the paths of
	* all classes in the application
	* @param string $dir > [optional]path to the directory
	**/
	private static function setPathsController($dir = null){
		if(self::$pathsController == null){
			self::$pathsController = array();
		}
		
		if(empty($dir)){
			$dir = realpath(CWB_DIR);
		}
		
		if(empty(self::$beforeDir)){
			self::$beforeDir = CWB_DIR;
		}
		
		$files = scandir($dir);
		
		foreach($files as $file){
			if($file == '.' || $file == '..') continue;
			
			if(is_file($dir . DS . $file)){
				self::$pathsController[] = str_replace(self::$beforeDir, '', $dir . DS . $file);
			}elseif(is_dir($dir . DS . $file)){
				self::setPathsController($dir . DS . $file);
			}
		}
	}
	
	/**
	* autoload class
	* @param string $className > name of class
	**/
	public static function autoload($className){
		$className = str_replace('CWB', '', $className);
		$className = trim($className, '\\');
		$className = str_replace('\\', DS, $className);
		
		$file = $className;
		foreach(self::$pathsController as $value){
			if(preg_match('#' . preg_quote($className) . '#i', $value)){
				$file = $value;
			}
		}
		
		$appDir = CWB_DIR;
		if(is_file($appDir . $file)){
			include_once $appDir . $file;
		}
	}
	
	/**
	* register the autoload function
	**/
	public static function register(){
		if(self::$pathsController == null){
			self::setPathsController();
		}
		
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}
	
	/**
	* unregister the autoload function
	**/
	public static function unregister(){
		spl_autoload_unregister(array(__CLASS__, 'autoload'));
	}
}

// register the autoload
Autoloader::register();