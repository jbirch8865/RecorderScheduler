<?php
function DebuggingLoggingStatic($Input,$Output = '',$Type = '2')
{
	$Link = new MySQLLink('Dayspring');
	$Link->AddToSyslog($Input, $Output, $Type);
}
function RecordingsReportingStoppedStatus()
{
	$Link = new MySQLLink('Dayspring');
	$ServicesToRecord = $Link->ExecuteSQLQuery("SELECT * FROM Services_To_Record WHERE RecordingStatus = '3'");
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
		RemoveDeadNoise($StoppedFile['id'].".wav");
		SetRecordingStatusToRendered($StoppedFile['id']);
	}
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
}

function RemoveDeadNoiseBeginning($FileName)
{
	$response = shell_exec("sudo -u pi sox /var/www/html/DayspringMen/Recordings/$FileName /var/www/html/DayspringMen/Recordings/tempReverse.wav silence -l 1 0.01 1% reverse 2>&1");
	DebuggingLoggingStatic('Response from RemoveDeadNoiseBeginnning - '.$response);
	
	
}

function RemoveDeadNoiseEnd($FileName)
{
	$response = shell_exec("sudo -u pi sox /var/www/html/DayspringMen/Recordings/tempReverse.wav /var/www/html/DayspringMen/Recordings/$FileName silence -l 1 0.01 1% reverse 2>&1");
	DebuggingLoggingStatic('Response from RemoveDeadNoiseEnd - '.$response);
}

function Add1SecondOfSilenceToFront($FileName)
{
	$response = shell_exec("sudo -u pi sox --combine concatenate /var/www/html/DayspringMen/Recordings/FilesToKeep/silence.wav /var/www/html/DayspringMen/Recordings/$FileName /var/www/html/DayspringMen/Recordings/temp.wav 2>&1");
	rename("/var/www/html/DayspringMen/Recordings/temp.wav","/var/www/html/DayspringMen/Recordings/$FileName");
	DebuggingLoggingStatic('Response from Add1SecondOfSilenceToFront - '.$response);
	
	
}

function Add1SecondOfSilenceToBack($FileName)
{
	$response = shell_exec("sudo -u pi sox --combine concatenate /var/www/html/DayspringMen/Recordings/$FileName /var/www/html/DayspringMen/Recordings/FilesToKeep/silence.wav /var/www/html/DayspringMen/Recordings/temp.wav 2>&1");
	rename("/var/www/html/DayspringMen/Recordings/temp.wav","/var/www/html/DayspringMen/Recordings/$FileName");
	DebuggingLoggingStatic('Response from Add1SecondOfSilenceToBack - '.$response);
}

?>