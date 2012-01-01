<?php
ob_start();
session_start();

require_once( "phpSmug/phpSmug.php" );
require_once( "ezsql/ezsql.php" );


try {
	$f = new phpSmug("APIKey=zuos1B2jSMrWQcSnHKAetCj69RCBUTSy", "AppName=Carousel", "OAuthSecret=5848bbe7ddd01af294e4d128ab57a5ec");
	if ( ! isset( $_SESSION['SmugGalReqToken'] ) ) {
		// Step 1: Get a Request Token
		$d = $f->auth_getRequestToken();
		$_SESSION['SmugGalReqToken'] = serialize( $d );
		header("Location: " . $f->authorize());
	} else {
		$reqToken = unserialize( $_SESSION['SmugGalReqToken'] );
		// Step 3: Use the Request token obtained in step 1 to get an access token
		$f->setToken("id={$reqToken['Token']['id']}", "Secret={$reqToken['Token']['Secret']}");
		$token = $f->auth_getAccessToken();	// The results of this call is what your application needs to store.
        $_SESSION['token'] = $token;

        $db->query($db->prepare('INSERT INTO requests (session_id, smugmug_token, smugmug_secret) VALUES(%s, %s, %s)', session_id(), $token['Token']['id'], $token['Token']['Secret']));


        header("Location: index.php");
	}
}
catch ( Exception $e ) {
//	echo "{$e->getMessage()} (Error Code: {$e->getCode()})";

        $_SESSION = array();
        // sends as Set-Cookie to invalidate the session cookie
        if (isset($_COOKIES[session_name()])) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', 1, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
        }
        session_regenerate_id();
        header("Location: index.php?error=Something went wrong, please try that again.");



}
?>
