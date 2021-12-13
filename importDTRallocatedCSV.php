<?php


include_once("fonction.php");
include_once("config.php");

$message = "<br>START : ".date("d-m-Y H:i:s");

//recupere le nom du fichier indiqué par l'user 
// $fichier="../import_ttc/DTRMetrics.xlsm"; 
$fichier = "c:/intranet/".$applocation."/import_ttc/DTRInformation.Metrics.PROD.csv"; 

$openfile = file($fichier);

$row = 1;
if (($handle = fopen($fichier, "r")) !== FALSE) {
    while (($value = fgetcsv($handle, 0, ",")) !== FALSE)
	{
		if ($row >= 12500)
		{
			// $num = count($value);
			// echo "<p> $num champs à la ligne $row: <br /></p>\n";
			// for ($c=0; $c < $num; $c++) {
				// echo "\"".$value[$c] . "\"<br />\n";
			// }

			if ($value[16] == "Allocated")
			{
				$sql = "SELECT id, batchno, site_ptp FROM essai WHERE batchno LIKE '".$value[0]."%_DTR' ORDER BY batchno ASC";
				$req = $conex->query($sql);
				// $message .= "<P>" . $conex->connect_errno . " : " . $conex->error . "<P>";
				
				if (mysqli_num_rows($req) > 0)
				{
					$message .= "<P>".$value[0]." | on : ".$value[17]." | Allocated to : ".$value[20];
					
					$message .= "<br>TB found in PTP : ";
					while($enr = mysqli_fetch_object($req))
					{
						$message .= "<br>".$enr->batchno." ".$enr->site_ptp;
						if ($enr->site_ptp != $value[20] AND substr($enr->batchno, -3) == "DTR")
						{
							update("essai", "dtrtodelete = 'Allocated_in_PTE'", "id = '".$enr->id."'");
							update("essai_bkp", "dtrtodelete = 'Allocated_in_PTE'", "id = '".$enr->id."'");
							$message .= " TO DELETE";
						} else {
							$message .= " TO KEEP";
						}
					}
				}
			}
			
			elseif ($value[16] == "Cancelled")
			{
				$sql = "SELECT id, batchno, site_ptp FROM essai WHERE batchno LIKE '".$value[0]."%_DTR' ORDER BY batchno ASC";
				$req = $conex->query($sql);
				// $message .= "<P>" . $conex->connect_errno . " : " . $conex->error . "<P>";
				
				if (mysqli_num_rows($req) > 0)
				{
					$message .= "<P>".$value[0]." | on : ".$value[18]." | Cancelled";
					
					$message .= "<br>TB found in PTP : ";
					while($enr = mysqli_fetch_object($req))
					{
						$message .= "<br>".$enr->batchno." ".$enr->site_ptp;
						// if ($enr->site_ptp != $value[20] AND substr($enr->batchno, -3) == "DTR") 
						// {
							update("essai", "dtrtodelete = 'Cancelled_in_PTE'", "id = '".$enr->id."'");
							update("essai_bkp", "dtrtodelete = 'Cancelled_in_PTE'", "id = '".$enr->id."'");
							$message .= " TO DELETE";
						// } else {
							// $message .= " TO KEEP";
						// }
					}
				}
			}
       }
		$row++;
    }
    fclose($handle);
}


$message .= "<br>Fin boucle ".date("d-m-Y H:i:s");
 echo $message;
 
$message = str_replace("<br>", "\n",$message);
$message = str_replace("<P>", "\n\n",$message);
$message = str_replace("<b>", "",$message);
$message = str_replace("</b>", "",$message);

// creation ouverture fichier LOG
$logfileImport = fopen("importDTRallocatedCSV.log", "a");
// ECRITURE LOG
$txtlogfileImport = "\n".$message;
fwrite($logfileImport, $txtlogfileImport);
// FERMETURE DU FICHIER
fclose($logfileImport);

mysqli_close($conex);


?>
