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
		$pageTitle = '';
		$pageDescription = '';
		$pageKeyword = '';
		$pageCanonical = '';
		$homeActive = ' active';
		
		return $this->view('home');
	}
		
	/**
	* Handle logout
	**/
	public function logout(){
		// unset all session values
		$_SESSION = array();
		
		// Get session parameters
		$params = session_get_cookie_params();
		
		// Delete the actual cookie
		setcookie('session_name', '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		
		// Destroy session
		session_destroy();
		
		header("Location: /login");
		exit();
	}
}