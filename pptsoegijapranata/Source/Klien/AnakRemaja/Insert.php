<?php
	if(isset($_POST['hdnId'])) {
		$RequestPath = "$_SERVER[REQUEST_URI]";
		$file = basename($RequestPath);
		$RequestPath = str_replace($file, "", $RequestPath);
		include "../../GetPermission.php";
		$Id = $_POST['hdnId'];
		$Nama = $_POST['txtNama'];
		$JKelamin= $_POST['ddlJKelamin'];
		$TLahir=$_POST['txtTLahir'];
		$TglLahir = explode('-', $_POST['dateTglLahir']);
		$_POST['dateTglLahir'] = "$TglLahir[2]-$TglLahir[1]-$TglLahir[0]";
		$TglLahir=$_POST['dateTglLahir'];
		$NamaAyah=$_POST['txtAyah'];
		$NamaIbu=$_POST['txtIbu'];
		$Alamat = $_POST['txtAlamat'];
		$Telepon = $_POST['txtTelepon'];
		$Keterangan = $_POST['txtKeterangan'];
		$jenisKlien = $_POST['hdnJenis'];
		$hdnIsEdit = $_POST['hdnIsEdit'];
		$Message = "Data gagal dimasukkan, cek koneksi internet dan coba lagi!";
		$MessageDetail = "";
		$FailedFlag = 0;
		$State = 1;
		if($cek==0) {
			$Content = "Anda Tidak Memiliki Akses Untuk Menu Ini!";
		}
		else {
	
			$sql = "CALL spInsAnakRemaja(".$Id.",'".$jenisKlien."','".$Nama."','".$JKelamin."', '".$TLahir."','".$TglLahir."','".$NamaAyah."','".$NamaIbu."','".$Alamat."','".$Telepon."','".$Keterangan."','".$hdnIsEdit."','".$_SESSION['Username']."')";
			
			if (! $result=mysql_query($sql, $dbh)) {
				echo mysql_error();
				return 0;
			}				
			$row=mysql_fetch_row($result);
			
			
			echo returnstate($row[0], $row[1], $row[2],$row[3], $row[4]);
		}
	}
	
	function returnstate($Id, $Message, $MessageDetail, $FailedFlag, $State) {
		$data = array(
			"Id" => $Id, 
			"Message" => $Message,
			"MessageDetail" => $MessageDetail,
			"FailedFlag" => $FailedFlag,
			"State" => $State
		);
		return json_encode($data);
	
	}
?>