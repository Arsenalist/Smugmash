<?php
if (session_id() == "") { @session_start(); }
$action = $_REQUEST['action'];

if ($action == 'save-album') {
	$_SESSION['album'] = $_REQUEST['album'];
} else if ($action == 'save-password') {
	$_SESSION['pswd'] = $_REQUEST['pswd'];
}

?>