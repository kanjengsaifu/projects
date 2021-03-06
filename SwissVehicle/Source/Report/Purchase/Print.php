<?php
	if(isset($_POST['ddlItem'])) {
		$RequestPath = "$_SERVER[REQUEST_URI]";
		$file = basename($RequestPath);
		$RequestPath = str_replace($file, "", $RequestPath);
		include "../../GetPermission.php";
		include '../../assets/lib/fpdf17/fpdf.php';
		include '../../assets/lib/fpdf17-add-on/pdf_js.php';
		global $ItemID;
		global $SupplierID;
		global $txtFromDate;
		global $txtToDate;
		global $date;
		global $where;
		global $order_by;
		$ItemID = mysql_real_escape_string($_POST['ddlItem']);
		$SupplierID = mysql_real_escape_string($_POST['ddlSupplier']);
		$array_bulan = array(1=>'Januari','Februari','Maret', 'April', 'Mei', 'Juni','Juli','Agustus','September','Oktober', 'November','Desember');
		$tanggal = date('j');
		$tahun = date('Y');
		if((INT)strlen($tanggal) == 1) $tanggal = "0".$tanggal;
		$bulan = $array_bulan[date('n')];
		$StartDate = "";
		$EndDate = "";
		if($_POST['txtFromDate'] == "") {
			$txtFromDate = "2000-01-01";
			$StartDate = "01 Januari 2000";
		}
		else {
			$txtFromDate = explode('-', mysql_real_escape_string($_POST['txtFromDate']));
			$_POST['txtFromDate'] = "$txtFromDate[2]-$txtFromDate[1]-$txtFromDate[0]";
			$StartDate = $txtFromDate[0] ." ".$array_bulan[(int)$txtFromDate[1]]." ".$txtFromDate[2]; 
			$txtFromDate = $_POST['txtFromDate'];
		}
		if($_POST['txtToDate'] == "") {
			$txtToDate = date("Y-m-d");
			$EndDate = $tanggal." ".$bulan." ".$tahun;
		}
		else {
			$txtToDate = explode('-', mysql_real_escape_string($_POST['txtToDate']));
			$_POST['txtToDate'] = "$txtToDate[2]-$txtToDate[1]-$txtToDate[0]";
			$EndDate = $txtToDate[0] ." ".$array_bulan[(int)$txtToDate[1]]." ".$txtToDate[2];
			$txtToDate = $_POST['txtToDate'];
		}
		$date = $StartDate . " - " . $EndDate;
		
		$where = " 1=1 ";
		$order_by = "DateNoFormat ASC";
		if (ISSET($_GET['sort']) && $_GET['sort'] != "{}" )
		{
			$order_by = "";
			$_GET['sort'] = json_decode($_GET['sort'], true);
			foreach($_GET['sort'] as $key => $value) {
				if($key != 'No') $order_by .= " $key $value";
				else $order_by = "DateNoFormat ASC";
			}
		}
		//Handles search querystring sent from Bootgrid
		if (ISSET($_GET['searchPhrase']) )
		{
			$search = trim($_GET['searchPhrase']);
			$where .= " AND ( DATE_FORMAT(TP.TransactionDate, '%d-%m-%Y') LIKE '%".$search."%' OR MS.SupplierName LIKE '%".$search."%' OR MI.ItemName LIKE '%".$search."%' OR PD.Quantity LIKE '%".$search."%' OR PD.Price LIKE '%".$search."%' OR PD.Remarks LIKE '%".$search."%' )";
		}
		class PDF_AutoPrint extends PDF_JavaScript
		{
			var $width;
			var $aligns;
			function SetWidths($w)
			{
				//Set the array of column widths
				$this->widths=$w;
			}

			function SetAligns($a)
			{
				//Set the array of column alignments
				$this->aligns=$a;
			}

			function Row($data)
			{
				//Calculate the height of the row
				$nb=0;
				for($i=0;$i<count($data);$i++)
					$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
				$h=5*$nb;
				//Issue a page break first if needed
				$this->CheckPageBreak($h);
				//Draw the cells of the row
				for($i=0;$i<count($data);$i++)
				{
					$w=$this->widths[$i];
					$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
					//Save the current position
					$x=$this->GetX();
					$y=$this->GetY();
					//Draw the border
					$this->Rect($x,$y,$w,$h);
					//Print the text
					$this->MultiCell($w,5,$data[$i],0,$a);
					//Put the position to the right of the cell
					$this->SetXY($x+$w,$y);
				}
				//Go to the next line
				$this->Ln($h);
			}

			function CheckPageBreak($h)
			{
				//If the height h would cause an overflow, add a new page immediately
				if($this->GetY()+$h>$this->PageBreakTrigger)
					$this->AddPage($this->CurOrientation);
			}

			function NbLines($w,$txt)
			{
				//Computes the number of lines a MultiCell of width w will take
				$cw=&$this->CurrentFont['cw'];
				if($w==0)
					$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
				$s=str_replace("\r",'',$txt);
				$nb=strlen($s);
				if($nb>0 and $s[$nb-1]=="\n")
					$nb--;
				$sep=-1;
				$i=0;
				$j=0;
				$l=0;
				$nl=1;
				while($i<$nb)
				{
					$c=$s[$i];
					if($c=="\n")
					{
						$i++;
						$sep=-1;
						$j=$i;
						$l=0;
						$nl++;
						continue;
					}
					if($c==' ')
						$sep=$i;
					$l+=$cw[$c];
					if($l>$wmax)
					{
						if($sep==-1)
						{
							if($i==$j)
								$i++;
						}
						else
							$i=$sep+1;
						$sep=-1;
						$j=$i;
						$l=0;
						$nl++;
					}
					else
						$i++;
				}
				return $nl;
			}

			function Header() {
				$this->SetFont('Arial','B',14);
				$this->Cell(190,5,"Swiss House",0,1,'C');
				$this->SetFont('Arial','',9);
				$this->Cell(190,5,"Jalan Karang Anyar No 14",0,1,'C');
				$this->Cell(190,5,"Banyumanik - Semarang",0,1,'C');
				$this->Line(10,27,200,27);
				$this->Line(10,26,200,26);
				$this->Ln(5);
				//Judul Kolom
				$w = Array(20,25,50,10,20,25,40);
				$this->SetTextColor(0, 0, 0);
				$this->SetFont('Arial', 'B', 14);
				$this->Ln(2);
				$this->Cell(190,0.5,"Laporan Pembelian",0,0,'C');
				$this->Ln(8);
				$this->SetFont('Arial','',9);
				$this->Cell(15,0.5,"Tanggal",0,0,'L');
				$this->Cell(100,0.5," : ".$GLOBALS['date'],0,1,'L');
				$this->Ln(5);
				$header = array('Tanggal', 'Supplier', 'Barang', 'Qty', 'Harga', 'Total', 'Keterangan');
				$this->SetFont('Arial','B',9);
				for($i=0; $i<count($header); $i++) {
					$this->Cell($w[$i],5,$header[$i],1,0,'C');
				}
				$this->Ln(5);
			}
					
			function Table() {
				include "../../DBConfig.php";
				//Data
				$this->SetFont('Arial','',8);
				$sql = "SELECT
							DATE_FORMAT(TP.TransactionDate, '%d-%m-%Y') AS TransactionDate,
							TP.TransactionDate DateNoFormat,
							MS.SupplierName,
							MI.ItemName,
							PD.Quantity,
							PD.Price,
							(PD.Price * PD.Quantity) TotalAmount,
							PD.Remarks
						FROM
							transaction_purchase TP
							JOIN master_supplier MS
								ON MS.SupplierID = TP.SupplierID
							LEFT JOIN transaction_purchasedetails PD
								ON TP.PurchaseID = PD.PurchaseID
							LEFT JOIN master_item MI
								ON MI.ItemID = PD.ItemID 
						WHERE
							".$GLOBALS['where']."
							AND CAST(TP.TransactionDate AS DATE) >= '".$GLOBALS['txtFromDate']."'
							AND CAST(TP.TransactionDate AS DATE) <= '".$GLOBALS['txtToDate']."'
							AND CASE
								WHEN ".$GLOBALS['SupplierID']." = 0
								THEN MS.SupplierID
								ELSE ".$GLOBALS['SupplierID']."
							END = MS.SupplierID
							AND CASE
								WHEN ".$GLOBALS['ItemID']." = 0
								THEN MI.ItemID
								ELSE ".$GLOBALS['ItemID']."
							END = MI.ItemID
						ORDER BY
							".$GLOBALS['order_by'];
								
					if (! $result = mysql_query($sql, $dbh)) {
						echo mysql_error();
						return 0;
					}
					$Stock = 0;
					$GrandTotal = 0;
					while($row = mysql_fetch_array($result)) {
						$GrandTotal += $row['TotalAmount'];
						$this->Row(Array($row['TransactionDate'], $row['SupplierName'], $row['ItemName'], $row['Quantity'],  number_format($row['Price'],2,".",","), number_format($row['TotalAmount'],2,".",","), $row['Remarks']));
					}
					$this->Cell(125,5,'Grand Total',1,0,'L');
					$this->Cell(25,5,number_format($GrandTotal,2,".",","),1,0,'R');
					$this->Cell(40,5,'',1,0,'L');
			}

			function Footer() {
				$this->SetY(-15);
				$this->SetFont('Arial','',8);
				$this->Cell(0,10,"Halaman ".$this->PageNo()."/{nb}",0,0,'C');
			}
			
			function AutoPrint($dialog=false)
			{
				//Open the print dialog or start printing immediately on the standard printer
				$param=($dialog ? 'true' : 'false');
				$script="print($param);";
				$this->IncludeJS($script);
			}

			function AutoPrintToPrinter($server, $printer, $dialog=false)
			{
				//Print on a shared printer (requires at least Acrobat 6)
				$script = "var pp = getPrintParams();";
				if($dialog)
					$script .= "pp.interactive = pp.constants.interactionLevel.full;";
				else
					$script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
				$script .= "pp.printerName = '\\\\\\\\".$server."\\\\".$printer."';";
				$script .= "print(pp);";
				$this->IncludeJS($script);
			}
		}
		
		$pdf = new PDF_AutoPrint('P', 'mm', 'A4');
		$pdf->AliasNbPages();
		$pdf->SetTopMargin(10);
		$pdf->SetLeftMargin(10);
		$pdf->SetRightMargin(10);
		$pdf->AddPage();
		$pdf->SetWidths(Array(20,25,50,10,20,25,40));
		$pdf->SetAligns(Array('C', 'L', 'L', 'R', 'R', 'R', 'L'));
		srand(microtime()*1000000);
		$pdf->Table();
		$filename = "Laporan Pembelian " . $GLOBALS['date'] . ".pdf";
		//$pdf->AutoPrint(true);
		if($_GET['PrintType'] == "1") {
			$pdf->Output($filename, "D");
		}
		else {
			$pdf->AutoPrint(true);
			$pdf->Output($filename, "I");
		}
		
	}
?>
