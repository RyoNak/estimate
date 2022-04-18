<?php
require_once("../TCPDF/tcpdf.php");

class PdfMakerEstimate {
	private $pdf = null;
	//x軸開始位置
  private $posit_startX = 26;
	//y軸開始位置
  private $posit_startY = 115;
  private $wid_no = 8;
  private $wid_number = 72;
  private $wid_quant = 8;
  private $wid_price = 17;
  private $wid_remarks = 36;
	//表の1行の高さ
  private $baseHeight = 5; 
	//ページ下部限界値
  private $bottomToPage = 275; 
	//小計、消費税、合計の高さ合計値
	private $summarizeHeight = 20;
	//備考の高さ
	private $outlineHeight = 30;
	//X軸の位置
 	private $currentX = [];
	//Y軸の位置
  private $currentY = 0;		

	
	public function __construct(){
		$this->setInitialParam();
    $this->pdf = new TCPDF('P', 'mm', 'A4',true, 'UTF-8',false,false);
    $this->pdf->setPrintHeader(false);
    $this->pdf->setPrintFooter(false);
    $this->pdf->SetMargins(0, 0, 0);
    $this->pdf->SetAutoPageBreak(false);
    $this->pdf->setFont('kozgopromedium','B',14);
    $this->pdf->AddPage('P','A4');		
	}
	/*PDF上部のコンテンツを出力する*/
	public function makeTopContents($fileTitle,$title,$estimate_number,$dateStr,$name,$is_company,$delivery,$term,$address,$deadline,$total_price,$ev_name,$ev_mail){
		
		
    //タイトル
    $this->pdf->Text(95,20,$fileTitle,false);

    //No.
    $this->pdf->setFont('helvetica','',7);
    $this->pdf->Text(152,28,'No.',false);
    $this->pdf->Text(162,28,$estimate_number,false);

    //日付
    $this->pdf->setFont('ipaexg','',7);
    $this->pdf->Text(151,33,'日付',false);
    $this->pdf->Text(162,33,$dateStr,false);

    //顧客名
    $this->pdf->setFont('ipaexg','',8);
    $this->pdf->MultiCell(50.8,5,$name,'B','C',false, 0,33,47,false,0,false,true,0,'T',true);
    if($is_company){
      $this->pdf->MultiCell(10,5,'御中','B','',false, 0,84,47,true,0,false,true,0,'T',true);	
    }else{
      $this->pdf->MultiCell(10,5,'様','B','',false, 0,84,47,true,0,false,true,0,'T',true);
    }


    //案件名
    $this->pdf->setFont('kozgopromedium','B',9);
    $this->pdf->MultiCell(17.8,5,'件名','B','',false,0,26,63,true,0,false,true,0,'T',true);
    $this->pdf->MultiCell(50,5,$title,'B','C',false, 0,44,63,true,0,false,true,0,'T',true);

    //納期
    $this->pdf->setFont('ipaexg','',7);
    $this->pdf->MultiCell(17.8,4,'納期','B','',false, 0,26,79,true,0,false,true,0,'T',true);
    $this->pdf->MultiCell(50,4,$delivery,'B','C',false, 0,44,79,true,0,false,true,0,'T',true);
    //支払い条件
    $this->pdf->MultiCell(17.8,4,'支払条件','B','',false, 0,26,84,true,0,false,true,0,'T',true);
    $this->pdf->MultiCell(50,4,$term,'B','C',false, 0,44,84,true,0,false,true,0,'T',true);
    //納入場所
    $this->pdf->MultiCell(17.8,4,'納入場所','B','',false, 0,26,89,true,0,false,true,0,'T',true);
    $this->pdf->MultiCell(50,4,$address,'B','C',false, 0,44,89,true,0,false,true,0,'T',true);
    //見積もり期限
    $this->pdf->MultiCell(17.8,4,'有効期限','B','',false, 0,26,94,true,0,false,true,0,'T',true);
    $this->pdf->MultiCell(50,4,$deadline,'B','C',false, 0,44,94,true,0,false,true,0,'T',true);


    //合計金額
    $this->pdf->setFont('kozgopromedium','B',9);
    $this->pdf->MultiCell(17.8,6,'合計金額','B','',false, 0,26,104,true,0,false,true,0,'M',true);
    $this->pdf->setFont('helvetica','B',10);
    $this->pdf->MultiCell(33.8,6,number_format($total_price),'B','R',false, 0,44,104,true,0,false,true,0,'M',true);
    $this->pdf->setFont('kozgopromedium','',7);
    $this->pdf->MultiCell(16,6,'円（税込）','B','C',false, 0,78,104,true,0,false,true,0,'M',true);
    $this->pdf->Line(26,109.5,94,109.5);

    //NSS会社情報
    $this->pdf->setFont('ipaexg','',7);
    $this->pdf->Text(128,47,'会社名：',false);
    $this->pdf->Text(142,47,'株式会社NKP　フィットネス事業部',false);
    $this->pdf->Text(142,52,'〒888-9999',false);
    $this->pdf->Text(142,57,'福岡県福岡市博多区博多駅前5-5-5',false);
    $this->pdf->Text(131,70,'TEL：',false);
    $this->pdf->Text(142,70,'999-999-9999',false);
    $this->pdf->Text(131,75,'FAX：',false);
    $this->pdf->Text(142,75,'0900-888-888',false);
    $this->pdf->Text(128,88,'E-mail：',false);
    $this->pdf->Text(142,88,$ev_mail,false);
    $this->pdf->Text(131,101,'担当：',false);
    $this->pdf->Text(142,101,$ev_name,false);		
	}
	/*明細ヘッダーを出力する*/
	public function makeTableHeader(){
    //テーブルヘッダー
    //最上部太枠
    $this->pdf->SetLineWidth(0.4);
    $this->pdf->Line(26,$this->currentY,184,$this->currentY);
    //製品レコード左太枠
    $this->pdf->Line(26,$this->currentY,26,$this->currentY+$this->baseHeight+0.5);
    //製品レコード右太枠
    $this->pdf->Line(184,$this->currentY,184,$this->currentY+$this->baseHeight+0.5);
    //ヘッダー下部ボーダー二重線
    $this->pdf->SetLineWidth(0.2);
    $this->pdf->Line(26,$this->currentY+$this->baseHeight,184,$this->currentY+$this->baseHeight);
    $this->pdf->Line(26,$this->currentY+$this->baseHeight+0.5,184,$this->currentY+$this->baseHeight+0.5);

    //No.
    $this->pdf->SetLineWidth(0.2);
    $this->pdf->MultiCell($this->wid_no,$this->baseHeight,'No.','R','C',false, 0,$this->currentX[0],$this->currentY,true,0,false,true,0,'M',true);
    //製品番号
    $this->pdf->MultiCell($this->wid_number,$this->baseHeight,'製品番号','R','C',false, 0,$this->currentX[1],$this->currentY,true,0,false,true,0,'M',true);
    //数量
    $this->pdf->MultiCell($this->wid_quant,$this->baseHeight,'数量','R','C',false, 0,$this->currentX[2],$this->currentY,true,0,false,true,0,'M',true);
    //単価
    $this->pdf->MultiCell($this->wid_price,$this->baseHeight,'単価','R','C',false, 0,$this->currentX[3],$this->currentY,true,0,false,true,0,'M',true);
    //金額
    $this->pdf->MultiCell($this->wid_price,$this->baseHeight,'金額','R','C',false, 0,$this->currentX[4],$this->currentY,true,0,false,true,0,'M',true);
    //備考
    $this->pdf->MultiCell($this->wid_remarks,$this->baseHeight,'備考','','C',false, 0,$this->currentX[5],$this->currentY,true,0,false,true,0,'M',true);
	}
	/*ループでデータをテーブルに出力する*/
	public function makeProductRow($estimate_ties){

    for($i = 0; $i < count($estimate_ties); $i++){
      
      $order_num = $estimate_ties[$i]['order_num'];
      $item_name = $estimate_ties[$i]['item_name'];
      $quantity = $estimate_ties[$i]['quantity'];
      $unit_price = $estimate_ties[$i]['unit_price']?number_format($estimate_ties[$i]['unit_price']):'';
      $multi_price = $estimate_ties[$i]['unit_price']?number_format($estimate_ties[$i]['unit_price'] * $estimate_ties[$i]['quantity']):'';
      $remarks = $estimate_ties[$i]['remarks'];	


      //30製品ごとに改ページ
      if($i !== 0 && $i % 30 === 0){				
        $this->breakPage();
				$this->makeTableHeader();
      }
			
			$this->posit_startY += $this->baseHeight;
			
      //製品レコード左太枠
      $this->pdf->SetLineWidth(0.4);
      $this->pdf->Line(26,$this->posit_startY,26,$this->posit_startY+$this->baseHeight);
      //製品レコード右太枠
      $this->pdf->Line(184,$this->posit_startY,184,$this->posit_startY+$this->baseHeight);
      //製品レコード出力
      //No.
      $this->pdf->SetLineWidth(0.2);
      $this->pdf->MultiCell($this->wid_no,$this->baseHeight,$order_num,'R','C',false, 0,$this->currentX[0],$this->posit_startY,true,0,false,true,0,'M',true);
      //製品番号
      $this->pdf->MultiCell($this->wid_number,$this->baseHeight,$item_name,'R','L',false, 0,$this->currentX[1],$this->posit_startY,true,0,false,true,0,'M',true);
      //数量
      $this->pdf->MultiCell($this->wid_quant,$this->baseHeight,$quantity,'R','C',false, 0,$this->currentX[2],$this->posit_startY,true,0,false,true,0,'M',true);
      //単価
      $this->pdf->MultiCell($this->wid_price,$this->baseHeight,$unit_price,'R','R',false, 0,$this->currentX[3],$this->posit_startY,true,0,false,true,0,'M',true);
      //金額
      $this->pdf->MultiCell($this->wid_price,$this->baseHeight,$multi_price,'R','R',false, 0,$this->currentX[4],$this->posit_startY,true,0,false,true,0,'M',true);
      //備考
      $this->pdf->MultiCell($this->wid_remarks,$this->baseHeight,$remarks,'','L',false, 0,$this->currentX[5],$this->posit_startY,true,0,false,true,0,'M',true);
      //下線
      $this->pdf->MultiCell(184-26,$this->baseHeight,'','B','',false, 0,$this->currentX[0],$this->posit_startY,true,0,false,true,0,'M',true);
    }		
	}
	/*改ページする*/
	public function breakPage(){
		$this->pdf->AddPage('P','A4');
    $this->posit_startY = 20;	
    $this->currentY = $this->posit_startY - 0.5;		
	}
	/*空の行を挿入する*/
	public function addBlankRow($count){
		
    $row_num = $count;//order_numの続きを連番で振るため
		
    for($j = 0; $j < 20 - $count; $j++){
      //次の行のＹ軸開始値を設定
      $this->posit_startY += $this->baseHeight;			
      $row_num += 1;
      //製品レコード左太枠
      $this->pdf->SetLineWidth(0.4);
      $this->pdf->Line(26,$this->posit_startY,26,$this->posit_startY+$this->baseHeight);
      //製品レコード右太枠
      $this->pdf->Line(184,$this->posit_startY,184,$this->posit_startY+$this->baseHeight);
      //製品レコード出力
      //No.
      $this->pdf->SetLineWidth(0.2);
      $this->pdf->MultiCell($this->wid_no,$this->baseHeight,$row_num,'R,B','C',false, 0,$this->currentX[0],$this->posit_startY,true,0,false,true,0,'M',true);
      //製品番号
      $this->pdf->MultiCell($this->wid_number,$this->baseHeight,'','R,B','L',false, 0,$this->currentX[1],$this->posit_startY,true,0,false,true,0,'M',true);
      //数量
      $this->pdf->MultiCell($this->wid_quant,$this->baseHeight,'','R,B','C',false, 0,$this->currentX[2],$this->posit_startY,true,0,false,true,0,'M',true);
      //単価
      $this->pdf->MultiCell($this->wid_price,$this->baseHeight,'','R,B','R',false, 0,$this->currentX[3],$this->posit_startY,true,0,false,true,0,'M',true);
      //金額
      $this->pdf->MultiCell($this->wid_price,$this->baseHeight,'','R,B','R',false, 0,$this->currentX[4],$this->posit_startY,true,0,false,true,0,'M',true);
      //備考
      $this->pdf->MultiCell($this->wid_remarks,$this->baseHeight,'','B','L',false, 0,$this->currentX[5],$this->posit_startY,true,0,false,true,0,'M',true);				
    }		
	}
	/*現在Y軸の一行下に太線を引く*/
	public function writeBoldBorder(){		
    $this->pdf->SetLineWidth(0.4);
    $this->pdf->Line(26,$this->posit_startY+$this->baseHeight,184,$this->posit_startY+$this->baseHeight);				
	}
	/*PDF下部のコンテンツを出力する*/
	public function makeBottomContents($subtotal_price,$tax_rate,$outline){
		//小計、消費税、合計を描写できる余裕がなければ改ページ
		if($this->bottomToPage - $this->summarizeHeight < $this->posit_startY){
			$this->breakPage();
			$this->pdf->Line(26,$this->posit_startY,184,$this->posit_startY);		
		}else{
			$this->posit_startY += $this->baseHeight;	
		}
		//小計、消費税、合計を描写
    $summarize = array(
      '小計'=>$subtotal_price,
      '消費税'=>$subtotal_price * ($tax_rate - 1),
      '合計'=>$subtotal_price * $tax_rate,
    );			
    $i = 0;
    foreach($summarize as $key=>$value){  
      $this->pdf->SetLineWidth(0.4);
      //左太枠
      $this->pdf->Line(26,$this->posit_startY,26,$this->posit_startY+$this->baseHeight);
      //右太枠
      $this->pdf->Line(184,$this->posit_startY,184,$this->posit_startY+$this->baseHeight);

      $this->pdf->SetLineWidth(0.2);
      $this->pdf->MultiCell($this->wid_no+$this->wid_number+$this->wid_quant+$this->wid_price,$this->baseHeight,'','R,B','C',false, 0,$this->currentX[0],$this->posit_startY,true,0,false,true,0,'M',true);
      $this->pdf->MultiCell($this->wid_price,$this->baseHeight,$key,'R,B','C',false, 0,$this->currentX[4],$this->posit_startY,true,0,false,true,0,'M',true);
      $this->pdf->MultiCell($this->wid_remarks,$this->baseHeight,number_format($value).'円','R,B','R',false, 0,$this->currentX[5],$this->posit_startY,true,0,false,true,0,'M',true);
      $this->posit_startY += $this->baseHeight;	
      //最後の行であれば下枠を閉じる
      if($i === count($summarize)-1){
        $this->pdf->SetLineWidth(0.4);
        $this->pdf->Line(26,$this->posit_startY,184,$this->posit_startY);
      }
      $i++;
    }
		
		//下部備考を描写する余裕がなければ改ページ
		if($this->bottomToPage - $this->outlineHeight < $this->posit_startY + $this->baseHeight){
			$this->breakPage();
		}else{
			$this->posit_startY += $this->baseHeight;	
		}
    //上枠
    $this->pdf->SetLineWidth(0.4);
    $this->pdf->Line(26,$this->posit_startY,184,$this->posit_startY);		
    $this->pdf->Line(26,$this->posit_startY,26,$this->posit_startY+$this->baseHeight);		
    $this->pdf->Line(184,$this->posit_startY,184,$this->posit_startY+$this->baseHeight);		
    //備考見出し
    $this->pdf->SetLineWidth(0.2);
    $this->pdf->MultiCell(184-26,$this->baseHeight,'【備考】','B','L',false,0,$this->currentX[0],$this->posit_startY,true,0,false,true,0,'M',true);
    $this->posit_startY += $this->baseHeight;
    $this->pdf->SetLineWidth(0.4);
		
		$o_height = $this->bottomToPage - $this->posit_startY;
		if($this->bottomToPage - $this->posit_startY > 50){
			$o_height = 50;
		}

    $this->pdf->MultiCell(184-26,$o_height,$outline,'L,R,B','L',false,0,$this->currentX[0],$this->posit_startY,true,0,false,true,0,'M',true);		
		
	}
	/*ページネーションを設定する*/
	public function writePagination(){
    $totalNumOfPages = $this->pdf->getNumPages();
    for ($i = 1; $i <= $totalNumOfPages; $i++) {
        $this->pdf->setPage($i, true);
   			// y座標を初期化
        $this->pdf->setY(0);
				$this->pdf->text(184,280,($i.'/'.$totalNumOfPages),false);
    }
	}
	/*PDFを出力する*/
	public function outputPdf(){
		$this->pdf->Output('sample.pdf', 'I');		
	}
	/*セッター*/
	public function setInitialParam(){
		$this->currentX[0] = $this->posit_startX;
		$this->currentX[1] = $this->posit_startX+$this->wid_no;
		$this->currentX[2] = $this->posit_startX+$this->wid_no+$this->wid_number;
		$this->currentX[3] = $this->posit_startX+$this->wid_no+$this->wid_number+$this->wid_quant;
		$this->currentX[4] = $this->posit_startX+$this->wid_no+$this->wid_number+$this->wid_quant+$this->wid_price;
		$this->currentX[5] = $this->posit_startX+$this->wid_no+$this->wid_number+$this->wid_quant+$this->wid_price+$this->wid_price;
		
		$this->currentY = $this->posit_startY - 0.5;//-0.5は調整用の幅
	}
}