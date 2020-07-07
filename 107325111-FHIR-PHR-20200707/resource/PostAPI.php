<?php

require_once("HTTPStatusCode.php");

function PostAPI($url,$PostFile){
	//$url = 'http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest';

	/*$PostFile = array(
	"resourceType"=>"MedicationRequest",
	"identifier" => array(
			"type" => array(
				"text" => "22"
			)
		 )
	);*/

	$header = array();
	$header[] = 'Content-Type:text/json';

	$curl = curl_init();  //初始化
	curl_setopt($curl,CURLOPT_URL,$url);  //設定url
	curl_setopt($curl,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);  //設定http驗證方法
	curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);  //設定curl_exec獲取的資訊的返回方式
	curl_setopt($curl,CURLOPT_POST,1);  //設定傳送方式為post請求
	curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($PostFile));  //設定post的資料

	$result = curl_exec($curl);
	$info = curl_getinfo($curl); //執行後結果，內含http狀態碼
	$info['http_code'];

	if($result === false){
		echo curl_errno($curl);
		exit();
	}
	print_r($result);
	curl_close($curl);
}
?>