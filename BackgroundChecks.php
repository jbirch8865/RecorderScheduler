<?php
function DebuggingLoggingStatic($Input,$Output = '',$Type = '2')
{
	$Link = new MySQLLink('Dayspring');
	$Link->AddToSyslog($Input, $Output, $Type);
}

function GetTimeToRender($id)
{
	$Link = new MySQLLink('Dayspring');
	$ServicesToRecord = $Link->ExecuteSQLQuery("SELECT TimeToRender FROM Services_To_Record WHERE id = '$id'");
	if(mysqli_num_rows($ServicesToRecord) == 1)
	{
		$row = mysqli_fetch_array($ServicesToRecord);
		return $row['TimeToRender'];
	}else
	{
		return 0;
	}
}

function RecordingsReportingStoppedStatus()
{
	$Link = new MySQLLink('Dayspring');
	$ServicesToRecord = $Link->ExecuteSQLQuery("SELECT * FROM Services_To_Record WHERE RecordingStatus = '3'");
	return $ServicesToRecord;
}

function RecordingsReportingRenderingStatus()
{
	$Link = new MySQLLink('Dayspring');
	$ServicesToRecord = $Link->ExecuteSQLQuery("SELECT * FROM Services_To_Record WHERE RecordingStatus = '7'");
	return $ServicesToRecord;
}

function DBFiles()
{
	$DBFiles = array();
	if(!$RecordingsReporting = AllRecordingsReportingExceptErrors()){return false;}
	While($row = mysqli_fetch_assoc($RecordingsReporting))
	{
		$DBFiles[$row['id'].'.wav'] = array('InFileSystem' => false, 'RecordingStatus' => $row['RecordingStatus']);
	}
	return $DBFiles;
}

function RealFiles()
{
	$RealFiles = array();
	if(!$Recordings = GetCurrentFileState()){return false;}
	ForEach($Recordings as $File => $FileInfo)
	{
		$RealFiles[$FileInfo['FileName']] = array('FileSize' => $FileInfo['FileSize'], 'InDB' => false);
	}	
	return $RealFiles;
}

function AreAllFilesInDB()
{
	$RealFiles = IterateRealFiles();
	ForEach($RealFiles as $File => $FileInfo)
	{
		if(!$FileInfo['InDB'])
		{
			return false;
		}
	}
	return true;
}

function IterateRealFiles()
{
	$DBFiles = DBFiles();
	$RealFiles = RealFiles();
	ForEach($RealFiles as $File => $FileInfo)
	{
		if(isset($DBFiles[$File]))
		{
			
			$RealFiles[$File]['InDB'] = true;
		}else
		{	
			
			$RealFiles[$File]['InDB'] = false;
		}
	}
	return $RealFiles;
}

function AreAllDBFilesinFileSystem()
{
	$DBFiles = IterateDBFiles();
	ForEach($DBFiles as $row => $data)
	{
		if(!$data['InFileSystem'])
		{
			return false;
		}
	}
	return true;
}

function IterateDBFiles()
{
	$DBFiles = DBFiles();
	$RealFiles = RealFiles();
	ForEach($DBFiles as $row => $data)
	{
		if(isset($RealFiles[$row]))
		{
			
			$DBFiles[$row]['InFileSystem'] = true;
		}elseif($data['RecordingStatus'] == 1)
		{
			$DBFiles[$row]['InFileSystem'] = true;  //hate this cause it's not true but for all intensive purposes I want it to process like the file is present because it isn't supposed to be present
		}else
		{	
			
			$DBFiles[$row]['InFileSystem'] = false;
		}
	}
	return $DBFiles;
}

function OutOfSyncErrors()
{
	if(date('i') == '00' || true)
	{
		if(AreAllFilesInDB() && AreAllDBFilesinFileSystem())
		{
			return true;
		}else
		{
			if(!AreAllFilesInDB())
			{
				DeleteRealFilesMissingInDB();
			}elseif(!AreAllDBFilesinFileSystem())
			{
				ForFilesInDBSetStatusToErrorIfMissingRealFile();
			}
		}
	}
	
}

function ForFilesInDBSetStatusToErrorIfMissingRealFile()
{
	$DBFiles = IterateDBFiles();
	ForEach($DBFiles as $row => $data)
	{
		if(!$data['InFileSystem'])
		{
			$name = explode('.',$row);
			SetRecordingStatusToError($name[0]);
		}
	}
}

function ProcessStoppedFiles()
{
	$StoppedFiles = RecordingsReportingStoppedStatus();
	While($StoppedFile = mysqli_fetch_assoc($StoppedFiles))
	{
		SetRecordingStatusToRendering($StoppedFile['id']);
		StartRenderingFile($StoppedFile['id'].".wav");
	}
}

