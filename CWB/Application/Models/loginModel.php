<?php

namespace CWB\Application\Models;

use CWB\Lib\Model;

/**
* Class loginModel
* Get All the Information About the System
*
* @package CWB
**/
class loginModel extends Model{
	public function getUserDataByEmail($params){
		return $this->db->ObjectBuilder()->rawQueryOne('SELECT * FROM users WHERE email=?', $params);
	}
	
	public function register(){
		// Sanitize and validate the data passed in
		$error = array();

		$name = isset($_POST['name']) ? filter_input(INPUT_POST, 'name', FILTER_SANITIZE_EMAIL) : '';
		$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
		$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
		
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			// Not a valid email
			$error_msg[] = 'The Email address you entered is not valid';
		}
	
		if(strlen($password) != 128){
			// The hashed password should be 128 characters long
			// If it's not, something really odd has happened
			$error_msg[] = 'Invalid password configuration';
		}
	
		// Username validity and password validity have been checked client side
		// This should be adequate as nobody gains any advantage from breaking these rules
		if($user != $this->getUserDataByEmail(Array("$email"))){
			$error_msg[] = 'A user with this email address already exists';
		}
	
		if(empty($error_msg)){
			// Create a random salt
			$random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));
			$data = Array(
				'fullname' => $name,
				'email' => $email,
				'password' => $this->db->func('SHA(?)', Array($password . $random_salt)),
				'salt' => $random_salt,
				'userType' => 'member',
				'status' => 'Suspended',
				'createdAt' => $this->db->now()
			);
			
			if($this->db->insert("users", $data)){
				return "SUCCESS";
			}else{
				$error_msg[] = 'Please Try Again';
			}
		}
		return $error_msg[0];
	}
	
	public function updateUserData($condition, $data){
		foreach($condition as $key=>$value){
			$this->db->Where($key, $value);
		}
		
		if(isset($data['password'])){
			$data['password'] = $this->db->func('SHA(?)', $data['password']);
		}
		
		return $this->db->update('users', $data);
	}
	
	public function checklogin($email = null, $password = null){
		$params = array("$email");
		
		// Using prepared statements means that SQL injection is not possible
		if($user = $this->db->ObjectBuilder()->rawQueryOne("SELECT id, email, password, salt, userType FROM users WHERE email=?", $params)){
			$user_id = $user->id;
			$email = $user->email;
			$salt = $user->salt;
			
			// Hash the password with unique salt
			$password = hash('sha1', $password . $salt);
			
			if($this->checkBrute($user->id) == true){
				// Account is locked
				// Send an email to user saying their account is locked
				
				// Error: Account Blocked, Brute Force Attack found
				return "bruteForceAttackFound";
				//header("Location: /error.php?errorCode=bruteForceAttackFound");
				//exit();
			}else{
				// Check if the password in the database matches
				if($user->password == $password){
					// Password is correct!
					// Get the user-agent string of the user
					$user_browser = $_SERVER['HTTP_USER_AGENT'];
					
					// xss protection as we might print this value
					$user_id = preg_replace("/[^0-9]+/", "", $user_id);
					$_SESSION['user_id'] = $user_id;
					
					// xss protection as we might print this value
					$email = preg_replace("/[^a-zA-Z0-9_\-@.]+/", "", $email);
					$_SESSION['email'] = $email;
					$_SESSION['login_string'] = hash('sha512', $password . $user_browser);
					$_SESSION['user_type'] = $user->userType;
					
					// Login successful
					return "true";
				}else{
					// Password is Incorrect!
					// Record this attempt
					$now = time();
					$data = array(
						'userId' => $user_id,
						'time' => $this->db->now()
					);
					
					if(!$this->db->insert("login_attempts", $data)){
						// Error: Record won't log
						header("Location: /error.php?errorCode=loginModel2");
						exit();
					}
					
					return "false";
				}
			}
		}
		return "noUserFound";
	}
	
	private function checkBrute($user_id = null){
		// Get timestamp of current time
		$now = time();
		
		// All login attempts are counted from the past 2 hours
		$valid_attempts = $now - ((2*60*60)+(2.5*60*60));
		$params = array($user_id, "'$valid_attempts'");
		$login = $this->db->ObjectBuilder()->rawQueryOne("SELECT count(time) as count FROM login_attempts WHERE userId=$user_id AND time>$valid_attempts");
		if($login != null){
			if($login->count > 10){
				return true;
			}else{
				return false;
			}
		}else{
			// Error: Database error: Query won't process
			header("Location: /error.php?errorCode=loginModel1");
			exit();
		}
	}
	
	
	public function addEmail($data){
		$data["createdAt"] = $this->db->now();
		$data["status"] = 0;
		
		$this->db->insert("emails", $data);
	}
}