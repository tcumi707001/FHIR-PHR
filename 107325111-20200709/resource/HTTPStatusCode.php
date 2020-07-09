<?php
function HttpStatusCode($info){
	switch ($info) {
		case 200:
			return '1'; //SUCCESS：請求成功
			break;

		case 400:
			return '0'; //ERROR：RESTful API 語法錯誤
			break;

		case 404:
			return '0'; //ERROR：RESTful API 不存在
			break;

		case 408:
			return '0'; //ERROR：請求超時
			break;

		default:
			return '0'; //其他錯誤，參考狀態碼列表https://www.itread01.com/p/1413334.html
			break;
	}
}
?>