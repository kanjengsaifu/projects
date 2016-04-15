<?php
	if(ISSET($_GET['BrandID']) && ISSET($_GET['TypeID'])) {
		$RequestPath = "$_SERVER[REQUEST_URI]";
		$file = basename($RequestPath);
		$RequestPath = str_replace($file, "", $RequestPath);	
		include "../../GetPermission.php";
		//echo $_SERVER['REQUEST_URI'];
		$BrandID = mysql_real_escape_string($_GET['BrandID']);
		$TypeID = mysql_real_escape_string($_GET['TypeID']);
				
		if($_GET['txtFromDate'] == "") {
			$txtFromDate = "2000-01-01";
			$FromDate = "01-01-2000";
		}
		else {
			$FromDate = $_GET['txtFromDate'];
			$txtFromDate = explode('-', mysql_real_escape_string($_GET['txtFromDate']));
			$_GET['txtFromDate'] = "$txtFromDate[2]-$txtFromDate[1]-$txtFromDate[0]"; 
			$txtFromDate = $_GET['txtFromDate'];
		}
		if($_GET['txtToDate'] == "") {
			$txtToDate = date("Y-m-d");
			$ToDate = date("d-m-Y");
		}
		else {
			$ToDate = $_GET['txtToDate'];
			$txtToDate = explode('-', mysql_real_escape_string($_GET['txtToDate']));
			$_GET['txtToDate'] = "$txtToDate[2]-$txtToDate[1]-$txtToDate[0]"; 
			$txtToDate = $_GET['txtToDate'];
		}

		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
		date_default_timezone_set("Asia/Jakarta");

		if (PHP_SAPI == 'cli')
			die('This example should only be run from a Web Browser');

		/** Include PHPExcel */
		require_once '../../assets/lib/PHPExcel.php';


		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator($_SESSION['UserLogin'])
									 ->setLastModifiedBy($_SESSION['UserLogin'])
									 ->setTitle("Laporan Penjualan Per Barang")
									 ->setSubject("Laporan")
									 ->setDescription("Laporan Penjualan Per Barang")
									 ->setKeywords("Generate By PHPExcel")
									 ->setCategory("Laporan");
		//Header
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', "LAPORAN PENJUALAN PER BARANG");
		
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
		
		//set bold
		$objPHPExcel->getActiveSheet()->getStyle("A1:A2")->getFont()->setBold(true);
		
		$rowExcel = 4;
		$col = 0;
		//set color
		//$objPHPExcel->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKGREEN ) );
		$objPHPExcel->getActiveSheet()->setCellValue("A".$rowExcel, "No");
		$objPHPExcel->getActiveSheet()->setCellValue("B".$rowExcel, "No Nota");
		$objPHPExcel->getActiveSheet()->setCellValue("C".$rowExcel, "Tanggal");
		$objPHPExcel->getActiveSheet()->setCellValue("D".$rowExcel, "Nama Sales");
		$objPHPExcel->getActiveSheet()->setCellValue("E".$rowExcel, "Nama Pelanggan");
		$objPHPExcel->getActiveSheet()->setCellValue("F".$rowExcel, "Merek");
		$objPHPExcel->getActiveSheet()->setCellValue("G".$rowExcel, "Tipe");
		$objPHPExcel->getActiveSheet()->setCellValue("H".$rowExcel, "Batch");
		$objPHPExcel->getActiveSheet()->setCellValue("I".$rowExcel, "Qty");
		$objPHPExcel->getActiveSheet()->setCellValue("J".$rowExcel, "Harga jual");
		$objPHPExcel->getActiveSheet()->setCellValue("K".$rowExcel, "Diskon");
		$objPHPExcel->getActiveSheet()->setCellValue("L".$rowExcel, "Total");
		$objPHPExcel->getActiveSheet()->setCellValue("M".$rowExcel, "Keterangan");
		$rowExcel++;
		
		$sql = "SELECT
					OT.OutgoingNumber,
					DATE_FORMAT(OT.TransactionDate, '%d/%c%/%y') AS TransactionDate,
					MS.SalesName,
					MC.CustomerName,
					MB.BrandName,
					MT.TypeName,
					TOD.BatchNumber,
					TOD.Quantity,
					TOD.SalePrice,
					CASE
						WHEN TOD.IsPercentage = 1
						THEN (TOD.SalePrice * TOD.Discount)/100
						ELSE TOD.Discount
					END DiscountAmount,
					CASE
						WHEN TOD.IsPercentage = 1 AND TOD.Discount <> 0
						THEN CONCAT('(', TOD.Discount, '%)')
						ELSE ''
					END Discount,
					TOD.IsPercentage,
					CASE
						WHEN TOD.IsPercentage = 1
						THEN IFNULL(TOD.Quantity * (TOD.SalePrice - ((TOD.SalePrice * TOD.Discount)/100)), 0)
						ELSE IFNULL(TOD.Quantity * (TOD.SalePrice - TOD.Discount), 0)
					END AS Total,
					OT.Remarks
				FROM
					transaction_outgoing OT
					JOIN master_sales MS
						ON MS.SalesID = OT.SalesID
					JOIN master_customer MC
						ON MC.CustomerID = OT.CustomerID
					LEFT JOIN transaction_outgoingdetails TOD
						ON TOD.OutgoingID = OT.OutgoingID
					LEFT JOIN master_type MT
						ON MT.TypeID = TOD.TypeID
					LEFT JOIN master_brand MB
						ON MB.BrandID = MT.BrandID
				WHERE
					OT.TransactionDate >= '".$txtFromDate."'
					AND OT.TransactionDate <= '".$txtToDate."'
					AND CASE
							WHEN ".$BrandID." = 0
							THEN MB.BrandID
							ELSE ".$BrandID."
						END = MB.BrandID
					AND CASE
							WHEN ".$TypeID." = 0
							THEN MT.TypeID
							ELSE ".$TypeID."
						END = MT.TypeID
				GROUP BY
					OT.TransactionDate,
					MS.SalesName,
					MC.CustomerName,
					MB.BrandName,
					OT.OutgoingNumber,
					MT.TypeName,
					TOD.BatchNumber,
					TOD.Quantity,
					TOD.SalePrice,
					OT.Remarks
				UNION ALL
				SELECT
					SR.SaleReturnNumber,
					DATE_FORMAT(SR.TransactionDate, '%d/%c%/%y') AS TransactionDate,
					'',
					MC.CustomerName,
					MB.BrandName,
					MT.TypeName,
					SRD.BatchNumber,
					SRD.Quantity,
					SRD.SalePrice,
					CASE
						WHEN SRD.IsPercentage = 1
						THEN (SRD.SalePrice * SRD.Discount)/100
						ELSE SRD.Discount
					END DiscountAmount,
					CASE
						WHEN SRD.IsPercentage = 1 AND SRD.Discount <> 0
						THEN CONCAT('(', SRD.Discount, '%)')
						ELSE ''
					END Discount,
					SRD.IsPercentage,
					-CASE
						WHEN SRD.IsPercentage = 1
						THEN IFNULL(SRD.Quantity * (SRD.SalePrice - ((SRD.SalePrice * SRD.Discount)/100)), 0)
						ELSE IFNULL(SRD.Quantity * (SRD.SalePrice - SRD.Discount), 0)
					 END AS Total,
					SR.Remarks
				FROM
					transaction_salereturn SR
					JOIN master_customer MC
						ON MC.CustomerID = SR.CustomerID
					LEFT JOIN transaction_salereturndetails SRD
						ON SRD.SaleReturnID = SR.SaleReturnID
					LEFT JOIN master_type MT
						ON MT.TypeID = SRD.TypeID
					LEFT JOIN master_brand MB
						ON MB.BrandID = MT.BrandID
				WHERE
					SR.TransactionDate >= '".$txtFromDate."'
					AND SR.TransactionDate <= '".$txtToDate."'
					AND CASE
							WHEN ".$BrandID." = 0
							THEN MB.BrandID
							ELSE ".$BrandID."
						END = MB.BrandID
					AND CASE
							WHEN ".$TypeID." = 0
							THEN MT.TypeID
							ELSE ".$TypeID."
						END = MT.TypeID
				GROUP BY
					SR.TransactionDate,
					MC.CustomerName,
					MB.BrandName,
					SR.SaleReturnNumber,
					MT.TypeName,
					SRD.BatchNumber,
					SRD.Quantity,
					SRD.SalePrice,
					SR.Remarks
				ORDER BY	
					TransactionDate ASC";
					
		if (! $result = mysql_query($sql, $dbh)) {
			echo mysql_error();
			return 0;
		}
		$RowNumber = 1;
		$Stock = 0;
		while($row = mysql_fetch_array($result)) {
			$objPHPExcel->getActiveSheet()->setCellValue("A".$rowExcel, $RowNumber);
			$objPHPExcel->getActiveSheet()->setCellValue("B".$rowExcel, $row['OutgoingNumber']);
			$objPHPExcel->getActiveSheet()->setCellValue("C".$rowExcel, $row['TransactionDate']);
			$objPHPExcel->getActiveSheet()->setCellValue("D".$rowExcel, $row['SalesName']);
			$objPHPExcel->getActiveSheet()->setCellValue("E".$rowExcel, $row['CustomerName']);
			$objPHPExcel->getActiveSheet()->setCellValue("F".$rowExcel, $row['BrandName']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$rowExcel, $row['TypeName'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue("H".$rowExcel, $row['BatchNumber']);
			$objPHPExcel->getActiveSheet()->setCellValue("I".$rowExcel, $row['Quantity']);
			$objPHPExcel->getActiveSheet()->setCellValue("J".$rowExcel, $row['SalePrice']);
			$objPHPExcel->getActiveSheet()->setCellValue("K".$rowExcel, number_format($row['DiscountAmount'],2,".",",").$row['Discount']);
			$objPHPExcel->getActiveSheet()->setCellValue("L".$rowExcel, $row['Total']);
			$objPHPExcel->getActiveSheet()->setCellValue("M".$rowExcel, $row['Remarks']);
			$RowNumber++;
			$rowExcel++;
		}
		$objPHPExcel->getActiveSheet()->getStyle("J5:J".$rowExcel)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->getStyle("K5:K".$rowExcel)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$objPHPExcel->getActiveSheet()->getStyle("L5:L".$rowExcel)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$objPHPExcel->getActiveSheet()->setCellValue("L".$rowExcel, "=SUM(L5:L".($rowExcel-1).")");
		$objPHPExcel->getActiveSheet()->setCellValue("A".$rowExcel, "Grand Total");
		$objPHPExcel->getActiveSheet()->mergeCells("A".$rowExcel.":K".$rowExcel);
		$rowExcel++;
		//merge title
		$objPHPExcel->getActiveSheet()->mergeCells("A1:M2");
		$objPHPExcel->getActiveSheet()->getStyle("A4:M4")->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle("A1:M2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle("A4:M4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('c4bd97');

		//set all width 
		$fromCol='A';
		$toCol= 'N';
		for($j = $fromCol; $j !== $toCol; $j++) {
			//$calculatedWidth = $objPHPExcel->getActiveSheet()->getColumnDimension($i)->getWidth();
			$objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
		}
		$styleArray = array(
			'borders' => array(
			  'allborders' => array(
				  'style' => PHPExcel_Style_Border::BORDER_THIN
			  )
			)
		);		
		$objPHPExcel->getActiveSheet()->getStyle("A4:M".($rowExcel-1))->applyFromArray($styleArray);		

		$title = "Laporan Penjualan Per Barang $FromDate - $ToDate";
		// Rename worksheet
		//$objPHPExcel->getActiveSheet()->setTitle($title);
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);


		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');

		//output file name
		header('Content-Disposition: attachment;filename="'.$title.'.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
?>