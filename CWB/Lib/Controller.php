<?php

namespace CWB\Lib;

use CWB\Config\App;
use CWB\Lib\Helper as Helper;

/**
* Class Controller
*
* @package CWB/Lib
**/
class Controller{
	
	protected $_model,
			  $_controller,
			  $_action;
			  
	protected $userType = null,
			  $email = null,
			  $userID = null;
	
	protected $cfg,
			  $view;
	
	protected $defalutTemplate = 'Classic';
	
	public function __construct($model="\CWB\Lib\Model", $controller="Controller", $action="index"){
		// register configurations
		$this->cfg = array();
		
		// construct MVC
		$this->_controller = $controller;
		$this->_action = $action;
		
		// initialize the template class
		$this->view = new Template($controller, $action);
		
		// Strat construct models
		$this->_model = new $model();
	
		// Call the function to set other extra prameter for the calss
		$this->init();
		
	}
	
	protected function init(){}
	
	/** process default action view **/
	public function defaultAction($params = null){
		// make the default action path
		$path = Helper::UrlContent("~/Views/{$this->defalutTemplate}/{$this->_controller}/{$this->_action}.php");
		
		// if we have action name
		if(file_exists($path)){
			$this->view->directViewPath = true;
			$this->view->viewPath = $path;
		}else{
			return $this->unknownAction();
		}
		
		// if we have parameteres
		if(!empty($params) && is_array($params)){
			// assign local variables
			foreach($params as $key=>$value){
				$this->view->set($key, $value);
			}
		}
		
		// dispatch the result
		return $this->view();
	}
	
	/** process unknown action view **/
	public function unknownAction($params = array()){
		// Feed 404 header to the client
		header("HTTP/1.0 404 Not Found");
		
		// find custom 404 page
		$path = Helper::UrlContent("~/Views/{$this->defalutTemplate}/shared/_404.php");
		
		// if we have custom 404 page, the use it
		if(file_exists($path)){
			$this->view->directViewPath = true;
			$this->view->viewPath = $path;
			return $this->view;
		}
		
		// find common 404 page
		$path = Helper::UrlContent("~/Views/shared/_404.php");
		if(file_exists($path)){
			$this->view->directViewPath = true;
			$this->view->viewPath = $path;
			return $this->view;
		}else{
			// Do not do any more work in this script
			// Else call the 404 page
			\CWB\Core\Error::getErrorPage('404.php');
			exit();
		}
	}
	
	/** Set Variables **/
	public function _set($name, $value){
		// set the parameteres to the template class
		$this->view->set($name, $value);
	}
	
	/** Returns the template result **/
	public function view($path = ''){
		// Set the path of view
		if($path) $this->view->viewPath = $path;
		// Dispatch the result of the template class
		return $this->view;
	}
	
	/** Returns the login state **/
	public function validateLogin(){
		if(isset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['login_string'], $_SESSION['user_type'])){
			$user_id = $_SESSION['user_id'];
			$email = $_SESSION['email'];
			$login_string = $_SESSION['login_string'];
			$user_type = $_SESSION['user_type'];
			
			// Get the user-agent string of the user
			$user_browser = $_SERVER['HTTP_USER_AGENT'];
			
			$params = Array($user_id);
			if($user = $this->_model->getUserData($params)){
				if($user != null){
					$login_check = hash('sha512', $user->password . $user_browser);
					if($login_check == $login_string){
						// User is Logged In!!!
						$this->userType = $user_type;
						$this->email = $email;
						$this->userID = $user_id;
						return true;
					}else{
						// User is Not Logged In!!!
						return false;
					}
				}
				
				// Error : User Not Found
				header("Location: /error.php?errorCode=2001");
				exit();
			}
			
			// Error : validateLogin() won't work properly
			header("Location: /error.php?errorCode=2000");
			exit();
		}else{
			// User is not Logged in
			return false;
		}
	}
}