<?php
ob_start();
if(isset($_GET['DurationinMinutes']) && isset($_GET['RecordingStatus']))
{
	include 'AutoRecord.php';
	include 'BuildRecordingSchedule.php';
	include '../DBClass.php';
	if($_GET['RecordingStatus'] == 'Start')
	{
		if(is_numeric($_GET['DurationinMinutes']))
		{
			if($_GET['DurationinMinutes'] == 0)
			{
				$DurationinMinutes = '120';
			}else
			{
				$DurationinMinutes = $_GET['DurationinMinutes'];
			}
			$Name = AddNewScheduledRecording(date('H:i:s'), date('H:i:s',strtotime("+$DurationinMinutes Minutes")), date('Y-m-d'));
			StartRecording($DurationinMinutes * 60,$Name);
		}
	}else
	{
		StopRecording();
	}
}
header('location: index.php');
?>