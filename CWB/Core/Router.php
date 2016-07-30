<?php

namespace CWB\Core;

use CWB\Config\App;

/**
* Class Router
* Route all the path here
*
* @package CWB
**/

final class Router {
	/**
	* request of the URL
	* @var string $pathURL
	**/
	public $pathURL;
	
	/**
	* Contains the redirects of the routes
	* @var array $routes
	**/
	protected $routes;
	
	/**
	* Controller type = namespace\class
	* @var string $controller
	**/
	public $controller = 'CWB\Application\Controller\\';
	
	/**
	* get the method
	* @var string
	**/
	public $method = 'index';
	
	/**
	* arguments for the function
	* @var array $args
	**/
	public $args = array();
	
	/**
	* Dir of the controllers class
	* @var String $ctrlDir dir of the controllers class
	**/
	private $ctrlDir;
	
	/**
	* init the router
	* load the directorys, parse routes, aliases, Controller\Method.
	**/
	public function __construct(){
		$this->ctrlDir = App::getAppDir() . 'Controller' . DS; // directory of controllers
		
		// if file routes exists get routes array
		if(file_exists(App::getAppDir() . 'Routes.php')){
			// Array with routes
			$this->routes = require App::getAppDir() . 'Routes.php';
		}else{
			// create a default array
			$this->routes = array('_root_' => '');
		}
		
		// get the current type of request
		switch(App::TYPE_REQUEST){
			case 'PATH_INFO':
				$uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
				$uri = explode('?', $uri);
				$uri = trim($uri[0], '/');
				break;
			case 'ORIG_PATH_INFO':
				$uri = isset($_SERVER['PATH_INFO']) ? str_replace('index.php', '', $_SERVER['PATH_INFO'], 1) : '';
				$uri = trim($uri, '/');
				$uri = explode('?', $uri);
				$uri = trim($uri[0], '/');
				break;
			case 'REQUEST';
				$uri = isset($_REQUEST['controller']) ? $_REQUEST['controller'] . '/' : '';
				$uri .= isset($_REQUEST['method']) ? $_REQUEST['method'] . '/' : '';
				if(isset($_REQUEST['args'])){
					$uri .= is_array($_REQUEST['args']) ? trim(implode('/', $_REQUEST['args']), '/') : $_REQUEST['args'];
				}
			default:
				$uri = '';
		}
		
		if(!empty($uri)){
			$this->pathURL = $uri;
		}else{
			$this->pathURL = $this->routes['_root_'];
		}
		
		$this->parseRoutes();
		$this->loadAlias();
		$this->setFunc();
	}
	
	/**
	* Remove characters specials and replace (-|.) or (_).
	*
	* @param string $string string to be formated
	* @return string the string formated
	**/
	private function validUrl2Func($string){
		$string = preg_replace("/[^a-zA-Z0-9\_\-\.\/]/", "", $string);
		return preg_replace("/[\-\.]/", "_", $string);
	}
	
	private function parseRoutes(){
		if($this->pathURL == $this->routes['_root_']) return;
		
		foreach($this->routes as $roter=>$loaded){
			// if route has not character for formation
			if(strpos($roter, '%') === false) continue;
			
			// segments do request
			$segm = explode('/', $this->pathURL);
			
			// verify the begin with
			foreach($segm as $k){
				if(stripos($roter, $k) !== false){
					array_shift($segm);
				}else{
					continue;
				}
			}
			
			// while '%' in routes is greater then $segm: add false in $segm
			while(count($segm) < substr_count($roter, '%')){
				$segm[] = false;
			}
			
			// remove route with pattern without formatation
			unset($this->routes[$roter]);
			
			$slice = '/';
			
			// if last element of the PATH_INFO is false || '0' remove '/0' of the end
			if(substr($this->pathURL, -1) != '0'){
				$slice = '/..0';
			}
			
			$roter = rtrim(@vsprintf($roter, $segm), $slice);
			
			// set the route formated
			$this->routes[$roter] = trim(@vsprintf($loaded, $segm), $slice);
		}
	}
	
	/**
	* load the alias for the controller/method
	**/
	private function loadAlias(){
		if($this->pathURL == $this->routes['_root_']) return;
		
		$alias = null;
		$path = '';
		$arrPI = explode('/', $this->pathURL);
		
		foreach($arrPI as $p){
			// add a path and remove the first element of the array
			$path .= array_shift($arrPI);
			
			// if path exist into route
			if(isset($this->routes[$path])){
				$alias = $this->routes[$path] . '/' . implode('/', $arrPI);
			}
			
			// for more one subPathRoute
			$path .= '/';
		}
		
		$this->pathURL = $alias != null ? $alias : $this->pathURL;
	}
	
	/**
	* set the method and controller
	**/
	private function setFunc(){
		// get the parts of URL
		$parts = explode('/', $this->pathURL);
		
		// here is where is joined the parts
		$part = '';
		foreach($parts as $p){
			if(empty($p)){
				break;
			}
			
			// valid for name of class and file
			$p = $this->validUrl2Func(array_shift($parts));
			$part .= $p;
			
			// if is a directory add more one path and continue
			if(is_dir($this->ctrlDir . $part)){
				$part .= '/';
				$this->controller .= $p . '\\';
				continue;
			}else if(class_exists($this->controller . $p)){
				// If is a file into a Controller get the namespace\class and stop this loop
				$this->controller .= $p;
				break;
			}else{
				// if not found file or directory call the '404 - Not Found'
				if(!isset($this->routes['_404']) || empty($this->routes['_404_'])){
					Error::getErrorPage('404.php');
				}else{
					$this->routes['_404_'] = explode('/', trim($this->routes['_404_'], '/'));
					
					// get the method
					$_404M = array_pop($this->routes['_404_']);
					
					// set the controller and method
					$this->controller = App::getAppDir() . implode('\\', $this->routes['_404_']);
					$this->method = $_404M;
					
					if(is_callable(array((string)$this->controller, (string)$this->method))){
						return;
					}else{
						Error::getErrorPage('404.php');
					}
				}
			}
		}
		
		// If is a directory the $this->controller get the Index Controller,
		// because index controller is default for sub dir
		if(is_dir(str_replace(array('/', '\\'), DS, $this->ctrlDir . substr($this->controller, 27)))){
			// add Index controller for the pager
			$this->controller .= 'IndexController';
			if(is_callable(array((string)$this->controller, (string)$this->method))){
				return;
			}
		}
		
		// if number of the parts is greater then 0
		// and not is false
		// and different of the method default set new method
		if(count($parts) && strlen($parts[0]) && $parts[0] != $this->method){
			// validate the method
			$this->method = $this->validUrl2Func(array_shift($parts));
		}
		
		// if have more arguments from URL, get the requesteds
		if(count($parts) > 0){
			$this->args = $parts;
		}
		
		// if can call method
		if(is_callable(array((string)$this->controller, (string)$this->method))){
			return;
		}else{
			// Else call the 404 page
			if(!isset($this->routes['_404']) || empty($this->routes['_404_'])){
				Error::getErrorPage('404.php');
			}else{
				$this->routes['_404_'] = explode('/', trim($this->routes['_404_'], '/'));
				
				// get the method
				$_404M = array_pop($this->routes['_404_']);
				
				// set the controller and method
				$this->controller = App::getAppDir() . implode('\\', $this->routes['_404_']);
				$this->method = $_404M;
				
				if(is_callable(array((string)$this->controller, (string)$this->method))){
					return;
				}else{
					Error::getErrorPage('404.php');
				}
			}
		}
	}
}
