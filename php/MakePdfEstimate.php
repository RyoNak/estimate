<?php
require_once("PdfMakerEstimate.php");
/*---------------DBから見積もりデータを取得---------------*/
$e_id = $_GET['id'];
$estimate = null;
$ties_id = null;
$estimate_ties = [];

$url = "../component/db_properties.json";
$json = file_get_contents($url);
$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
$arr = json_decode($json,true);

if(!empty($arr)){
	foreach($arr['estimate'] as $es){
		if(intval($es['id']) === intval($e_id)){
			$estimate = $es;
			$ties_id = explode(',',$es['tie_ids']);
		}
	}
}

if(!empty($ties_id)){
	foreach($ties_id as $t){
		foreach($arr['estimate_tie'] as $est){
			if(intval($t) === intval($est['id'])){
				array_push($estimate_ties,$est);
			}
		}
	}
}


/*---------------各値を変数にセット---------------*/


$dateObj = getdate(strtotime($estimate['reg_date']));
$dateStr = $dateObj['year'].'年'.$dateObj['mon'].'月'.$dateObj['mday'].'日';
$is_company = false;
$name = '';

if(strpos($estimate['name'],'御中')){
	$is_company = true;
	$name = str_replace('御中','',$estimate['name']);
}else{
	$name = str_replace('様','',$estimate['name']);
}

$fileTitle = '御見積書';
$title = $estimate['title'];
$estimate_number = $estimate['id'];
$delivery = $estimate['delivery'];
$term = $estimate['terms'];
$address = $estimate['address'];
$deadline = $estimate['deadline'];
$ev_name = $estimate['m_name'];
$ev_mail = $estimate['m_mail'];


$subtotal_price = 0;
$tax_rate = 1.1;
$total_price = 0;


foreach($estimate_ties as $et){
	$price = $et['unit_price'] * $et['quantity'];
	$subtotal_price += $price;
}

$total_price = $subtotal_price * $tax_rate;



/*------------------------PDF設定------------------------*/
$pdf = new PdfMakerEstimate();
//トップコンテンツ出力
$pdf->makeTopContents($fileTitle,$title,$estimate_number,$dateStr,$name,$is_company,$delivery,$term,$address,$deadline,$total_price,$ev_name,$ev_mail);
// ヘッダー出力
$pdf->makeTableHeader();
// 製品データ出力
$pdf->makeProductRow($estimate_ties);
//データ数が20未満の場合、空の行を挿入
if(count($estimate_ties) < 20){
	$pdf->addBlankRow(count($estimate_ties));
}
/*表を閉じる*/
$pdf->writeBoldBorder();
/*ボトムコンテンツ出力*/
$pdf->makeBottomContents($subtotal_price,$tax_rate,$estimate['outline']);
/*ページネーション出力*/
$pdf->writePagination();
//ブラウザに出力
$pdf->outputPdf();

