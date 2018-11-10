<?php
include '/var/www/html/DayspringMen/AutoRecord.php';
$Recording = isRecording();
if($Recording)
{
	echo 'True';
}else
{
	echo 'False';
}
?>