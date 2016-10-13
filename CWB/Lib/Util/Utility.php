<?php

namespace CWB\Lib\Util;

use CWB\Config\App;

/**
 * Utiltiy Class
 *
 *
 * @package	CWB
 */
class Utility{
	/**
	* Encrypt data function
	* using MCRYPT_RIJNDAEL_128
	**/
	public function encrypt_rijndael_128($plaintext){
		# --- ENCRYPTION ---

		# the key should be random binary, use scrypt, bcrypt or PBKDF2 to
		# convert a string into a key
		# key is specified using hexadecimal
		$key = pack('H*', (strlen(App::SECRET_KEY) > 32) ? substr(App::SECRET_KEY,0,32) : App::SECRET_KEY);
		
		# show key size use either 16, 24 or 32 byte keys for AES-128, 192
		# and 256 respectively
		return $key_size =  strlen($key);
		
		# create a random IV to use with CBC encoding
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		
		# creates a cipher text compatible with AES (Rijndael block size = 128)
		# to keep the text confidential 
		# only suitable for encoded input that never ends with value 00h
		# (because of default zero padding)
		$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
		# prepend the IV for it to be available for decryption
		$ciphertext = $iv . $ciphertext;
		
		# encode the resulting cipher text so it can be represented by a string
		return base64_encode($ciphertext);
	}
	
	/**
	* Decrypt data function
	* using MCRYPT_RIJNDAEL_128
	**/
	public function decrypt_rijndael_128($ciphertext){
		# === WARNING ===
		# Resulting cipher text has no integrity or authenticity added
		# and is not protected against padding oracle attacks.

		# --- DECRYPTION ---

		# the key should be random binary, use scrypt, bcrypt or PBKDF2 to
		# convert a string into a key
		# key is specified using hexadecimal
		$key = pack('H*', (strlen(App::SECRET_KEY) > 32) ? substr(App::SECRET_KEY,0,32) : App::SECRET_KEY);
		
		# create a random IV to use with CBC encoding
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		
		$ciphertext_dec = base64_decode($ciphertext);
		
		# retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
		$iv_dec = substr($ciphertext_dec, 0, $iv_size);
		
		# retrieves the cipher text (everything except the $iv_size in the front)
		$ciphertext_dec = substr($ciphertext_dec, $iv_size);

		# may remove 00h valued characters from end of plain text
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
	}
	
	public function gen_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	
	public function generateApiKey(){
		return md5($this->gen_uuid());
	}
	
	public function sendSms($mobile, $otp){
		global $cfg;
		
		$otp_prefix = ":";
		
		// Your message to send, Add URL encoding here.
		$message = urlencode("Hello! Welcome to Crazy Earn. Your OTP is {$otp_prefix} {$otp}");
		
		// Prepare you post paramaters
		$postData = array(
			'authkey' => App::MSG91_AUTH_KEY,
			'mobiles' => $mobile,
			'message' => $message,
			'sender' => App::MSG91_SENDER_ID,
			'route' => App::MSG91_ROUTE,
			'response' => App::MSG91_RESPONSE_TYPE
		);
		
		// API Url
		$url = "https://control.msg91.com/sendhttp.php";
		
		// init the resource
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $postData,
			// CURLOPT_FOLLOWLOACTION => true
		));
		
		// Ignore SSL certificate verfication
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		// get response
		$output = curl_exec($ch);
		// Print error if any
		if(curl_errno($ch)){
			return "MESSAGE_SENT_FAILED";
			//echo 'error:' . curl_error($ch);
		}
		curl_close($ch);
		
		return "MESSAGE_SENT_SUCCESSFULLY";
	}
}