<?php
$url = "../component/db_properties.json";
$json = file_get_contents($url);
$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
$arr = json_decode($json,true);
$result = [];

if(isset($_GET['action'])){
	$action = $_GET['action'];
	$params = json_decode($_POST['param'],true);
	
	switch($action){
		case 'get_product_list':
			foreach($params['select_products'] as $p){
				foreach($arr['api_product_list'] as $ar){
          if($p['product_cd'] === $ar['product_cd']){
            array_push($result,$ar);	
          }     					
				}   
			}
			break;
	}
	
}

echo json_encode($result);
exit;