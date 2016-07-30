<?php

namespace CWB\Core;

use CWB\Config\App;

/**
* Class Error
* Handle all error here
* Display the defalut pages
*
* @package CWB
**/
final class Error extends \Exception {
	/**
	* @param string $page > page of error (must be have your sufix type: .php || .html)
	* @param array $params > parameteres repassados for this page
	**/
	public static function getErrorPage($page, $params = array()){
		if(is_array($params) || is_object($params)){
			foreach($params as $key=>$value){
				${$key} = $value;
			}
		}
		
		include CWB_DIR . 'ErrorPage' . DS . $page;
		die;
	}
	
	/**
	* get the last message of error
	* @return array of errors
	**/
	public static function lastError(){
		return @error_get_last();
	}
}
