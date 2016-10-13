<?php

namespace CWB\Application\Controllers;

use CWB\Lib\Controller;

/**
* Class indexController
* Get All the Information About the System
*
* @package CWB
**/
class IndexController extends Controller{
	/**
	*  return Home page view
	**/
	public function index(){
		return $this->view('home');
	}
}