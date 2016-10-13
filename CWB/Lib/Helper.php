<?php

namespace CWB\Lib;

use CWB\Config\App;

class Helper{
	
	
	/** Get server url path **/
	public static function getServerUrl(){
		$port = $_SERVER['SERVER_PORT'];
		$http = "http";
		
		if($port == "80"){
			$port = "";
		}
		
		if(!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on"){
			$http = "https";
		}
		
		if(empty($port)){
			return $http."://".$_SERVER['SERVER_NAME'];
		}else{
			return $http."://".$_SERVER['SERVER_NAME'].":".$port;
		}
	}
	
	
	
	public static function isLocalhost(){
		// if this is localhost
		return $_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1';
	}
	
	/** Get client IP address **/
	public static function getClientIP(){
		$ip = "127.0.0.1";
		
		if(getenv("HTTP_CLIENT_IP")){
			$ip = getenv("HTTP_CLIENT_IP");
		}else if(getenv("HTTP_X_FORWARDED_FOR")){
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		}else if(getenv("REMOTE_ADDR")){
			$ip = getenv("REMOTE_ADDR");
		}else{
			$ip = "UNKNOWN";
		}
		
		if($ip == "::1"){
			$ip = "127.0.0.1";
		}
		
		return $ip;
	}
	
	/** Check if the action is AJAX request **/
	public static function isAjax(){
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
	}
	
	/** Minify output html content **/
	public static function minify_content($content){
		if(!Helper::isLocalhost()){
			$path = self::UrlContent('~/library/min/lib/', '');
			$filename = $path . 'Minify/HTML.php';
			$html = $js = $css = false;
			
			if(file_exists($filename)){
				require_once $filename;
				$html = true;
			}
			
			$filename = $path . 'Minify/CSS.php';
			if(file_exists($filename)){
				require_once $filename;
				$css = true;
			}
			
			$filename = $path . 'JSMin.php';
			if(file_exists($filename)){
				require_once $filename;
				$js = true;
			}
			
			// if we pass above files
			if(($html == true) && ($js == true) && ($css == true)){
				$content = Minify_HTML::minify($content, array(
					'cssMinifier' => array('Minify_CSS', 'minify'),
					'jsMinifier' => array('JSMin', 'minify')
				));
			}
		}
		
		return $content;
	}
	
	/** Minify output js content **/
	public static function minify_content_js($content){
		$filename = self::UrlContent('~/library/min/lib/JSMin.php', '');
		
		if(!Helper::isLocalhost() && file_exists($filename)){
			require_once $filename;
			$content = JSMin::minify($content);
		}
		return $content;
	}
	
	/** Minify output css content **/
	public static function minify_content_css($content){
		$filename = self::UrlContent('~/library/min/lib/Minify/CSS.php', '');
		if(!Helper::isLocalhost() && file_exists($filename)){
			require_once $filename;
			$content = Minify_CSS::minify($content);
		}
		
		return $content;
	}
	
	/********************************************
	* @param mixed $haystack the source content *
	* @param mixed $needle the string to search *
	********************************************/
	
	/*************** startsWith ***************/
	public static function startsWith($haystack, $needle){
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
	
	/*************** endsWith ***************/
	public static function endsWith($haystack, $needle){
		$length = strlen($needle);
		if($length == 0){
			return true;
		}
		
		$start = $length * -1; //negative
		return (substr($haystack, $start) === $needle);
	}
	
	/** trim start characher **/
	public static function trimStart($prefix, $string){
		if(substr($string, 0, strlen($prefix)) == $prefix){
			$string = substr($string, strlen($prefix), strlen($string));
		}
		return $string;
	}
	
	/** trim end characher **/
	public static function trimEnd($suffix, $string){
		if(substr($string, (strlen($suffix) * -1)) == $suffix){
			$string = substr($string, 0, strlen($string) - strlen($suffix));
		}
		return $string;
	}
	
	/** Resolves a virtual path into an absolute path **/
	public static function UrlContent($path, $sub='CWB/Application'){
		if(self::startsWith($path, '~')){
			$path = str_replace('/', DS, $path);
			$appPath = ROOT . DS . $sub . (self::startsWith($path, '~/') ? '' : DS);
			$result = str_replace('~', $appPath, $path);
			$result = str_replace(DS.DS, DS, $result);
			//die($result);
			return $result;
		}else{
			return $path;
		}
	}
}