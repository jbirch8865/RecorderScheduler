<?php

function GetDayToStartRecording($DayOfWeek)
{
	$Weekdays = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
	return date('Y-m-d', strtotime("Next $Weekdays[$DayOfWeek]"));
}

function CalculateEndRecording($EndTime, $StartTime, $DayToRecord)
{
	$DoesThisCrossMidnight = strtotime($EndTime) < strtotime($StartTime);
	if($DoesThisCrossMidnight)
	{
		$EndRecording = date('Y-m-d', strtotime("+1 day", strtotime($DayToRecord)));
	}else
	{
		$EndRecording = $DayToRecord;			
	}
	return $EndRecording;	
}

function NowisaValidRunTime()
{
	if(date('Hi') == '130' || date('Hi') == '1418')
	{
		return true;
	}else
	{
		return false;
	}
}

function GetScheduledRecordings()
{
	$Link = new MySQLLink('Dayspring'); 
	$RecordingsToCreate = $Link->ExecuteSQLQuery('SELECT * FROM Recording_Preferences');
	return $RecordingsToCreate;
}

function AddNewScheduledRecording($StartTime, $EndTime, $DayToRecord)
{
	$StartTimeStamp = $DayToRecord.' '.$StartTime;
	$EndTimeStamp = CalculateEndRecording($EndTime, $StartTime, $DayToRecord).' '.$EndTime;		
	$Link = new MySQLLink('Dayspring');
	$Link->ExecuteSQLQuery("INSERT INTO Services_To_Record SET `Timestamp_To_Start` = '$StartTimeStamp', `Timestamp_To_Stop` = '$EndTimeStamp', `RecordingStatus` = '1', `FileLocation` = 'Recordings/'");
	return $Link->GetLastInsertID();
}

function ScheduleAllConfiguredRecordings()
{
	$SchedulesToRecord = GetScheduledRecordings();
	While($ScheduleToRecord = mysqli_fetch_assoc($SchedulesToRecord))
	{
		AddNewScheduledRecording($ScheduleToRecord['Start_Time'],$ScheduleToRecord['End_Time'],GetDayToStartRecording($ScheduleToRecord['Day_Of_Week']));
		
	}	
}
function AutoAddServiceToRecord()
{
	if(NowisaValidRunTime())
	{
		ScheduleAllConfiguredRecordings();
	}
}

?>