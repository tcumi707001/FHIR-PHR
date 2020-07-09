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

	$curl = curl_init();  //��l��
	curl_setopt($curl,CURLOPT_URL,$url);  //�]�wurl
	curl_setopt($curl,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);  //�]�whttp���Ҥ�k
	curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);  //�]�wcurl_exec�������T����^�覡
	curl_setopt($curl,CURLOPT_POST,1);  //�]�w�ǰe�覡��post�ШD
	curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($PostFile));  //�]�wpost�����

	$result = curl_exec($curl);
	$info = curl_getinfo($curl); //����ᵲ�G�A���thttp���A�X
	$info['http_code'];

	if($result === false){
		echo curl_errno($curl);
		exit();
	}
	print_r($result);
	curl_close($curl);
}
?>