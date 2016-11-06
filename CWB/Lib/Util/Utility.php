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
	
	public function get_timeago($ptime){
		$estimate_time = time() - strtotime($ptime);
		
		if( $estimate_time < 1 ){
			return 'less than 1 second ago';
		}
		
		$condition = array(
			12 * 30 * 24 * 60 * 60  =>  'year',
			30 * 24 * 60 * 60       =>  'month',
			24 * 60 * 60            =>  'day',
			60 * 60                 =>  'hour',
			60                      =>  'minute',
			1                       =>  'second'
		);
		
		foreach( $condition as $secs => $str ){
			$d = $estimate_time / $secs;
			if( $d >= 1 ){
				$r = round( $d );
				return 'about ' . $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
			}
		}
	}
}