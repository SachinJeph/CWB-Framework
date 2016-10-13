<?php
namespace CWB\Lib;

use CWB\Config\App;
use CWB\Lib\DB\MysqliDb;

class Model {
	protected $_model;
	
	public $db,
		   $shopDB;
	
	public function __construct(){
		$this->_model = get_class($this);
		
		$this->init();
		
		if(App::DB_NEED){
			$this->connectToDatabase();
		}
	}
	
	protected function init(){}
	
	protected function connectToDatabase(){
		$mysqli = new \mysqli(App::DB_HOST, App::DB_USER, App::DB_PASS, App::DB_NAME);
		
		if($mysqli->connect_error){
			// Error : Unable to connect to MySql(Database)
			header("Location: /error.php?errorCode=0000");
			exit();
		}
		
		$this->db = new MysqliDb($mysqli);
		if(!$this->db->ping()){
			// Error : Database is not running up
			header("Location: /error.php?errorCode=0001");
			exit();
		}
	}
	
	private function crypto_rand_secure($min, $max){
		$range = $max - $min;
		if ($range < 1) return $min; // not so random...
		$log = ceil(log($range, 2));
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd >= $range);
		return $min + $rnd;
	}

	public function getToken($length){
		$token = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		$codeAlphabet.= "0123456789";
		$max = strlen($codeAlphabet) - 1;
		for ($i=0; $i < $length; $i++) {
			$token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
		}
		return $token;
	}
}