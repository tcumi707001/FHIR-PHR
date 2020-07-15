<?php
require_once 'D:www/vendor/firebase/php-jwt/src/BeforeValidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/ExpiredException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/SignatureInvalidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/JWT.php';

use \Firebase\JWT\JWT; //導入官方模組

if(isset($_COOKIE['authenicationtoken'])){
	$authenicationtoken = $_COOKIE['authenicationtoken'];
	$privkeypass = '111111'; //私鑰密碼  
	$numberofdays = 365;     //有效時間(日) 
	$csrpath = "./serverptx/server.pem"; //伺服器證書
	$cerpath = "./serverptx/server.cer"; //證書路徑 
	$pfxpath = "./serverptx/server.pfx"; //證書存儲文件路徑
	$pempath = "./serverptx/privkey.pem"; //私鑰文件路徑
	$keypath = "./serverptx/pubkey.key"; //公鑰文件路径

	$portal_privateKey = openssl_pkey_get_private(file_get_contents($pempath),$privkeypass); //開啟私鑰文件
	$portal_publickey = openssl_pkey_get_public(file_get_contents($keypath)); //開啟公鑰文件

	$jwt = explode(".",$authenicationtoken); //分段
	$header = json_decode(JWT::urlsafeB64Decode($jwt[0]),true);

	try{
		$decoded = JWT::decode($authenicationtoken, $portal_publickey, array($header['alg'])); //以公鑰驗證，驗證成功則回傳內容，驗證內容中已經包驗證payload內時間內容 iat、exp、ndf
		echo true;
	}catch (RuntimeException $e){
		echo false;
	}
}else{
	//echo "<<認證token不存在>>";
	echo false;
}
?> 