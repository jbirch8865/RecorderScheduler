<?php
if(isset($_GET['date']) && isset($_GET['start_time']) && isset($_GET['end_time']) && isset($_GET['DurationinMinutes']))
{
	include 'BuildRecordingSchedule.php';
	include '../DBClass.php';
	if(!$_GET['DurationinMinutes'] == 0)
	{		
		$EndTime = date('H:i:s',strtotime("+".$_GET['DurationinMinutes']." Minutes"));
		echo $EndTime;
	}else
	{
		$EndTime = date('H:i:s',strtotime($_GET['end_time']));
	}
	AddNewScheduledRecording(date('H:i:s',strtotime($_GET['start_time'])), $EndTime, date('Y-m-d',strtotime($_GET['date'])));
}
header('location: index.php');
?>