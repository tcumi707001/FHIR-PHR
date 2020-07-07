<?php

/*
JWT(HS256) SHA256加密簽章方法 (PHP)
*/

require_once 'D:www/vendor/firebase/php-jwt/src/BeforeValidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/ExpiredException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/SignatureInvalidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/JWT.php';

use \Firebase\JWT\JWT; //導入官方模組

$key = 'ABCDEFG'; //簽章密碼

echo "Key:" . "<br>" . $key . "<br>";

$payload = array(
	 "iss"=>"https://203.64.84.229:8011/FHIR_PHR/portal.html",
	 "aud"=>"https://203.64.84.229:8011/FHIR_PHR/",
     "sub"=>"1",
	 "roles"=>"1",
     "iat"=> 0,
	 "ndf"=> 0,
	 "exp"=> 0,
     "roles"=>"1",
     "PurposeOfUse"=>"medicationrequest",
	 "scope"=>array(
	 "method"=>"GET",
	 "url"=>"1"
	 )
); //之後改成由讀取外部文件
	$payload["iat"] = time();
	$payload["ndf"] = time();
	$payload["exp"] = time()+ 300000;

$jwt = JWT::encode($payload, $key); //以key簽章，生成token
echo "<br>" . "JWT(HS256):" . "<br>" . print_r($jwt , true) . "<br>";

$decoded = JWT::decode($jwt, $key, array('HS256')); //以key驗章，成功返還payload
echo "<br>" . "payload:" . "<br>" . print_r($decoded , true) . "<br>";
?>