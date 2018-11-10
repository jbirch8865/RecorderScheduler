<?php
function AllRecordingsReportingExceptErrors()
{
	$Link = new MySQLLink('Dayspring');
	$ServicesToRecord = $Link->ExecuteSQLQuery("SELECT * FROM Services_To_Record WHERE RecordingStatus IS NOT NULL");
	return $ServicesToRecord;	
}

function RecordingsReportingRunningStatus()
{
	$Link = new MySQLLink('Dayspring');
	$ServicesToRecord = $Link->ExecuteSQLQuery("SELECT * FROM Services_To_Record WHERE RecordingStatus = '2'");
	return $ServicesToRecord;
}

function TodaysRecordings()
{
	$Link = new MySQLLink('Dayspring');
	$TodaysRecordings = $Link->ExecuteSQLQuery("SELECT * FROM Services_To_Record WHERE DATE(Timestamp_To_Start) = '".date('Y-m-d')."' OR DATE(Timestamp_To_Stop) = '".date('Y-m-d')."'");
	return $TodaysRecordings;	
}

function RecordingsToStart()
{
	$Link = new MySQLLink('Dayspring');
	$ServicesToRecord = $Link->ExecuteSQLQuery("SELECT * FROM Services_To_Record WHERE (DATE(Timestamp_To_Start) = '".date('Y-m-d')."' OR DATE(Timestamp_To_Stop) = '".date('Y-m-d')."') AND RecordingStatus = '1'");
	return $ServicesToRecord;
}

function ServicesToRecord()
{
	$Link = new MySQLLink('Dayspring');
	$ServicesToRecord = $Link->ExecuteSQLQuery("SELECT * FROM Services_To_Record INNER JOIN Recording_Status ON Services_To_Record.RecordingStatus = Recording_Status.Status_id ORDER BY Timestamp_To_Start DESC");
	return $ServicesToRecord;	
}

function VerifyKillArecord()
{
	if(isRecording())
	{
		KillARecord();
	}else
	{
		return true;
	}
	sleep(1);
	if(isRecording())
	{
		return false;
	}else
	{
		return true;
	}
}

function KillARecord()
{
	$run = 'sudo -pi pkill arecord 2>&1';
	$response = shell_exec($run);
	$Link = new MySQLLink('Dayspring');
	$Link->AddToSyslog($run, $response);
	return $response;
}

function GetCurrentFileState()
{
	$dir = new DirectoryIterator(dirname(__FILE__).'/Recordings');
	$Files = array();
	if(iterator_count($dir) > 100){return false;}
	foreach ($dir as $fileinfo) {
		clearstatcache();
		if (!$fileinfo->isDot()) {
			//echo $fileinfo->getpathName()."<br><br>";
			$Files[] = array('FileName' => $fileinfo->getfileName(),'FileSize' => filesize($fileinfo->getpathName()), 'LastModified' => filemtime($fileinfo->getpathName()));
		}
	}
	return $Files;
}

function GetFilesRecording()
{
	$FileState1 = GetCurrentFileState();
	sleep(2);
	$FileState2 = GetCurrentFileState();
	/*
	echo '<br><br>';
	print_r($FileState1);
	echo '<br><br>';
	print_r($FileState2);
	echo '<br><br>';
	*/
	$ChangedFiles = array();
	if(count($FileState1) == count($FileState2))
	{
		ForEach($FileState1 as $File => $FileInfo)
		{
			if($FileState2[$File]['FileSize'] != $FileInfo['FileSize'])
			{
				$ChangedFiles[] = $FileState2[$File];
			}
		}
	}else
	{
		return false;
	}
	return $ChangedFiles;
}

function isRecording()
{
	if(count(GetFilesRecording()) > 0)
	{
		return true;
	}else
	{
		SetRunningRecordsToStop();			
		return false;
	}
	
//	shell_exec('sudo -u pi arecord -d 10 Recordings/test.wav > /dev/null 2>/dev/null &')
}

function CanIBeRecording()
{
	$TodaysRecordings = TodaysRecordings();
	While($row = mysqli_fetch_assoc($TodaysRecordings))
	{
		$StartRecording = $row['Timestamp_To_Start'];
		$EndRecording = $row['Timestamp_To_Stop'];
		$CurrentStatus = $row['RecordingStatus'];

		if(strtotime(date('Y-m-d H:i:s')) < strtotime("+30 minutes",strtotime($EndRecording)) && strtotime(date('Y-m-d H:i:s')) > strtotime("-30 minutes",strtotime($StartRecording)))
		{
			return true;
		}
	}
		return false;
}

