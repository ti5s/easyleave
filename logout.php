<?php

//logout.php
$redirectUrl = 'index.php';

session_start();

if(isset($_SESSION['employee_id'])){
	$redirectUrl = 'employee_login.php';
}

session_destroy();

header("location:".$redirectUrl."");

?>