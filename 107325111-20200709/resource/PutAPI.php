<?php
function PutAPI($url,$targetId,$PutFile){
	//$url = 'http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest/'; 
	//$targetId = '119';
	/*$PutFile = array(
	"resourceType"=>"MedicationRequest",
	"id" => $targetId,
	"identifier" => array(
			"type" => array(
				"text" => "99"
			)
		 )
	);*/
 
	$header = array();
	$header[] = 'Content-Type:text/json';

	$curl = curl_init(); //初始化
	curl_setopt($curl, CURLOPT_URL, $url.$targetId); //設定url並加上修改目標id
	curl_setopt ($curl, CURLOPT_HTTPHEADER, $header); //設定編碼
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,1); //設定curl_exec獲取的資訊的返回方式 
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST,"PUT"); //設定傳送方式為put請求
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($PutFile)); //設定put的資料


	$result = curl_exec($curl);
	if($result === false){
		echo curl_errno($curl);
		exit();
	}
	print_r($result);
	curl_close($curl);
}
?>