function ShouldIBeRecording()
{
	$TodaysRecordings = RecordingsToStart();
	While($row = mysqli_fetch_assoc($TodaysRecordings))
	{

		$StartRecording = $row['Timestamp_To_Start'];
		$EndRecording = $row['Timestamp_To_Stop'];
		$CurrentStatus = $row['RecordingStatus'];
		$Name = $row['id'];
		if(!isRecording() && strtotime(date('Y-m-d H:i:s')) < strtotime("+3 minutes",strtotime($StartRecording)) && strtotime(date('Y-m-d H:i:s')) > strtotime("-30 seconds",strtotime($StartRecording)))
		{
			$Duration = CalculateRecordingDuration($StartRecording, $EndRecording);
			if(StartRecording($Duration, $Name))
			{
				return true;
			}else
			{
				return false;
			}
		}

	}

	return false;	
}

function SetRecordingStatusToStart($id)
{
	$Link = new MySQLLink('Dayspring');
	$Link->ExecuteSQLQuery("UPDATE Services_To_Record SET RecordingStatus = '2' WHERE id = '$id'");
}

function SetRecordingStatusToError($id)
{
	$Link = new MySQLLink('Dayspring');
	$Link->ExecuteSQLQuery("UPDATE Services_To_Record SET RecordingStatus = NULL WHERE id = '$id'");
	
}

function SetRecordingStatusToStop($id)
{
	$Link = new MySQLLink('Dayspring');
	$Link->ExecuteSQLQuery("UPDATE Services_To_Record SET RecordingStatus = '3' WHERE id = '$id'");
	
}

function SetRecordingStatusToRendered($id)
{
	$Link = new MySQLLink('Dayspring');
	$Link->ExecuteSQLQuery("UPDATE Services_To_Record SET RecordingStatus = '4' WHERE id = '$id'");
	
}

function SetRecordingStatusToUploaded($id)
{
	$Link = new MySQLLink('Dayspring');
	$Link->ExecuteSQLQuery("UPDATE Services_To_Record SET RecordingStatus = '5' WHERE id = '$id'");
	
}

function MarkRecordingStartStopTimes($MySQLid, $Duration)
{
	$Link = new MySQLLink('Dayspring');
	$Link->ExecuteSQLQuery("UPDATE Services_To_Record SET Recording_Started = CURRENT_TIMESTAMP, Recording_Set_To_End = TIMESTAMPADD(SECOND,$Duration,CURRENT_TIMESTAMP) WHERE id = '$MySQLid'");
}

function StartRecording($DurationinSeconds, $Name)
{
	if($DurationinSeconds > 7200){$DurationinSeconds = 7200;}
	//increased by .6, 
	$SampleRateAdjustment = round($DurationinSeconds * 5.52,0); //This multiplier is based on the sample rate being run, the duration in seconds is based on a sample rate of 8000mhz, however the below command in our configuration will run at 44100mhz
	echo "sudo -u pi arecord -f S16_LE -D hw:1,0 -d $SampleRateAdjustment /var/www/html/DayspringMen/Recordings/$Name.wav > /dev/null 2>/dev/null &";
	$run = "sudo -u pi arecord -f S16_LE -D hw:1,0 -d $SampleRateAdjustment /var/www/html/DayspringMen/Recordings/$Name.wav > /dev/null 2>/dev/null &";
	$response = shell_exec($run);
	$Link = new MySQLLink('Dayspring');
	$Link->AddToSyslog($run,$response);
	if(isRecording())
	{
		SetRecordingStatusToStart($Name);
		MarkRecordingStartStopTimes($Name, $DurationinSeconds);
		return true;
	}else
	{
		SetRecordingStatusToError($Name);
		return false;
	}
}

function StopRecording()
{
	VerifyKillArecord();
}

function CalculateRecordingDuration($StartTime, $EndTime)
{
	$timeFirst  = strtotime($StartTime);
	$timeSecond = strtotime($EndTime);
	$differenceInSeconds = $timeSecond - $timeFirst;
	return $differenceInSeconds;
}

function KillRougeRecordings()
{
	$TodaysRecordings = mysqli_fetch_all(TodaysRecordings());
	if(count($TodaysRecordings) > 0)
	{
		if(CanIBeRecording())
		{
			return true;
		}else
		{
			if(VerifyKillArecord())
			{
				return true;
			}else
			{
				return false;
			}
		}
	}else
	{
			if(VerifyKillArecord())
			{
				return true;
			}else
			{
				return false;
			}
		
	}
}

function SetAutoRecordsToStopAfterDuration()
{
	
	$Recordings = RecordingsReportingRunningStatus();
	
	if(mysqli_num_rows($Recordings) > 0)
	{
		While($row = mysqli_fetch_assoc($Recordings))
		{
			if($row['Recording_Set_To_End'] <= date('Y-m-d H:i:s'))
			{
				echo $row['Recording_Set_To_End'];
				SetRecordingStatusToStop($row['id']);
			}else
			{
				echo $row['Recording_Set_To_End'];
			}
		}
	}
}

function SetRunningRecordsToStop()
{
	$Recordings = RecordingsReportingRunningStatus();	
	if(mysqli_num_rows($Recordings) > 0)
	{
		While($row = mysqli_fetch_assoc($Recordings))
		{
			SetRecordingStatusToStop($row['id']);
		}
	}
	
}

?>