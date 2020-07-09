<?php
header('Access-Control-Allow-Origin: *'); //讓chromes能用此API，不會擋憑證
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

$privateKey = openssl_pkey_get_private(file_get_contents($pempath),$privkeypass); //開啟私鑰文件
$publicKey = openssl_pkey_get_public(file_get_contents($keypath)); //開啟公鑰文件

$PortalUrl = "https://203.64.84.229:8011/FHIR_PHR/portal.html";//之後改成由讀取外部文件

$MedicationRequest = array(
    "domain" => "https://203.64.84.229:8022",
    "resource" => "https://203.64.84.229:8022/resource.html"
    );//之後改成由讀取外部文件

$consent = array(
	"resourceType" => "Consent",
    "patient" => array(
        "reference" => "Patient/139"
    ),
    "performer" => array(
        "reference" => "Patient/139"
    ),
    "provision" => array(
		"period" => array(
			"start" => "2020-03-03",
			"end" => "2020-10-20"
		),
        "actor" => array(
            "reference" => array(
				"reference" => "Practitioner/258"
			)
        ),
        "action" => array(
            "0" => array(
				"text" => "GET",
				"coding" => array(
					"0" => array(
						"code" => "http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest?patient=139&date=gt2020-01-01"
					),
					"1" => array(
						"code" => "http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/Patient/139"
					)
				)
			),
			"1" => array(
				"text" => "POST",
				"coding" => array(
					"0" => array(
						"code" => "http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest?patient=139&date=gt2020-01-01"
					)
				)
			),
			"2" => array(
				"text" => "DELETE",
				"coding" => array(
					"0" => array(
						"code" => "http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest?patient=139&date=gt2020-01-01"
					)
				)
			),
			"3" => array(
				"text" => "PUT",
				"coding" => array(
					"0" => array(
						"code" => "http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest?patient=139&date=gt2020-01-01"
					)
				)
			),
        )
    )
);
		/*----------------------
		之後改成由讀取外部文件
		http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/Consent/274
		mothod : provision.action.text
		url : provision.action.coding.code
		----------------------*/

			//(portal.html)流程：登入portal > 選擇病人 > 取得已被授權的功能[列表] > [返回鍵]回portal > 列出resource清單 > [按鍵]選取resource連結 > 抓取授權清單，取得token > 將token附加於resource連結後前往resource 
			//(medicationRequest.html) ： 
				//包含網頁：顯示病人處方列表[get]、新增病人處方[post]、更新處方[put]、刪除處方[delete]

			//Resource 取得處方流程 -> 上傳流程 

			//(portal.html)流程：登入portal > 選擇病人> 病人藥物資訊處理網頁(patient.html) > 列出被授權的功能列表 > [按鍵]選取功能(resource連結) > 於portal取得token > token傳給resource
			//#授權功能列表呈現 (呈現該用戶對於病人可進行的服務網頁 e.g. 藥物處方、影像報告等...) > [選擇藥物處方]傳送認證token給與藥物處方網頁(medicationRequest.htm) > 
			//(resurce server)呼叫 FHIR API  

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
	 "scope"=>array()
); //基礎模板
//之後改成由讀取外部文件

if(isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])=='POST'){ //判斷是否為GET方法
		if(isset($_POST['json'])){
				$json = $_POST['json'];
				$json = json_decode($json, true);
				$authenticationToken = $json['authenication-jwt'];
		}	
	}

//$header = get_all_headers();
//$authenticationToken = $header['authentication-jwt']; //token內容

//$reference = json_decode(base64_decode($_GET['reference']));
//$reference= 'http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest?patient=139';


/*-------------------
編輯payload.scope內容
-------------------*/
$numi=0; //計數器，紀錄method
$numa=0;  //計數器，紀錄url
for ($i=0;$i<sizeof($json['API']);$i++){
	for ($j=0;$j<sizeof($consent['provision']['action']);$j++)
	{
		if($json['API'][$i]['method'] == $consent['provision']['action'][$j]['text']){ //比對method
			for ($a=0;$a<sizeof($json['API'][$i]['url']);$a++){
				for ($b=0;$b<sizeof($consent['provision']['action'][$j]['coding']);$b++){
					if($json['API'][$i]['url'][$a] == $consent['provision']['action'][$j]['coding'][$b]['code']){ //比對url
						$payload["scope"][$numi]['method']= $consent['provision']['action'][$j]['text'];
						$payload["scope"][$numi]['url'][$numa]= $consent['provision']['action'][$j]['coding'][$b]['code'];
						$numa++;
						break;
					}
				}
			}
			$numi++;
			$numa=0;
			break;
		}
	}
}

$jwt = JWT::encode($payload, $privateKey, 'RS256'); //以私鑰章,生成token

$ResourceDomain = '203.64.84.229:8022'; 

$payload["iss"] = $PortalUrl;
//$domain = explode('/',$url);
$payload["aud"] = $ResourceDomain; 

$actor = explode("/",$consent['provision']['actor']['reference']['reference']); //將被授權者身分與id分開  e.g. Practitioner/139
$payload["sub"] = $actor[1];
$payload["roles"] = $actor[0];

$payload["iat"] = time();
$payload["ndf"] = time();
$payload["exp"] = time()+ 300000;

//$payload["scope"]["method"] = $consent['provision']['securityLabel']['display'][0];
//$payload["scope"]["url"] = $consent['provision']['securityLabel']['system'][0];
/*
$arrlen = count($consent['provision']['securityLabel']['display']);
for ($i=0;$i<$arrlen;$i++)
{
	$payload["scope"]['method'][$i]= $consent['provision']['securityLabel']['display'][$i];
	$payload["scope"]['url'][$i]= $consent['provision']['securityLabel']['system'][$i];
}
*/

$AuthorizationToken = JWT::encode($payload, $privateKey, 'RS256'); //以私鑰章,生成token

//setcookie("AuthorizationJwt",$AuthorizationToken,time()+3600,'/'); //存到portal client
//setcookie("Url",$url,time()+3600,'/'); 

header("authorizationToken: ".$AuthorizationToken); //將token放在header傳送
//header("Url: ".$url); 
//header('content-type:application/json;charset=utf8');
//header("Location: ".$url.'?token='.$AuthorizationToken); 

echo $AuthorizationToken;

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