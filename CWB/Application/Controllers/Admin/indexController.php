<?php

namespace CWB\Application\Controllers\Admin;

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
		if(!$this->validateLogin() || ($this->userType !=  'admin')){
			header('Location: /login');
			exit();
		}
		$this->view->set('utility', new \CWB\Lib\Util\Utility());
	}
	
	/**
	*  return Home page view
	**/
	public function index(){
		header("Location: /admin/dashboard");
		exit();
	}
	
	public function dashboard(){
		return $this->view('admin/dashboard');
	}
}