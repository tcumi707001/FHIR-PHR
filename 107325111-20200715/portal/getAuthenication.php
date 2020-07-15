<?php
header('Access-Control-Allow-Origin: *'); //讓chromes能用此API，不會擋憑證
/*
<<主要流程>>
1. 取得私鑰
2. 以用戶帳號(username)查詢Person.identifier
3. 製作payload
4. 以payload和私鑰導入官方模組，產生token
5. 將token與公鑰加入header.cookie (設定目標網域為resource網域) (參數設定為AuthorizationJwt、AuthorizationPubkey)
6. 跳轉至resource 
*/

require_once 'D:www/vendor/firebase/php-jwt/src/BeforeValidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/ExpiredException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/SignatureInvalidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/JWT.php';
require_once("GetAPI.php");

use \Firebase\JWT\JWT; //導入官方模組

$privkeypass = '111111'; //私鑰密碼  
$numberofdays = 365;     //有效時間(日) 
$csrpath = "./serverptx/server.pem"; //伺服器證書
$cerpath = "./serverptx/server.cer"; //證書路徑 
$pfxpath = "./serverptx/server.pfx"; //證書存儲文件路徑
$pempath = "./serverptx/privkey.pem"; //私鑰文件路徑
$keypath = "./serverptx/pubkey.key"; //公鑰文件路径

$privateKey = openssl_pkey_get_private(file_get_contents($pempath),$privkeypass); //開啟私鑰文件
$publicKey = openssl_pkey_get_public(file_get_contents($keypath)); //開啟公鑰文件

$PortalUrl = "https://203.64.84.229:8011/FHIR_PHR/portal.html";

$username = $_GET['username'];
$password = $_GET['password'];

$payload = array(
	 "iss"=>"https://203.64.84.229:8011/FHIR_PHR/portal.html",
	 "aud"=>"203.64.84.229:8011",
     "iat"=> 0,
	 "ndf"=> 0,
	 "exp"=> 0,
	 "typ" => "jwt",
	 "name" => "Kevin",
	 "sub"=>"1",
     "roles"=>"1",
     "PurposeOfUse"=>"medicationrequest",
); //基礎模板
//之後改成由讀取外部文件

/*-------------------
查詢FHIR.Person
驗證帳號密碼
-------------------*/
try{
	$userdata_array = json_decode(GetAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/Person?identifier='.$username),true);
	//以帳戶查詢Person.identifier儲存的帳戶內容

	for($i=0;$i<sizeof($userdata_array);$i++){
		$userdata = $userdata_array[$i]['resource'];
		for($j=0;$j<sizeof($userdata['identifier']);$j++){
			if($userdata['identifier'][$j]['system']=='password' && $userdata['identifier'][$j]['value'] == $password){
				//echo '帳密驗證成功';
				/*-------------------
				根據取得的帳戶資料(Person)
				編輯payload
				-------------------*/
				$ResourceDomain = '203.64.84.229:8011'; 

				$payload["iss"] = $PortalUrl;
				//$domain = explode('/',$url);
				$payload["aud"] = $ResourceDomain; 

				$actor = explode("/",$userdata['link']['0']['target']['reference']); //將被授權者身分與id分開  e.g. Practitioner/258
				$payload["sub"] = $actor[1];
				$payload["roles"] = $actor[0];

				$payload["iat"] = time();
				$payload["ndf"] = time();
				$payload["exp"] = time()+ 300000;

				$AuthorizationToken = JWT::encode($payload, $privateKey, 'RS256'); //以私鑰章,生成認證token
				echo $AuthorizationToken;
			}
		}
	}
}catch(Exception $e){
	echo '帳戶驗證失敗';
}

function get_all_headers(){
		$ignore = array('host','accept','content-length','content-type');
		$headers = array();
		foreach($_SERVER as $key=>$value){
			if(substr($key, 0, 5)==='HTTP_'){
				$key = substr($key, 5);
				$key = str_replace('_', ' ', $key);
				$key = str_replace(' ', '-', $key);
				$key = strtolower($key);
				if(!in_array($key, $ignore)){
					$headers[$key] = $value;
				}
			}
		}
		return $headers;
	}
?>