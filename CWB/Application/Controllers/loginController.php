<?php

namespace CWB\Application\Controllers;

use CWB\Lib\Controller;
use CWB\Lib\Util\Email;
use CWB\Lib\Helper;

/**
* Class loginController
* Get All the Information About the System
*
* @package CWB
**/
class LoginController extends Controller{
	public function init(){
		parent::init();
		if($this->validateLogin()){
			if($this->userType == "admin"){
				header('Location: /admin');
				exit();
			}elseif($this->userType == "member"){
				header('Location: /member');
				exit();
			}
		}
	}
	
	/**
	*  return login page view
	**/
	public function index(){
		if($_SERVER["REQUEST_METHOD"] == 'POST' && Helper::isAjax() && isset($_POST['login'], $_POST['email'], $_POST['password'])){
			$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			$password = $_POST['password']; // Hashed Password
			
			return $this->_model->checklogin($email, $password);
		}
		
		return $this->view('login');
	}
	
	public function register(){
		if($_SERVER["REQUEST_METHOD"] == 'POST' && Helper::isAjax() && isset($_POST['name'], $_POST['email'], $_POST['password'])){
			return $this->_model->register();
		}
		
		return $this->view('register');
	}
	
	/**
	*  return login page view
	**/
	public function forget(){
		if($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['recovery-email'])){
			$email = filter_input(INPUT_POST, 'recovery-email', FILTER_SANITIZE_EMAIL);
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			$key = $this->_model->getToken(10); // Hashed Password
			$password = hash('sha512', $key);
			$error = array();
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				// Not a valid email
				$error_msg[] = 'The Email address you entered is not valid';
			}
			
			if(strlen($password) != 128){
				// The hashed password should be 128 characters long
				// If it's not, something really odd has happened
				$error_msg[] = 'Invalid password configuration';
			}
			
			
			$params = Array("$email");
			if(empty($error_msg) && $user = $this->_model->getUserDataByEmail($params)){
				$data = Array(
					'password' => Array($password . $user->salt),
				);
				
				$this->_model->updateUserData(array("id"=>$user->id), $data);
				
				//$data = file_get_contents(ROOT . DS . 'public' . DS . 'email' . DS . 'activationEmail.php');
				//$data = str_replace("{{{CURRENT_YEAR}}}", date('Y'), $data);
				
				//$encoder = new Encoder();
				//$link = $encoder->encode($storename);
				
				//$data = str_replace("{{{ACTIVATION_LINK}}}", $link, $data);
				$emailClass = new Email();
				$emailClass->mailtype = Email::MAILTYPE_HTML;
				$emailClass->from("developer@crazywapbox.com", "Blog Admin");
				$emailClass->to($email);
				$emailClass->subject("Forgot Password");
				$emailClass->message("Your new password : {$key}");
				$emailClass->send();
				
				/**
				$this->_model->addEmail(
					array(
						"fromEmail" => "shop@yourdreamstores.com",
						"fromName" => "Yourdreamstores.com",
						"toEmail" => $email,
						"subject" => 'Recover Password Of Your Webshop',
						"content" => "Your password is : {$key}"
					)
				);
				**/
				//return $key;
			}
		}
		header('Location: /login');
		exit();
	}
	
}