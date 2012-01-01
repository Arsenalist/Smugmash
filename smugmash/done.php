<?php
ob_start();
session_start();

//error_reporting(E_ERROR | E_PARSE);
require_once( "ezsql/ezsql.php" );


	$db->query($db->prepare('UPDATE requests SET smugmug_album_id = %s, smugmug_album_key = %s, smugmug_album_password = %s, email = %s, status = "notstarted" WHERE session_id = %s', $_REQUEST['album'], $_REQUEST['key'], $_REQUEST['password'], $_REQUEST['email'], session_id()));


    $id = $db->get_var($db->prepare('SELECT id FROM requests WHERE session_id = %s', session_id()));

	if ($_REQUEST['album'] != '') {
		require_once( "phpSmug/phpSmug.php" );


		try {
			if (isset($_REQUEST['password']) && $_REQUEST['password'] != '') {
				$f = new phpSmug("APIKey=zuos1B2jSMrWQcSnHKAetCj69RCBUTSy", "AppName=Carousel", "OAuthSecret=5848bbe7ddd01af294e4d128ab57a5ec");
				$token = $_SESSION['token'];
				$f->setToken( "id={$token['Token']['id']}", "Secret={$token['Token']['Secret']}" );
				$args = array("AlbumID" => $_REQUEST['album'], "AlbumKey" => $_REQUEST['key'], "Password" => $_REQUEST['password']);
				// make call to check password
				$f->images_get($args);
			}
		} catch (Exception $e) {
			header('Location: index.php?error=' . 'Error!!: ' . $e->getMessage());
			exit;
		}
	}

	// $pid = exec("/usr/bin/php -f /home1/raptorsr/public_html/smugmash/work/do.php ".$id . ' > /dev/null &');
	//$db->query($db->prepare('UPDATE requests SET pid = %s WHERE id = %d', $pid, $id));

	$_SESSION = array();
	// sends as Set-Cookie to invalidate the session cookie
	if (isset($_COOKIES[session_name()])) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', 1, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
	}
	session_regenerate_id();
	header('Location: index.php?message=All done! We are now busy making the video.');
?>
