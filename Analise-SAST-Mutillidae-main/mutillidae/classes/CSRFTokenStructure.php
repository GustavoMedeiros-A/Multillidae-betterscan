<?php 
	class CSRFTokenStructure{ 
		private $mCSRFToken = ""; 
		private $mPage = "";
		private $mUser = "";

		private $min = 10000000;
		private $max = 10000000;

		
		public function __construct($pPage, $pUser){
			if (CRYPT_BLOWFISH == 1) {
				$lCSRFToken = crypt(random_int(PHP_INT_MIN, PHP_INT_MAX), '$2a$10$'.random_int(PHP_INT_MIN, PHP_INT_MAX));
			}elseif (CRYPT_SHA256 == 1){
				$lCSRFToken = crypt(random_int(PHP_INT_MIN, PHP_INT_MAX), '$1$'.random_int(PHP_INT_MIN, PHP_INT_MAX));
			}else{
				$lCSRFToken = mt_rand();
			}// end if
			
			$this->mCSRFToken = $lCSRFToken; 
			$this->mPage = $pPage;
			$this->mUser = $pUser;
				
		}// end function

		public function getCSRFToken(){
			return $this->mCSRFToken;
		}// end function
			
	}// end class
?>