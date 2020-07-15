<?php
require_once("HTTPStatusCode.php");

function GetAPI($url){

	//嘗試以get向FHIR Server取得資訊，如果API格式有誤，或是該資料不存在或是未開放存取，那獲取資訊將會失敗。
	//以curl 來get API內容
	//$url = "http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest/117";
	$curl = curl_init();
 
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($curl); //取得回傳值 
	$info = curl_getinfo($curl); //向FHIRserver取值後結果，內含http狀態碼
 
	$result_A = json_decode($result,true);

	if(HttpStatusCode($info['http_code'])!=0)
	{
		//echo '驗證API成功';
		//echo json_encode($result_A['entry'][0]['resource']);
		if($result_A['total']!='0'){
			return json_encode($result_A['entry']);
		}else{
			return false;
		}
	}
	
}
//$response = json_decode($response);//轉為JSON格式
//$response = new SimpleXMLElement($response);//轉為XML
?> 