<?php
ob_start();
if(isset($_GET['id']))
{
	include 'AutoRecord.php';
	include '../DBClass.php';
	StopRecording();
}
header('location: index.php');
?>