function StartRenderingFile($FileName)
{
	RemoveDeadNoise($FileName);
}

function ProcessRenderingFiles()
{
	$RenderingFiles = RecordingsReportingRenderingStatus();
	While($RenderingFiles = mysqli_fetch_assoc($RenderingFiles))
	{
		ProcessRenderingFile($RenderingFiles['id']);
	}
}

function ProcessRenderingFile($id)
{
	if(IsFileDoneRendering($id.".wav"))
	{
		SetRecordingStatusToRendered($id);	
	}elseif(FileExceedsProcessingTime($id))
	{
		SetRecordingStatusToError($id);
	}else
	{
		Add1MinuteToRenderingTime($id);
	}
}

function FileExceedsProcessingTime($id)
{
	if(GetTimeToRender($id) > 7)
	{
		return true;
	}else
	{
		return false;
	}
}

function IsFileDoneRendering($FileName)
{
	if(file_exists("/var/www/html/DayspringMen/Recordings/Temp/$FileName.tempReverse.wav"))
	{
		return false;
	}else
	{
		return true;
	}
}

function Add1MinuteToRenderingTime($id)
{
	$Link = new MySQLLink('Dayspring');
	$Link->ExecuteSQLQuery("UPDATE Services_To_Record SET TimeToRender = TimeToRender + 1 WHERE id = '$id'");
}

function DeleteRealFilesMissingInDB()
{
	$Files = IterateRealFiles();
	ForEach($Files as $File => $FileInfo)
	{
		if(!$FileInfo['InDB'])
		{
			echo DeleteFile($File);
		}
	}
}

function DeleteFile($FileName)
{

	$response = unlink('/var/www/html/DayspringMen/Recordings/'.$FileName);
	$Link = new MySQLLink('Dayspring');
	$Link->AddToSyslog("unlinking $FileName",$response);	
}

function RemoveDeadNoise($FileName)
{
	RemoveDeadNoiseBeginning($FileName);
	RemoveDeadNoiseEnd($FileName);
	Add1SecondOfSilenceToFront($FileName);
	Add1SecondOfSilenceToBack($FileName);
	unlink("/var/www/html/DayspringMen/Recordings/Temp/$FileName.tempReverse.wav");
	DebuggingLoggingStatic("Unlinking $FileName.tempReverse.wav");
}

function RemoveDeadNoiseBeginning($FileName)
{
	$response = shell_exec("sudo -u pi sox --temp /var/www/html/DayspringMen/Recordings/Temp /var/www/html/DayspringMen/Recordings/$FileName /var/www/html/DayspringMen/Recordings/Temp/$FileName.tempReverse.wav silence -l 1 0.01 1% reverse 2>&1");
	DebuggingLoggingStatic('Response from RemoveDeadNoiseBeginnning - '.$response);
}

function RemoveDeadNoiseEnd($FileName)
{
	$response = shell_exec("sudo -u pi sox --temp /var/www/html/DayspringMen/Recordings/Temp /var/www/html/DayspringMen/Recordings/Temp/$FileName.tempReverse.wav /var/www/html/DayspringMen/Recordings/$FileName silence -l 1 0.01 1% reverse 2>&1");
	DebuggingLoggingStatic('Response from RemoveDeadNoiseEnd - '.$response);
}

function Add1SecondOfSilenceToFront($FileName)
{
	$response = shell_exec("sudo -u pi sox --temp /var/www/html/DayspringMen/Recordings/Temp --combine concatenate /var/www/html/DayspringMen/Recordings/FilesToKeep/silence.wav /var/www/html/DayspringMen/Recordings/$FileName /var/www/html/DayspringMen/Recordings/temp.wav 2>&1");
	rename("/var/www/html/DayspringMen/Recordings/temp.wav","/var/www/html/DayspringMen/Recordings/$FileName");
	DebuggingLoggingStatic('Response from Add1SecondOfSilenceToFront - '.$response);
	
	
}

function Add1SecondOfSilenceToBack($FileName)
{
	$response = shell_exec("sudo -u pi sox --temp /var/www/html/DayspringMen/Recordings/Temp --combine concatenate /var/www/html/DayspringMen/Recordings/$FileName /var/www/html/DayspringMen/Recordings/FilesToKeep/silence.wav /var/www/html/DayspringMen/Recordings/temp.wav 2>&1");
	rename("/var/www/html/DayspringMen/Recordings/temp.wav","/var/www/html/DayspringMen/Recordings/$FileName");
	DebuggingLoggingStatic('Response from Add1SecondOfSilenceToBack - '.$response);
}

?>