<?php
	if(isset($_GET['id'])) {
		$RequestPath = "$_SERVER[REQUEST_URI]";
		$file = basename($RequestPath);
		$RequestPath = str_replace($file, "", $RequestPath);
		include "../../GetPermission.php";
		//echo $_SERVER['REQUEST_URI'];
		$Content = "";
		$Id = $_GET['id'];
		$Nama = "";
		$Tipe = "";
		$IsEdit = 0;
		
		if($cek==0) {
			$Content = "Anda Tidak Memiliki Akses Untuk Menu Ini!";
		}
		else {
			if($Id !=0) {
				$IsEdit = 1;
				//$Content = "Place the content here";
				$sql = "SELECT
							*
						FROM
							master_asisten
						WHERE
							asistenID = $Id";
							
				if (! $result=mysql_query($sql, $dbh)) {
					echo mysql_error();
					return 0;
				}				
				$row=mysql_fetch_row($result);
				$Id = $row[0];
				$Nama = $row[1];
				$Tipe = $row[2];
				
				
			}
		}
	}
?>
<html>
	<head>
	</head>
	<body>
		<div class="row">
			<div class="col-md-12">
				<h2>Master Data asisten</h2>   
			</div>
		</div>
		<!-- /. ROW  -->
		<hr />
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<strong><?php if($Id == 0) echo "Tambah"; else echo "Ubah"; ?> Data asisten</strong>  
					</div>
					<div class="panel-body">
						<form class="col-md-6" id="PostForm" method="POST" action="" >
							Nama:<br />
							<input id="hdnId" name="hdnId" type="hidden" <?php echo 'value="'.$Id.'"'; ?> />
							<input id="hdnIsEdit" name="hdnIsEdit" type="hidden" <?php echo 'value="'.$IsEdit.'"'; ?> />
							<input id="txtNama" name="txtNama" type="text" class="form-control" placeholder="Nama "   required <?php echo 'value="'.$Nama.'"'; ?> />
							<input id="hdnTipe" name="hdnTipe" type="hidden" <?php echo 'value="'.$Tipe.'"'; ?> />
							<br />
							
							Tipe:<br />
									<select class="form-control" name="ddlTipe" id="ddlTipe" required>
								<option value="" selected>-- Pilih Tipe --</option>
								<option value="1">S1 Industri</option>
								<option value="2">S1 Anak&Remaja </option>
								<option value="3">S2 Industri</option>
								
							</select>
								
							<br />
							
							<input type="button" class="btn btn-default" value="Simpan" onclick="SubmitForm('./Master/Asisten/Insert.php');" />
						</form>
					</div>
				</div>
			</div>
		</div>
		<script>
		
			$(document).ready(function () {
				$("#ddlTipe").val($("#hdnTipe").val());
			});
		
		</script>
	</body>
</html>