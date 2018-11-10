<?php
include '/var/www/html/DBClass.php';
include '/var/www/html/DayspringMen/BuildRecordingSchedule.php';
include '/var/www/html/DayspringMen/AutoRecord.php';
include '/var/www/html/DayspringMen/BackgroundChecks.php';
//this is where we can automatically do something based on facters in this script.  This script is ran every 5 minutes using crontab
AutoAddServiceToRecord(); //Only at 1:30 AM and 1:20 PM
KillRougeRecordings(); //Runs every minute
ShouldIBeRecording(); //Runs every minute
SetAutoRecordsToStopAfterDuration(); //Runs every minute
OutOfSyncErrors(); //Runs at the top of every hour
ProcessStoppedFiles(); //Runs every minute
?>