<?php

namespace CWB\Lib;

/**
* Class Register
*
* @package CWB
**/
class Register {
	/**
	* $vars where registry the data
	* @var array
	**/
	private static $vars = array();
	
	/**
	* Record a variable in the register
	* @param mixed $data > some value to record
	* @param string $name > [optional case $data is a object] - name to call this $data in Register::get();
	* @throws \InvalidArgumentException
	**/
	public static function set(&$data, $name=null){
		if($name == null){
			if(is_object($data)){
				$name = get_class($data);
			}else{
				throw new \InvalidArgumentException('Expects at least 2 parameter for non object, null given.', E_USER_ERROR);
			}
		}
		
		static::$vars[$name] = $data;
	}
	
	/**
	* get the value of a var registerd
	* @param string $name > name of the record $var
	* @return mixed value of var registred or null on failure
	**/
	public static function &get($name){
		return isset(static::$vars[$name]) ? static::$var[$name] : null;
	}
	
	/**
	* delete the record by your name in registry
	* @param string $name > name of the $var
	**/
	public static function remove($name){
		if(isset(static::$vars[$name])) unset(static::$vars[$name]);
	}
	
	/**
	* get all names records in Register
	* @return array all names records
	**/
	public static function getNames(){
		return array_keys(static::$vars);
	}
}