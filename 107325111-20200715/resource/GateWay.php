<?php

require_once 'D:www/vendor/firebase/php-jwt/src/BeforeValidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/ExpiredException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/SignatureInvalidException.php';
require_once 'D:www/vendor/firebase/php-jwt/src/JWT.php';

require_once("GetAPI.php");
require_once("PostAPI.php");
require_once("PutAPI.php");
require_once("DeleteAPI.php");

use \Firebase\JWT\JWT; //導入官方模組

$post_data = $_POST;
$header = get_all_headers(); //取得header 內容
$ret = array();
$ret['get'] = $post_data;
$ret['header'] = $header;
//$PurposeOfUse = $_GET['PurposeOfUse'];

header('content-type:application/json;charset=utf8');

//echo json_encode($ret, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
/*
echo "\n"."AuthorizationJwt:"."\n";
echo $_COOKIE['AuthorizationJwt'];
echo "\n"."AuthorizationPubkey:"."\n";
echo $_COOKIE['AuthorizationPubkey'];
*/

//token檢驗

/*
檢驗流程：
1. 帶入公鑰驗證正確性(包含iat、ndf、exp時間驗證)(jwt.io套件內建內容)
2. 以$_SERVER['HTTP_REFERER'] 檢驗iss驗證token來源
3. 以$_SERVER['HTTP_HOST'] 驗證resource網域
4. 驗證API是否合法
*/	
	$header = get_all_headers();
	$authorizationToken = $header['authorization']; //token內容
	//$authorizationToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvMjAzLjY0Ljg0LjIyOTo4MDExXC9GSElSX1BIUlwvcG9ydGFsLmh0bWwiLCJhdWQiOiIyMDMuNjQuODQuMjI5OjgwMjIiLCJpYXQiOjE1OTMyMjY2NDEsIm5kZiI6MTU5MzIyNjY0MSwiZXhwIjoxNTkzNTI2NjQxLCJ0eXAiOiJqd3QiLCJuYW1lIjoiSmVycnkiLCJzdWIiOiIxMSIsInJvbGVzIjoiUHJhY3RpdGlvbmVyIiwiUHVycG9zZU9mVXNlIjoiR2V0TWVkaWNhdGlvblJlcXVlc3QiLCJzY29wZSI6eyJtZXRob2QiOlsiR0VUIl0sInVybCI6WyJodHRwOlwvXC8yMDMuNjQuODQuMjEzOjgwODBcL2hhcGktZmhpci1qcGFzZXJ2ZXJcL2ZoaXJcL01lZGljYXRpb25SZXF1ZXN0P3BhdGllbnQ9MTM5Il19fQ.Ksdsld-4G2y97uGriP2YJ-pDX9HNDasgsDk1tRYxtQKEaXd238Ai648xfxEhCjnJwauzPTKz2XAKJQMeZCdpcP8POk5Moek7h_cVbGBDgvsCxg0lds9TxPVt_Z6NOgjdP6GxhoGqapCXsVFvTi-5mY4kyv98aR7X01-derGnScak5wsdT3eCagYARQPW2bGPoNpiHL6QQHFZN8TA5RuzChkSwQEsdHHxXmfLYEOAWhk_Ouf0X0VldG8eEkypg5XvLzRiG7sTv4D2xJxAFKwWw0eYKHoNozty5rs3uBWZrhtHogGNfWeie6tWCTyVyW6derIn5u1ybmncNRNwql6brg';
	//$token = $_GET['authorizationToken'];
	//$scope = $_GET['scope'];

	$keypath = "./serverptx/pubkey.key"; //公鑰文件路径

	//$token = $_COOKIE['AuthorizationJwt']; //取得token
	$jwt = explode(".",$authorizationToken); //分段
	$header = json_decode(JWT::urlsafeB64Decode($jwt[0]),true);
	$payload = json_decode(JWT::urlsafeB64Decode($jwt[1]),true); //以JWT.io涵式取得payload內容
	//$priv_key = openssl_pkey_get_public($_COOKIE['AuthorizationPubkey']); //取得公鑰 改成存在resource
	$portal_publick_key = openssl_pkey_get_public(file_get_contents($keypath)); //開啟公鑰文件

