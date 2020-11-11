<?php
class Encryption
{
	const CIPHER = MCRYPT_RIJNDAEL_128; // Rijndael-128 is AES
	const MODE   = MCRYPT_MODE_CBC;

	/* Cryptographic key of length 16, 24 or 32. NOT a password! */
	private $key;
	public function __construct($key) {
		$this->key = $key;
	}
	public static function encrypt1($message, $key)
	{
		if (mb_strlen($key, '8bit') !== 32) {
			throw new Exception("Needs a 256-bit key!");
		}
		$ivsize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256,MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($ivsize, MCRYPT_DEV_URANDOM);
	
		// Add PKCS7 Padding
		$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_256,MCRYPT_MODE_CBC);
		$pad = $block - (mb_strlen($message, '8bit') % $block . '8bit');
		$message .= str_repeat(chr($pad), $pad);
	
		$ciphertext = mcrypt_encrypt(
				MCRYPT_RIJNDAEL_256,
				$key,
				$message,
				MCRYPT_MODE_CBC,
				$iv
				);
	
		return base64_encode($iv . $ciphertext);
	}
	public function encrypt($plaintext,$key) {
		//$key = "c2l0ZV8xNDAwLHZlcl8zLjE=########";
		$key_size =  strlen($key);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		//$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$iv = chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00');
		//$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_256);
		$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,$plaintext, MCRYPT_MODE_CBC, $iv);
		$ciphertext = $ciphertext;
		$ciphertext_base64 = base64_encode($ciphertext);
		return $ciphertext_base64;
	}
	
// 	function encrypt($data) {
// 		$key = "c2l0ZV8xNDAwLHZlcl8zLjE=########";
// 		// Remove the base64 encoding from our key
// 		//$encryption_key = base64_decode($key);
// 		// Generate an initialization vector
// 		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
// 		// Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
// 		$encrypted = openssl_encrypt($data, 'aes-256-cbc', $key,OPENSSL_RAW_DATA, $iv);
// 		// The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
// 		return base64_encode($iv.$encrypted);
// 	}

	public function decrypt($ciphertext,$key) {
		//$key = "c2l0ZV8xNDAwLHZlcl8zLjE=########";
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$ciphertext_dec = base64_decode($ciphertext);
		# retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
		$iv_dec = substr($ciphertext_dec, 0, $iv_size);
		
		# retrieves the cipher text (everything except the $iv_size in the front)
		$ciphertext_dec = substr($ciphertext_dec, $iv_size);
		
		# may remove 00h valued characters from end of plain text
		$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key,
		$ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
		return $plaintext_dec;
	}
	

	function decryptN($garble,$key) {
	  $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	  $combo = base64_decode($garble);
	  $iv =  chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00').chr('\x00');
	  $crypt = $combo;//substr($combo, $iv_size, strlen($combo));
	  $payload = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypt, MCRYPT_MODE_CBC, $iv);
	  return $payload;
	}
	
	
}