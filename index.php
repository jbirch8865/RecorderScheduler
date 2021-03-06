<?php
include '../DBClass.php';
include 'AutoRecord.php';
function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

?>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src = "../Developer Tools/jquery.js"></script>
<script src = "LoadPage.js"></script>
</head>
<body>
<h2>Avaiable Recording Space - <?php echo human_filesize(disk_free_space("/var/www/html/DayspringMen/"));?> </h2>
<form action = "ManuallyRecord.php" method = "GET">
<h2>Recording Status</h2>
Duration in Minutes(0 = 2hrs)<input type = "number" min = "00" max = "120" step = "01" name = "DurationinMinutes" value = "00" required><br><br>
<input type = "hidden" id = "RecordingStatus" name = "RecordingStatus" value = "">
<input type = "submit" id = "Recordbutton" value = "Checking Status" disabled>
</form>

<h2>Recording Schedule</h2>
<table>
<tr><th>Service To Record</th><th>status</th><th>Start Recording</th><th>Stop Recording</th><th>Recording Control</th><th>Download</th><th>File Size</th></tr>
<?php
$ServicesToRecord = ServicesToRecord();
While($row = mysqli_fetch_assoc($ServicesToRecord))
{
	$Download = true;
	if(file_exists("/var/www/html/DayspringMen/Recordings/".$row['id'].".wav"))
	{
		$fileSize = human_filesize(filesize("/var/www/html/DayspringMen/Recordings/".$row['id'].".wav"));
	}else
	{
		$fileSize = "Pending";
	}
	if($row['Title'] == 'Pending')
	{
		$Download = false;
		$Option = "<a href = 'ManuallyDelete.php?id=".$row['id']."'>Delete</a>";
	}elseif($row['Title'] == 'Recording')
	{
		$Option = "<a href = 'ManuallyStop.php?id=".$row['id']."'>Stop</a>";		
	}else
	{
		$Option = "<a href = 'ManuallyDelete.php?id=".$row['id']."'>Delete</a>";
	}
	if($Download){$Download = '<a href="'.$row['FileLocation'].$row['id'].'.wav'.'">Download</a>';}
	echo '<tr><td>'.$row['id'].'</td><td>'.$row['Title'].'</td><td>'.date('M-d h:i A',strtotime($row['Timestamp_To_Start'])).'</td><td>'.date('M-d h:i A',strtotime($row['Timestamp_To_Stop'])).'</td><td>'.$Option.'</td><td>'.$Download.'</td><td>'.$fileSize.'</td></tr>';
}
?>
</table>
<form action = "AddRecording.php" method = "GET">
<h2>Schedule Recording</h2>
Date: <input type = "date" name = "date" required><br><br>
StartTime: <input type = "time" name = "start_time" required><br><br>
EndTime: <input type = "time" name = "end_time"><br>
OR<br>
Duration in Minutes: <input name = "DurationinMinutes" type = "number" step = "01" min = "01" max = "3000"><br>
<input type = "submit">
</form>

</body>