try{
	$decoded = JWT::decode($authorizationToken, $portal_publick_key, array($header['alg'])); //以公鑰驗證，驗證成功則回傳內容，驗證內容中已經包驗證payload內時間內容 iat、exp、ndf
	$decoded_array = (array) $decoded;
	//echo "\n" ."<<公鑰驗證成功>>". "\n" . "payload:" . "\n" . print_r($decoded_array, true) . "\n"; //payload內容
			//echo "<<驗證resource網域>>";
			if($payload['aud'] == $_SERVER['HTTP_HOST']){ //驗證resource網域
				//echo "\n";
				//echo "Resource網域驗證成功";
				/*
				進行API使用驗證 ， client需要取得、上傳資料時，再進行API內容驗證、比較
				分為 GET、POST、PUT、DELETE三種 
				*/
				switch (strtoupper($_SERVER['REQUEST_METHOD'])) { //判斷方法
					case 'GET':
						$scope_resourcetype; //儲存token_payload_scope的resourcetype項目
						$scope_parameters; //儲存token_payload_scope的參數內容
						$request_resourcetype; //儲存request_url的resourcetype項目
						$request_parameters; //儲存request_url的參數內容
						
						//字串處理request api，分類為resourcetype與parameters內容
						$request_parameters_string=explode("&",$_SERVER['QUERY_STRING']);  //取得傳入的RESTful_API
						for ($i=0;$i<sizeof($request_parameters_string);$i++){
							$request_parameters[$i] = explode("=",$request_parameters_string[$i]);
						}
						$request_resourcetype = $request_parameters['0']['1']; 

						/*--
						加入resourcetype方法判斷：
						不同resourcetype之下parameters對應的json內容格式不同
						--*/

						for ($i=0;$i<sizeof($payload['scope']);$i++){
							if($payload['scope'][$i]['method'] == 'GET'){ //比對payload.scope.method方法
								for ($j=0;$j<sizeof($payload['scope'][$i]['url']);$j++){
									$payload_scope_url= explode("/",$payload['scope'][$i]['url'][$j]); //字串處理以'/'分割
									$scope_url_array = explode("?",$payload_scope_url['5']); //去掉resource server網址
									$scope_resourcetype = $scope_url_array['0']; //取得scope.resourcetype

									if($scope_resourcetype == $request_resourcetype){ //比對resourceType
										$scope_parameters = explode("&",$scope_url_array['1']); //取得scope.parameters
										for($p=0;$p<sizeof($scope_parameters);$p++){
											$scope_parameters[$p] = explode("=",$scope_parameters[$p]);
										} //取得scope.parameters

										$request_parameters_length = sizeof($scope_parameters); //parameters數量
										$checkOn_parameters = 0;
										for($z=1;$z<sizeof($request_parameters);$z++){ //檢查參數範圍
											for($x=0;$x<sizeof($scope_parameters);$x++){
												if($request_parameters[$z]['0'] == 'date' && $scope_parameters[$x]['0'] == 'date'){		
													//根據時間參數[gt、lt]檢查時間前後
													if(substr($scope_parameters[$x]['1'],0,2) == 'gt' && substr($request_parameters[$z]['1'],0,2) == 'gt'){ 
														if(strtotime(substr($scope_parameters[$x]['1'],2))<=strtotime(substr($request_parameters[$z]['1'],2))){
															$checkOn_parameters++;
														}
													}
													else if(substr($scope_parameters[$x]['1'],0,2) == 'lt' && substr($request_parameters[$z]['1'],0,2) == 'lt'){
														if(strtotime(substr($scope_parameters[$x]['1'],0,2))>=strtotime(substr($request_parameters[$z]['1'],0,2))){
															$checkOn_parameters++;
														}
													}
												}else if($request_parameters[$z]['0'] == 'effective-time' && $scope_parameters[$x]['0'] == 'effective-time'){		
													//根據時間參數[gt、lt]檢查時間前後
													if(substr($scope_parameters[$x]['1'],0,2) == 'gt' && substr($request_parameters[$z]['1'],0,2) == 'gt'){ 
														if(strtotime(substr($scope_parameters[$x]['1'],2))<=strtotime(substr($request_parameters[$z]['1'],2))){
															$checkOn_parameters++;
														}
													}
													else if(substr($scope_parameters[$x]['1'],0,2) == 'lt' && substr($request_parameters[$z]['1'],0,2) == 'lt'){
														if(strtotime(substr($scope_parameters[$x]['1'],0,2))>=strtotime(substr($request_parameters[$z]['1'],0,2))){
															$checkOn_parameters++;
														}
													}
												}else if($request_parameters[$z]['0'] == $scope_parameters[$x]['0'] && $request_parameters[$z]['1'] == $scope_parameters[$x]['1']){ 
													$checkOn_parameters++;
												}
											}
										}
										if($checkOn_parameters == (sizeof($scope_parameters))){ //必須要完成與scope內容相同的授權內容檢查 (date需包含於範圍內)
											$response_array = GetAPI($payload['scope'][$i]['url'][$j]);
											print_r($response_array); //檢查完向FHIR-server取得資料response回client
										}
									}
								}
							}
						}
						//GetAPI($payload['scope']['0']['url']['1']);
						break;
					case 'POST':
						if(isset($_POST['json'])){
							$scope_resourcetype; //儲存token_payload_scope的resourcetype項目
							$scope_parameters; //儲存token_payload_scope的參數內容
							$request_resourcetype; //儲存request_url的resourcetype項目
							$request_parameters; //儲存request_url的參數內容

							$json = $_POST['json']; 
							$json_file = json_decode($json, true); //取得輸入的json數據

							$request_parameters_string=explode("&",$_SERVER['QUERY_STRING']);  //取得傳入的RESTful_API
							for ($i=0;$i<sizeof($request_parameters_string);$i++){
								$request_parameters[$i] = explode("=",$request_parameters_string[$i]);
							}
							$request_resourcetype = $request_parameters['0']['1']; //儲存request_url的resourcetype
							
							for ($i=0;$i<sizeof($payload['scope']);$i++){
								if($payload['scope'][$i]['method'] == 'POST'){ //比對payload.scope.method方法
									for ($j=0;$j<sizeof($payload['scope'][$i]['url']);$j++){
										$payload_scope_url= explode("/",$payload['scope'][$i]['url'][$j]);
										$scope_url_array = explode("?",$payload_scope_url['5']); //去掉resource server網址，取scope.payload.url 的 resourceType和授權服務範圍									
										$scope_resourcetype = $scope_url_array['0']; //取得scope resourcetype
										$scope_parameters = explode("&",$scope_url_array['1']);
										for($p=0;$p<sizeof($scope_parameters);$p++){
											$scope_parameters[$p] = explode("=",$scope_parameters[$p]);
										} //取得scope.parameters
										
										if($scope_resourcetype == $request_resourcetype && $scope_resourcetype = $json_file['resourceType']){ //比對scope 與 json 的 resourceType
											
											//不同resourceType對應不同檢查
											switch ($scope_resourcetype) {
												case 'MedicationRequest':
													$checkOn_parameters = 0;
													for($z=0;$z<sizeof($scope_parameters);$z++){
														switch ($scope_parameters[$z]['0']) {
															case 'date' :
																if(substr($scope_parameters[$z]['1'],0,2) == 'gt'){ 
																	if(strtotime(substr($scope_parameters[$z]['1'],2))<=strtotime(substr($json_file['dosageInstruction']['0']['timing']['event']['0'],2))){
																		$checkOn_parameters++;
																	}
																}else if(substr($scope_parameters[$z]['1'],0,2) == 'lt'){
																	if(strtotime(substr($scope_parameters[$z]['1'],0,2))>=strtotime(substr($json_file['dosageInstruction']['0']['timing']['event']['0'],2))){
																		$checkOn_parameters++;
																	}
																}
																break;
															case 'patient' :
																//檢查scope 病人內容(id是否一樣)、date是否在scope規定的範圍內
																if($json_file['subject']['reference'] == 'Patient/'.$scope_parameters[$z]['1']){
																	$checkOn_parameters++;
																}
																break;
															case 'requester' :
																if($json_file['requester']['reference'] == 'Practitioner/'.$scope_parameters[$z]['1']){
																	$checkOn_parameters++;
																}
																break;
															default :
																break;
														}
													}
													if($checkOn_parameters == (sizeof($scope_parameters))){
														//PostAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/'.$json_file['resourceType'],$json_file);
														//當前socpe參數驗證結束，上傳fhir
														echo $json_file['subject']['reference'];
													}else{
														print_r($checkOn_parameters);
														print_r(sizeof($scope_parameters));
														print_r($scope_parameters);
													}
													break;
												case 'MedicationAdministration':
													$checkOn_parameters = 0;
													for($z=0;$z<sizeof($scope_parameters);$z++){
														switch ($scope_parameters[$z]['0']) {
															case 'effective-DateTime' :
																if(substr($scope_parameters[$z]['1'],0,2) == 'gt'){ 
																	if(strtotime(substr($scope_parameters[$z]['1'],2))<=strtotime(substr($json_file['effectiveDateTime'],2))){
																		$checkOn_parameters++;
																	}
																}else if(substr($scope_parameters[$z]['1'],0,2) == 'lt'){
																	if(strtotime(substr($scope_parameters[$z]['1'],0,2))>=strtotime(substr($json_file['effectiveDateTime'],2))){
																		$checkOn_parameters++;
																	}
																}
																$checkOn_parameters++;
																break;
															case 'patient' :
																//檢查scope 病人內容(id是否一樣)、date是否在scope規定的範圍內
																if('Patient/'.$scope_parameters[$z]['1'] == $json_file['subject']['reference']){
																	$checkOn_parameters++;
																}
																break;
															default :
																break;
														}
													}
													if($checkOn_parameters == (sizeof($scope_parameters))){
														//PostAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/'.$json_file['resourceType'],$json_file);
														//當前socpe參數驗證結束，上傳fhir
														echo $json_file['subject']['reference'];
													}
													break;
												case 'Observation':
													$checkOn_parameters = 0;
													for($z=0;$z<sizeof($scope_parameters);$z++){
														switch ($scope_parameters[$z]['0']) {
															case 'date' :
																if(substr($scope_parameters[$z]['1'],0,2) == 'gt'){ 
																	if(strtotime(substr($scope_parameters[$z]['1'],2))<=strtotime(substr($json_file['effectiveDateTime'],2))){
																		$checkOn_parameters++;
																	}
																}else if(substr($scope_parameters[$z]['1'],0,2) == 'lt'){
																	if(strtotime(substr($scope_parameters[$z]['1'],0,2))>=strtotime(substr($json_file['effectiveDateTime'],2))){
																		$checkOn_parameters++;
																	}
																}
																$checkOn_parameters++;
																break;
															case 'patient' :
																//檢查scope 病人內容(id是否一樣)、date是否在scope規定的範圍內
																if($json_file['subject']['reference'] == 'Patient/'.$scope_parameters[$z]['1']){
																	$checkOn_parameters++;
																}
																break;
															default :
																break;
														}
													}
													if($checkOn_parameters == (sizeof($scope_parameters))){
														//PostAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/'.$json_file['resourceType'],$json_file);
														//當前socpe參數驗證結束，上傳fhir
														echo $json_file['subject']['reference'];
													}
													break;
											}
										}
										
									}
								}
							}				
						}else{
							echo 'need file';
						}	
						break;
					case 'DELETE':
						if(isset($_GET['requestAPI'])){
							$request_resourcetype = $_GET['requestAPI'];
						}
						if(isset($_GET['id'])){
							$request_id = $_GET['id'];
						} //當前指定刪除的FHIR文件ID

						for ($i=0;$i<sizeof($payload['scope']);$i++){
							if($payload['scope'][$i]['method'] == 'DELETE'){
								for ($j=0;$j<sizeof($payload['scope'][$i]['url']);$j++){
									$payload_scope_url= explode("/",$payload['scope'][$i]['url'][$j]);
									$scope_url_array = explode("?",$payload_scope_url['5']); //去掉resource server網址，取scope.payload.url 的 resourceType和授權服務範圍									
									$scope_resourcetype = $scope_url_array['0']; //取得scope resourcetype

									$scope_parameters = explode("&",$scope_url_array['1']);
									for($p=0;$p<sizeof($scope_parameters);$p++){
										$scope_parameters[$p] = explode("=",$scope_parameters[$p]);
									} //取得scope.parameters

									$response_array = json_decode(GetAPI($payload['scope'][$i]['url'][$j]),true); //以DELETE授權內容的URL，向FHIR GET資源 //可能為多筆
									for($k=0;$k<sizeof($response_array);$k++){
										$response = $response_array[$k]['resource'];
										if($response['id'] == $request_id){ //檢驗資源ID，是否與請求相同，驗證當前文件是否存在
											if($request_resourcetype == $scope_resourcetype){ //檢驗當前resourcetype是否相同
											/*----------------
											根據不同的resourceType對刪除的FHIR文件檢查，確定此文件在授權範圍內
											----------------*/
												switch ($scope_resourcetype) {
													case 'MedicationRequest':
														$checkOn_parameters = 0;
														for($z=0;$z<sizeof($scope_parameters);$z++){
															switch ($scope_parameters[$z]['0']) {
																case 'date' :
																	if(substr($scope_parameters[$z]['1'],0,2) == 'gt'){ 
																		if(strtotime(substr($scope_parameters[$z]['1'],2))<=strtotime(substr($response['dosageInstruction']['0']['timing']['event']['0'],2))){
																			$checkOn_parameters++;
																		}
																	}else if(substr($scope_parameters[$z]['1'],0,2) == 'lt'){
																		if(strtotime(substr($scope_parameters[$z]['1'],0,2))>=strtotime(substr($response['dosageInstruction']['0']['timing']['event']['0'],2))){
																			$checkOn_parameters++;
																		}
																	}
																	break;
																case 'patient' :
																	//檢查scope 病人內容(id是否一樣)、date是否在scope規定的範圍內
																	if($response['subject']['reference'] == 'Patient/'.$scope_parameters[$z]['1']){
																		$checkOn_parameters++;
																	}
																	break;
																case 'requester' :
																	if($response['requester']['reference'] == 'Practitioner/'.$scope_parameters[$z]['1']){
																		$checkOn_parameters++;
																	}
																	break;
																default :
																	break;
															}
														}
														if($checkOn_parameters == (sizeof($scope_parameters))){
															//DeleteAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/'.$request_resourcetype.'/',$request_id);
															echo '<DELETE>'.$request_resourcetype.'/'.$request_id;
															//當前socpe參數驗證結束，刪除此FHIR文件
														}else{
															print_r($checkOn_parameters);
															print_r(sizeof($scope_parameters));
															print_r($scope_parameters);
															print_r($response);
														}
														break;
													case 'MedicationAdministration':
														$checkOn_parameters = 0;
														for($z=0;$z<sizeof($scope_parameters);$z++){
															switch ($scope_parameters[$z]['0']) {
																case 'effective-DateTime' :
																	if(substr($scope_parameters[$z]['1'],0,2) == 'gt'){ 
																		if(strtotime(substr($scope_parameters[$z]['1'],2))<=strtotime(substr($response['effectiveDateTime'],2))){
																			$checkOn_parameters++;
																		}
																	}else if(substr($scope_parameters[$z]['1'],0,2) == 'lt'){
																		if(strtotime(substr($scope_parameters[$z]['1'],0,2))>=strtotime(substr($response['effectiveDateTime'],2))){
																			$checkOn_parameters++;
																		}
																	}
																	$checkOn_parameters++;
																	break;
																case 'patient' :
																	//檢查scope 病人內容(id是否一樣)、date是否在scope規定的範圍內
																	if('Patient/'.$scope_parameters[$z]['1'] == $response['subject']['reference']){
																		$checkOn_parameters++;
																	}
																	break;
																default :
																	break;
															}
														}
														if($checkOn_parameters == (sizeof($scope_parameters))){
															//DeleteAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/'.$request_resourcetype.'/',$request_id);
															echo '<DELETE>'.$request_resourcetype.'/'.$request_id;
															//當前socpe參數驗證結束，刪除此FHIR文件
														}
														break;
													case 'Observation':
														$checkOn_parameters = 0;
														for($z=0;$z<sizeof($scope_parameters);$z++){
															switch ($scope_parameters[$z]['0']) {
																case 'date' :
																	if(substr($scope_parameters[$z]['1'],0,2) == 'gt'){ 
																		if(strtotime(substr($scope_parameters[$z]['1'],2))<=strtotime(substr($response['effectiveDateTime'],2))){
																			$checkOn_parameters++;
																		}
																	}else if(substr($scope_parameters[$z]['1'],0,2) == 'lt'){
																		if(strtotime(substr($scope_parameters[$z]['1'],0,2))>=strtotime(substr($response['effectiveDateTime'],2))){
																			$checkOn_parameters++;
																		}
																	}
																	$checkOn_parameters++;
																	break;
																case 'patient' :
																	//檢查scope 病人內容(id是否一樣)、date是否在scope規定的範圍內
																	if($response['subject']['reference'] == 'Patient/'.$scope_parameters[$z]['1']){
																		$checkOn_parameters++;
																	}
																	break;
																default :
																	break;
															}
														}
														if($checkOn_parameters == (sizeof($scope_parameters))){
															//DeleteAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/'.$request_resourcetype.'/',$request_id);
															echo '<DELETE>'.$request_resourcetype.'/'.$request_id;
															//當前socpe參數驗證結束，刪除此FHIR文件
														}
														break;
												}
											}
										}
									}
								}
							}
						}
						break;
					case 'PUT': //JSON需要指定文件ID
						if(isset($_GET['requestAPI'])){
							$request_resourcetype = $_GET['requestAPI'];
						}
						if(isset($_GET['id'])){
							$request_id = $_GET['id'];
						}
						parse_str(file_get_contents('php://input'), $json);
						$json_file = json_decode($json['json'], true); //取得輸入的json數據
	
						for ($i=0;$i<sizeof($payload['scope']);$i++){
							if($payload['scope'][$i]['method'] == 'PUT'){
								for ($j=0;$j<sizeof($payload['scope'][$i]['url']);$j++){
									$payload_scope_url= explode("/",$payload['scope'][$i]['url'][$j]);
									$scope_url_array = explode("?",$payload_scope_url['5']); //去掉resource server網址，取scope.payload.url 的 resourceType和授權服務範圍									
									$scope_resourcetype = $scope_url_array['0']; //取得scope resourcetype

									$scope_parameters = explode("&",$scope_url_array['1']);
									for($p=0;$p<sizeof($scope_parameters);$p++){
										$scope_parameters[$p] = explode("=",$scope_parameters[$p]);
									} //取得scope.parameters

									$response_array = json_decode(GetAPI($payload['scope'][$i]['url'][$j]),true); //以PUT授權內容的URL，向FHIR GET資源
									for($k=0;$k<sizeof($response_array);$k++){
										$response = $response_array[$k]['resource'];
										if($response['id'] == $request_id){ //檢驗資源ID，是否與請求相同，驗證更新文件是否存在
											if($request_resourcetype == $scope_resourcetype){ //檢驗當前resourcetype是否相同
											/*----------------
											根據不同的resourceType對更新的FHIR文件檢查，確定此文件在授權範圍內
											PUT方法下，需要額外檢查更新的JSON內容，當前只檢查patint項目是否符合授權
											----------------*/
												switch ($scope_resourcetype) {
													case 'MedicationRequest':
														$checkOn_parameters = 0;
														for($z=0;$z<sizeof($scope_parameters);$z++){
															switch ($scope_parameters[$z]['0']) {
																//檢查scope 病人內容(id是否一樣)、date是否在scope規定的範圍內
																case 'date' :
																	if(substr($scope_parameters[$z]['1'],0,2) == 'gt'){ 
																		if(strtotime(substr($scope_parameters[$z]['1'],2))<=strtotime(substr($response['dosageInstruction']['0']['timing']['event']['0'],2))){
																			$checkOn_parameters++;
																		}
																	}else if(substr($scope_parameters[$z]['1'],0,2) == 'lt'){
																		if(strtotime(substr($scope_parameters[$z]['1'],0,2))>=strtotime(substr($response['dosageInstruction']['0']['timing']['event']['0'],2))){
																			$checkOn_parameters++;
																		}
																	}
																	break;
																case 'patient' :
																	if($response['subject']['reference'] == 'Patient/'.$scope_parameters[$z]['1']){ //當前更新的FHIR文件是否符合授權
																		if($json_file['subject']['reference'] == 'Patient/'.$scope_parameters[$z]['1']){ //驗證更新的JSON.patient是否合乎授權
																			$checkOn_parameters++;
																		}
																	}
																	break;
																case 'requester' :
																	if($response['requester']['reference'] == 'Practitioner/'.$scope_parameters[$z]['1']){
																		$checkOn_parameters++;
																	}
																	break;
																default :
																	break;
															}
														}
														if($checkOn_parameters == (sizeof($scope_parameters))){
															//PutAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/'.$request_resourcetype.'/',$request_id,$json_file);
															echo '<PUT>'.$request_resourcetype.'/'.$request_id;
															print_r($json_file);
														}else{
															print_r($checkOn_parameters);
															print_r(sizeof($scope_parameters));
															print_r($scope_parameters);
															print_r($response);
														}
														break;
													case 'MedicationAdministration':
														$checkOn_parameters = 0;
														for($z=0;$z<sizeof($scope_parameters);$z++){
															switch ($scope_parameters[$z]['0']) {
																//檢查scope 病人內容(id是否一樣)、date是否在scope規定的範圍內
																case 'effective-time' :
																	if(substr($scope_parameters[$z]['1'],0,2) == 'gt'){ 
																		if(strtotime(substr($scope_parameters[$z]['1'],2))<=strtotime(substr($response['effectiveDateTime'],2))){
																			$checkOn_parameters++;
																		}
																	}else if(substr($scope_parameters[$z]['1'],0,2) == 'lt'){
																		if(strtotime(substr($scope_parameters[$z]['1'],0,2))>=strtotime(substr($response['effectiveDateTime'],2))){
																			$checkOn_parameters++;
																		}
																	}
																	$checkOn_parameters++;
																	break;
																case 'subject' :
																	if('Patient/'.$scope_parameters[$z]['1'] == $response['subject']['reference']){
																		$checkOn_parameters++;
																	}
																	break;
																default :
																	break;
															}
														}
														if($checkOn_parameters == (sizeof($scope_parameters))){
															//PutAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/'.$request_resourcetype.'/',$request_id,$json_file);
															echo '<PUT>'.$request_resourcetype.'/'.$request_id;
															print_r($json_file);
														}
														break;
													case 'Observation':
														$checkOn_parameters = 0;
														for($z=0;$z<sizeof($scope_parameters);$z++){
															switch ($scope_parameters[$z]['0']) {
																//檢查scope 病人內容(id是否一樣)、date是否在scope規定的範圍內
																case 'date' :
																	if(substr($scope_parameters[$z]['1'],0,2) == 'gt'){ 
																		if(strtotime(substr($scope_parameters[$z]['1'],2))<=strtotime(substr($response['effectiveDateTime'],2))){
																			$checkOn_parameters++;
																		}
																	}else if(substr($scope_parameters[$z]['1'],0,2) == 'lt'){
																		if(strtotime(substr($scope_parameters[$z]['1'],0,2))>=strtotime(substr($response['effectiveDateTime'],2))){
																			$checkOn_parameters++;
																		}
																	}
																	$checkOn_parameters++;
																	break;
																case 'patient' :
																	if($response['subject']['reference'] == 'Patient/'.$scope_parameters[$z]['1']){
																		$checkOn_parameters++;
																	}
																	break;
																default :
																	break;
															}
														}
														if($checkOn_parameters == (sizeof($scope_parameters))){
															//PutAPI('http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/'.$request_resourcetype.'/',$request_id,$json_file);
															echo '<PUT>'.$request_resourcetype.'/'.$request_id;
															print_r($json_file);
															$response_array = GetAPI($payload['scope'][$i]['url'][$j]);
															print_r($response_array); //檢查完向FHIR-server取得資料response回client
														}
														break;
												}
											}
										}
									}
								}
							}
						}
						break;
					default:
						//echo "token未包含授權內容";
						break;
				}
			}else {
				//echo "\n";
				//echo "error：Resource網域驗證失敗  " . $_SERVER['HTTP_HOST'];
			}
}catch(Exception $e){
	//echo "\n" . "<<公鑰驗證失敗>>";
}

/**
* 獲取自定義的header資料
*/
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