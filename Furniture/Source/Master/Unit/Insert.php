<?php
	if(isset($_POST['hdnUnitID'])) {
		$RequestPath = "$_SERVER[REQUEST_URI]";
		$file = basename($RequestPath);
		$RequestPath = str_replace($file, "", $RequestPath);
		include "../../GetPermission.php";
		$UnitID = mysql_real_escape_string($_POST['hdnUnitID']);
		$UnitName = mysql_real_escape_string($_POST['txtUnitName']);
		$hdnIsEdit = mysql_real_escape_string($_POST['hdnIsEdit']);
		$Message = "Data gagal dimasukkan, cek koneksi internet dan coba lagi!";
		$MessageDetail = "";
		$FailedFlag = 0;
		$State = 1;
		
		$sql = "CALL spInsUnit(".$UnitID.", '".$UnitName."', ".$hdnIsEdit.", '".$_SESSION['UserLogin']."')";
		
		if (! $result=mysql_query($sql, $dbh)) {
			echo mysql_error();
			return 0;
		}				
		$row=mysql_fetch_array($result);
		echo returnstate($row['ID'], $row['Message'], $row['MessageDetail'], $row['FailedFlag'], $row['State']);
	}
	
	function returnstate($ID, $Message, $MessageDetail, $FailedFlag, $State) {
		$data = array(
			"Id" => $ID, 
			"Message" => $Message,
			"MessageDetail" => $MessageDetail,
			"FailedFlag" => $FailedFlag,
			"State" => $State
		);
		return json_encode($data);
	
	}
?>
