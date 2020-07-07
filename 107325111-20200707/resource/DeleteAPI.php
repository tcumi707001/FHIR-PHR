<?php
require_once("HTTPStatusCode.php");

function DeleteAPI($url,$targetId){
	//$url = 'http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest/'; 
	//$targetId = '139';

	$header = array();
	$header[] = 'Content-Type:text/json';

	$curl = curl_init();
	curl_setopt ($curl,CURLOPT_URL,$url.$targetId); //設定url並加上刪除目標id
	curl_setopt ($curl, CURLOPT_HTTPHEADER, $header); //設定編碼
	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1); //設定curl_exec獲取的資訊的返回方式 
	curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, "DELETE"); //設定傳送方式為delete請求

	$result = curl_exec($curl);
	if($result === false){
		echo curl_errno($curl);
		exit();
	}
	print_r($result);
	curl_close($curl);
}
?>