<?php
$url = "../component/db_properties.json";
$json = file_get_contents($url);
$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
$arr = json_decode($json,true);

$action = $_GET['action'];
$results = '';

switch($action){
	case 'estimate_all':
		$results = $arr['estimate'];
		break;
	case 'product_all':
		$results = $arr['products'];
		break;
	case 'get_estimate':
		foreach($arr['estimate'] as $ar){
		 if($ar['id'] === intval($_GET['id'])){
			 $results = $ar;
		 }	
		}
		
		break;
	case 'get_estimate_tie':
		$results = [];
		$tie_ids = [];
		if(isset($_GET['tie_id'])){
			$tie_ids = explode('_',$_GET['tie_id']);
		}
		
		foreach($tie_ids as $tie_id){
			foreach($arr['estimate_tie'] as $et){
				if(intval($tie_id) === intval($et['id'])){
					array_push($results,$et);
				}
			}
		}
		break;		
	case 'get_product_cd':
		$results = [];
		$exp_ids = [];
		$price_id = $_GET['id'];
		if(isset($price_id)){
			$exp_ids = explode('_',$price_id);
		}
		
		foreach($exp_ids as $id){
			foreach($arr['color'] as $color){
				if($color['price_id'] === $id){
					array_push($results,$color);
				}
			}
		}
		
		break;
}

$data['result_select'] = $results; 

echo json_encode($data);
exit;