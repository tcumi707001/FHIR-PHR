<?php

/*
JWT(RS256) 私鑰簽章公鑰驗證方法 (PHP)
*/

require_once 'D:www/vendor/firebase/php-jwt/src/BeforeValidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/ExpiredException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/SignatureInvalidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/JWT.php';

use \Firebase\JWT\JWT; //導入官方模組

$privkeypass = '111111'; //私鑰密碼  
$numberofdays = 365;     //有效時間(日) 
$csrpath = "./serverptx/server.pem"; //伺服器證書
$cerpath = "./serverptx/server.cer"; //證書路徑 
$pfxpath = "./serverptx/server.pfx"; //證書存儲文件路徑
$pempath = "./serverptx/privkey.pem"; //私鑰文件路徑
$keypath = "./serverptx/pubkey.key"; //公鑰文件路径

$privateKey = openssl_pkey_get_private(file_get_contents($pempath),$privkeypass); //開啟鑰文件
$publicKey = openssl_pkey_get_public(file_get_contents($keypath)); //開啟鑰文件

echo "privateKey:" . "<br>" . file_get_contents($pempath) . "<br>" . "<br>";
echo "publicKey:" . "<br>" . file_get_contents($keypath) . "<br>" . "<br>";

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

$jwt = JWT::encode($payload, $privateKey, 'RS256'); //以私鑰章,生成token
echo "JWT(RS256):" . "<br>" . print_r($jwt, true) . "<br>";

$token = explode(".",$jwt); //取得token並分段
$header = json_decode(base64_decode($token[0]),true);
$payload = json_decode(base64_decode($token[1]),true); 
$signature = $token[2];

//$T_JWT = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE1OTAzODQzMjMsInVzZXJJZCI6IjkzIiwiZXhwIjoxNTkwMzg2MTIzLCJpc3MiOiJUQ1UgTUlUVyBFZHVjYXRpb24gUG9ydGFsIiwic3ViIjoiQXV0aGVudGljYXRpb24gVG9rZW4iLCJqdGkiOiI2YWExZjYxMy0yMjYwLTQ5ZDktOGMyZC0yMjk1NmI2MTg2YTYifQ.QcJq8AJuifDIqWBAmsj0Bwksbn9StDr1hIEXDHu0hDT92BNmuyKmjulv7NARWpPHiqHadiyJWrWcEDOLuk22sxnsFW0Hyz5aEzJVa9kRAfyX2OqC_fbnQikm0wurGSwizi1sHPx1Ozv352rRL5lgHrFGZr-xTYKF6bB6WxsH2jUmoG6utnV60aDcEaAnGMCIqZw2GA4U-8MV3706CbV4CW0WqHGxUI-HcVuZoOuBQg5EZGTgAYg_B_7_gbdddJRGtbjw56THLjdbF3qDAe5BE30opP6xiRl9XM5H23RSYx0cEA0YmZJ678D_SDV037-C_p6tgvusDzdtG1ZleCecSA';
$decoded = JWT::decode($jwt, $publicKey, array($header['alg'])); //以公鑰驗證 ， 驗證成功則回傳內容

$decoded_array = (array) $decoded;
echo "<br>" . "payload:" . "<br>" . print_r($decoded_array, true) . "<br>"; //payload內容

?>