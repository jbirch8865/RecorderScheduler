<?php
ob_start();
if(isset($_GET['id']))
{
	include 'AutoRecord.php';
	include '../DBClass.php';
	SetRecordingStatusToError($_GET['id']);
}
header('location: index.php');
?>