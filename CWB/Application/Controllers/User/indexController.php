<?php

namespace CWB\Application\Controllers\User;

use CWB\Lib\Controller;
use CWB\Lib\Helper;


/**
* Class indexController
* Get All the Information About the System
*
* @package CWB
**/
class IndexController extends Controller{
	
	public function init(){
		if(!$this->validateLogin() || ($this->userType !=  'member')){
			header('Location: /login');
			exit();
		}
		$this->view->set('utility', new \CWB\Lib\Util\Utility());
	}
	
	/**
	*  return Home page view
	**/
	public function index(){
		header("Location: /member/dashboard");
		exit();
		//echo $this->userID;
	}
	
	public function dashboard(){
		
		return $this->view('user/dashboard');
	}
}