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
	* Route type
	**/
	protected $routes = [
		'GET' => [],
		'POST' => [],
		'ANY' => [],
		'PUT' => [],
		'DELETE' => [],
	];
	
	public $patterns = [
		':any' => '.*',
		':id' => '[0-9]+',
		':slug' => '[a-z\-]+',
		':name' => '[a-zA-Z]+',
		':key' => '[a-zA-Z0-9]+',
	];
	
	const REGVAL = '/({:.+?})/';	
	
	/**
	* request of the URL
	* @var string $pathURL
	**/
	public $pathURL;
	
	/**
	* Contains the redirects of the routes
	* @var array $routes
	**/
	protected $_route;
	
	private $_controllerDir = 'CWB\Application\Controllers\\';
	private $_modelDir = 'CWB\Application\Models\\';

	public $_controller,
		   $_action,
		   $_view,
		   $_params;
	
	/**
	* Controller type = namespace\class
	* @var string $controller
	**/
	public $controller = 'CWB\Application\Controllers\\';
	
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
	public $ctrlDir,
		   $modelDir;
		   
	/**
	* init the router
	* load the directorys, parse routes, aliases, Controller\Method.
	**/
	public function __construct(){
		$this->ctrlDir = App::getAppDir() . 'Controllers' . DS; // directory of controllers
		$this->modelDir = App::getAppDir() . 'Models' . DS; // directory of controllers
		
		$this->_route = (strlen($_SERVER['REQUEST_URI']) != 1) ? rtrim($_SERVER['REQUEST_URI'], '/') : $_SERVER['REQUEST_URI'];
		$this->_controller = 'Controller';
		$this->_action = 'index';
		$this->_params = array();
		$this->_view = false;
	}
	
	public function group($type, $link, $pathHandler){
		foreach($pathHandler as $path => $handler){
			$this->addRoute($type, $link . ltrim($path, '/'), ltrim($link , '/') . '.' . $handler);
		}
	}
	
	public function any($path, $handler){
		$this->addRoute('ANY', $path, $handler);
	}
	
	public function get($path, $handler){
		$this->addRoute('GET', $path, $handler);
	}
	
	public function post($path, $handler){
		$this->addRoute('POST', $path, $handler);
	}
	
	public function delete($path, $handler){
		$this->addRoute('DELETE', $path, $handler);
	}
	
	protected function addRoute($method, $path, $handler){
		array_push($this->routes[$method], [$path => $handler]);
	}
	
	public function match(array $post){
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		$requestUri = $this->_route;
		
		$restMethod = $this->getRestfullMethod($post);
		
		if(!$restMethod && !in_array($requestMethod, array_keys($this->routes))){
			return FALSE;
		}
		
		$method = $restMethod ? : $requestMethod;
		
		foreach($this->routes[$method] as $resource){
			$args = [];
			$route = key($resource);
			$handler = reset($resource);
			
			if(preg_match(self::REGVAL, $route)){
				list($args, $uri, $route) = $this->parseRegexRoute($requestUri, $route);
			}
			
			if(!preg_match("#^$route$#", $requestUri)){
				unset($this->routes[$method]);
				continue;
			}
			
			if(is_string($handler) && strpos($handler, '@')){
				list($ctrl, $method)= explode('@', $handler);
				return ['controller'=>$ctrl, 'method'=>$method, 'args'=>$args];
			}
			
			if(empty($args)){
				return $handler();
			}
			
			return call_user_func_array($handler, $args);
		}
		
		
		$this->parseRoute();
		$controllerName = $this->_controller;
		
		// set model name
		$model = $this->_controller . 'Model';
		// if we have extended model
		$model = class_exists($this->_modelDir . $model) ? $this->_modelDir . $model : '\CWB\Lib\Model';
		
		// assign controller full name
		$this->_controller .= 'Controller';
		// if we have extended controller
		$this->_controller = class_exists($this->_controllerDir . $this->_controller) ? $this->_controllerDir . $this->_controller : '\CWB\Lib\Controller';
		
		// construct the controller class
		$dispatch = new $this->_controller($model, $controllerName, $this->_action);
		
		// if we have action function in controller
		$hasActionFunction = (int) method_exists($this->_controller, $this->_action);
		
		// We need to reference the parameters to a correct order in order to match the arguments order of the calling function
		$c = new \ReflectionClass($this->_controller);
		$m = $hasActionFunction ? $this->_action : 'defaultAction';
		$f = $c->getMethod($m);
		$p = $f->getParameters();
		
		$params_new = array();
		$params_old = $this->_params;
		
		// re-map the parameters
		for($i=0; $i<count($p); $i++){
			$key = $p[$i]->getName();
			if(array_key_exists($key, $params_old)){
				$params_new[$i] = $params_old[$key];
				unset($params_old[$key]);
			}
		}
		
		// after reorder, merge the leftovers
		$params_new = array_merge($params_new, $params_old);
		
		// call the action method
		$this->_view = call_user_func_array(array($dispatch, $m), $params_new);
	}
	
	private function parseRoute(){
		$id = false;
		
		// Parse path info
		if(isset($this->_route)){
			// The request path
			$path = ltrim($this->_route, '/');
			
			// the roules to routes
			$cai = '/^([\w]+)\/([\w]+)\/([\d]+).*$/';	//  controller/action/id
            $ci  = '/^([\w]+)\/([\d]+).*$/';			//  controller/id
            $ca  = '/^([\w]+)\/([\w]+).*$/';			//  controller/action
            $c   = '/^([\w]+).*$/';						//  action
            $i   = '/^([\d]+).*$/';						//  id
			
			// initialize the matches
			$matches = array();
			
			// If this is home page route
			if(empty($path)){
				$this->_controller = 'index';
				$this->_action = 'index';
			}else if(preg_match($cai, $path, $matches)){
				$this->_controller = $matches[1];
				$this->_action = $matches[2];
				$id = $matches[3];
			}else if(preg_match($ci, $path, $matches)){
				$this->_controller = $matches[1];
				$id = $matches[2];
			}else if(preg_match($ca, $path, $matches)){
				$this->_controller = $matches[1];
				$this->_action = $matches[2];
			}else if(preg_match($c, $path, $matches)){
				$this->_controller = $matches[1];
				$this->_action = 'index';
			}else if(preg_match($i, $path, $matches)){
				$id = $matches[1];
			}
			
			// get query string from url
			$query = array();
			$parse = parse_url($path);
			
			// if we have query string
			if(!empty($parse['query'])){
				// Parse query string
				parse_str($parse['query'], $query);
				
				// if query parameter is parsed
				if(!empty($query)){
					// merge the query parameters to $_GET variables
					$_GET = array_merge($_GET, $query);
					
					// merge the query parameters to $_REQUEST variables
					$_REQUEST = array_merge($_REQUEST, $query);
				}
			}
		}
		
		// gets the request method
		$method = $_SERVER['REQUEST_METHOD'];
		
		// assign params by methods
		switch($method){
			case "GET": //view
				// we need to remove _route in the $_GET params
				unset($_GET['_route']);
				
				// merge the params
				$this->_params = array_merge($this->_params, $_GET);
				break;
			
			case "POST" : // CREATE
			case "PUT" : // UPDATE
			case "DELETE" : // DELETE
			{
				if(!array_key_exists('HTTP_X_FILE_NAME', $_SERVER)){
					if($method == "POST"){
						$this->_params = array_merge($this->_params, $_POST);
					}else{
						// temp params
						$p = array();
						
						// the request payload
						$content = file_get_contents("php://input");
						
						// parse the content string to check we have [data] field or not
						parse_str($content, $p);
						
						// if we have data field
						$p = json_decode($content, true);
						
						// merge the data to existing params
						$this->_params = array_merge($this->_params, $p);
					}
				}
			}
				break;
		}
		
		// set param id to the id we have
		if(!empty($id)){
			$this->_params['id'] = $id;
		}
		
		if($this->_controller == 'index'){
			$this->_params = array($this->_params);
		}
	}
	
	protected function getRestfullMethod($postVar){
		if(array_key_exists('_method', $postVar)){
			if(in_array($method, array_keys($this->routes))){
				return $method;
			}
		}
	}
	
	protected function parseRegexRoute($requestUri, $resource){
		$route = preg_replace_callback(self::REGVAL, function($matches){
			$patterns = $this->patterns;
			$matches[0] = str_replace(['{', '}'], '', $matches[0]);
			
			if(in_array($matches[0], array_keys($patterns))){
				return $patterns[$matches[0]];
			}
		}, $resource);
		
		$regUri = explode('/', $resource);
		
		$args = array_diff(array_replace($regUri, explode('/', $requestUri)), $regUri);
		
		return [array_values($args), $resource, $route];
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
	
	/**
	* set the method and controller
	**/
	public function setFunc($controller, $method, array $args){
		$controller = str_replace('.', '/', $controller);
		$this->method = $this->validUrl2Func($method);
		$this->args = $args;
		
		if(strpos($controller, '/') !== false){
			$controllerDir = explode('/', $controller);
			
			$part = '';
			foreach($controllerDir as $c){
				if(empty($c)){
					break;
				}
				
				// valid for name of class and file
				$c = $this->validUrl2Func($c);
				$part .= ucfirst($c);
				
				// if is a directory add more one path and continue
				if(is_dir($this->ctrlDir . $part)){
					$part .= '/';
					$this->controller .= ucfirst($c) . '\\';
					continue;
				}else if(class_exists($this->controller . $c)){
					// If is a file into a Controller get the namespace\class and stop this loop
					$this->controller .= $c;
					break;
				}else{
					// if not found file or directory call the '404 - Not Found'
					Error::getErrorPage('404.php');
				}
			}
		}else{
			$this->controller .= $controller;
		}
		
		// If is a directory the $this->controller get the Index Controller,
		// because index controller is default for sub dir
		if(is_dir(str_replace(array('/', '\\'), DS, $this->ctrlDir . substr($this->controller, 28)))){
			// add Index controller for the pager
			$this->controller .= 'IndexController';
			if(is_callable(array((string)$this->controller, (string)$this->method))){
				return;
			}
		}
		
		// if can call method
		if(is_callable(array((string)$this->controller, (string)$this->method))){
			return;
		}else{
			// Else call the 404 page
			Error::getErrorPage('404.php');
		}
	}
}