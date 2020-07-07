<?php

/*
<<主要流程>>
1. 取得私鑰
2. 取得resource地址 (參數設定為url)
3. 製作payload
4. 以payload和私鑰導入官方模組，產生token
5. 將token與公鑰加入header.cookie (設定目標網域為resource網域) (參數設定為AuthorizationJwt、AuthorizationPubkey)
6. 跳轉至resource 
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
$publicKey = openssl_pkey_get_public(file_get_contents($keypath)); //開啟公鑰文件

$url = $_GET['url'];
$PortalUrl = "https://203.64.84.229:8011/FHIR_PHR/portal.html";//之後改成由讀取外部文件

$MedicationRequest = array(
    "domain" => "https://203.64.84.229:8022",
    "resource" => "https://203.64.84.229:8022/resource.html"
    );//之後改成由讀取外部文件

$consent = array(
	"resourceType" => "Consent",
    "identifier" => array(
		"type" => array(
			"text" => "22"
		)
	 ),
    "patient" => array(
        "reference" => "Patient/10"
    ),
    "performer" => array(
        "reference" => "Patient/10"
    ),
    "provision" => array(
        "actor" => array(
            "reference" => "Practitioner/11"
        ),
        "action" => array(
            "text" => "GET"
        ),
        "securityLabel" => array(
            "display" => array("GET","GET","POST"),
            "system" => array("http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest?patient=10&authoredon=gt2020-04-12",
			"http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/Patient?organization=MITW",
			"http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest?patient=10"),
			"text" =>  array("","","") //授權對應的網頁
			//(portal.html)流程：登入portal > 選擇病人 > 取得已被授權的功能[列表] > [返回鍵]回portal > 列出resource清單 > [按鍵]選取resource連結 > 抓取授權清單，取得token > 將token附加於resource連結後前往resource 
			//(medicationRequest.html) ： 
				//包含網頁：顯示病人處方列表[get]、新增病人處方[post]、更新處方[put]、刪除處方[delete]

			//Resource 取得處方流程 -> 上傳流程 

			//(portal.html)流程：登入portal > 選擇病人> 病人藥物資訊儲存網頁(patient.html) > 列出被授權的功能列表 > [按鍵]選取功能(resource連結) > 於portal取得token > (resurceserver)呼叫 FHIR API 
        ),
        "data" => array(
            "meaning" => "lt2020-05-25"
        )
    )
    );//之後改成由讀取外部文件

$payload = array(
	 "iss"=>"https://203.64.84.229:8011/FHIR_PHR/portal.html",
	 "aud"=>"https://203.64.84.229:8011/FHIR_PHR/",
     "iat"=> 0,
	 "ndf"=> 0,
	 "exp"=> 0,
	 "typ" => "jwt",
	 "name" => "Jetty",
	 "sub"=>"1",
     "roles"=>"1",
     "PurposeOfUse"=>"medicationrequest",
	 "scope"=>array()
); //基礎模板
//之後改成由讀取外部文件

$jwt = JWT::encode($payload, $privateKey, 'RS256'); //以私鑰章,生成token

$ResourceDomain = '203.64.84.229:8022'; 

$payload["iss"] = $PortalUrl;

$domain = explode('/',$url);
$payload["aud"] = $domain[2]; 

$actor = explode("/",$consent['provision']['actor']['reference']); //將被授權者身分與id分開  e.g. Practitioner/11
$payload["sub"] = $actor[1];
$payload["roles"] = $actor[0];

$payload["iat"] = time();
$payload["ndf"] = time();
$payload["exp"] = time()+ 300000;

//$payload["scope"]["method"] = $consent['provision']['securityLabel']['display'][0];
//$payload["scope"]["url"] = $consent['provision']['securityLabel']['system'][0];

$arrlen = count($consent['provision']['securityLabel']['display']);
for ($i=0;$i<$arrlen;$i++)
{
	$payload["scope"]['method'][$i]= $consent['provision']['securityLabel']['display'][$i];
	$payload["scope"]['url'][$i]= $consent['provision']['securityLabel']['system'][$i];
}

$AuthorizationToken = JWT::encode($payload, $privateKey, 'RS256'); //以私鑰章,生成token

setcookie("AuthorizationJwt",$AuthorizationToken,time()+3600,'/'); //存到portal client
setcookie("Url",$url,time()+3600,'/'); 

header("Authorization: ".$AuthorizationToken); //將token放在header傳送
header("Url: ".$url); 

//header("Location: ".$url);  //前往resource
header('content-type:application/json;charset=utf8');
echo $AuthorizationToken;
?>