<?php
	SESSION_START();
	if(isset($_POST['hdnCheckInID'])) {
		$RequestPath = "$_SERVER[REQUEST_URI]";
		$file = basename($RequestPath);
		$RequestPath = str_replace($file, "", $RequestPath);
		//include "../../GetPermission.php";
		include "../../DBConfig.php";
		$CheckInID = mysql_real_escape_string($_POST['hdnCheckInID']);
		$RoomID = mysql_real_escape_string($_POST['hdnRoomID']);
		mysql_query("START TRANSACTION", $dbh);
		mysql_query("SET autocommit=0", $dbh);
		$Message = "Check Out berhasil!";
		$MessageDetail = "";
		$FailedFlag = 0;
		//echo $DetailID;
		$State = 1;
		if($_POST['txtDownPaymentDate'] != "") {
			$DownPaymentDate = explode('-', mysql_real_escape_string($_POST['txtDownPaymentDate']));
			$DownPaymentDate = "$DownPaymentDate[2]-$DownPaymentDate[1]-$DownPaymentDate[0]";
		}
		else $DownPaymentDate = "";
		$DownPaymentAmount = $_POST['txtDownPaymentAmount'];
		if($DownPaymentAmount == "") $DownPaymentAmount = 0;
		
		if($_POST['txtPaymentDate'] != "") {
			$PaymentDate = explode('-', mysql_real_escape_string($_POST['txtPaymentDate']));
			$PaymentDate = "$PaymentDate[2]-$PaymentDate[1]-$PaymentDate[0]";
		}
		else $PaymentDate = "";
		
		$PaymentAmount = $_POST['txtPaymentAmount'];
		if($PaymentAmount == "") $PaymentAmount = 0;
		$sql = "UPDATE transaction_checkin
				SET
					CheckOutFlag = 1,
					DownPaymentAmount = ".str_replace(",", "", $DownPaymentAmount).",
					DownPaymentDate = '".$DownPaymentDate."',
					PaymentAmount = ".str_replace(",", "", $PaymentAmount).",
					PaymentDate = '".$PaymentDate."',
					ModifiedBy = '".$_SESSION['UserLogin']."'
				WHERE
					CheckInID = $CheckInID";

		if (! $result = mysql_query($sql, $dbh)) {
			$Message = "Terjadi Kesalahan Sistem";
			$MessageDetail = mysql_error();
			$FailedFlag = 1;
			echo returnstate($CheckInID, $Message, $MessageDetail, $FailedFlag, $State);
			mysql_query("ROLLBACK", $dbh);
			return 0;
		}
		
		$State = 2;
		$sql = "INSERT INTO transaction_checkout
				(
					TransactionDate,
					CheckInID,
					CreatedDate,
					CreatedBy
				)
				VALUES
				(
					NOW(),
					".$CheckInID.",
					NOW(),
					'".$_SESSION['UserLogin']."'
				)";
				
		if (! $result = mysql_query($sql, $dbh)) {
			$Message = "Terjadi Kesalahan Sistem";
			$MessageDetail = mysql_error();
			$FailedFlag = 1;
			echo returnstate($CheckInID, $Message, $MessageDetail, $FailedFlag, $State);
			mysql_query("ROLLBACK", $dbh);
			return 0;
		}
		
		$State = 3;
		$sql = "UPDATE
					master_room
				SET
					StatusID = 1
				WHERE
					RoomID = $RoomID";
					
		if (! $result = mysql_query($sql, $dbh)) {
			$Message = "Terjadi Kesalahan Sistem";
			$MessageDetail = mysql_error();
			$FailedFlag = 1;
			echo returnstate($CheckInID, $Message, $MessageDetail, $FailedFlag, $State);
			mysql_query("ROLLBACK", $dbh);
			return 0;
		}
		
		echo returnstate($CheckInID, $Message, $MessageDetail, $FailedFlag, $State);
		mysql_query("COMMIT", $dbh);
		return 0;
	}
	
	function returnstate($ID, $Message, $MessageDetail, $FailedFlag, $State) {
		$data = array(
			"ID" => $ID, 
			"Message" => $Message,
			"MessageDetail" => $MessageDetail,
			"FailedFlag" => $FailedFlag,
			"State" => $State
		);
		return json_encode($data);
	}
?>
