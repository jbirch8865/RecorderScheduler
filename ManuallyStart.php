<?php
ob_start();
if(isset($_GET['id']) && isset($_GET['Duration']))
{
	include 'AutoRecord.php';
	include '../DBClass.php';
	StartRecording($_GET['Duration'], $_GET['id']);
}
header('location: index.php');
?>