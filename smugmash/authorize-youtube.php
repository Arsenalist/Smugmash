<?php
ob_start();
session_start();

// Dev Key: AI39si6u1BZQQilPQ8Oa4qAKSnREMRWuwTOxndlTVmquAaabeNwgDoBXaQTiE7IoUU_8FoEB_iJ8fHRTCx4FvjdcfPYYT9-WYA
// Client ID: ytapi-ZararSiddiqi-DeveloperKey-7h546r09-0

set_include_path(get_include_path() . PATH_SEPARATOR . '/home1/raptorsr/tools/ZendGdata-1.11.1/library');
require_once( "ezsql/ezsql.php" );
require_once 'Zend/Loader.php'; // the Zend dir must be in your include_path
Zend_Loader::loadClass('Zend_Gdata_YouTube');
$yt = new Zend_Gdata_YouTube();

Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');



if (isset($_REQUEST['token'])) {
	$_SESSION['youtube_token'] = $_GET['token'];
	getAuthSubHttpClient();
	$db->query($db->prepare('UPDATE requests SET youtube_session_token = %s WHERE session_id = %s', $_SESSION['sessionToken'], session_id()));
	header("Location: index.php");

} else {
    header("Location: " . getAuthSubRequestUrl());
}


function getAuthSubRequestUrl() {

 //   $next = 'https://secure.bluehost.com/~raptorsr/smugmash/authorize-youtube.php';


    $next = 'http://smugmash.arsenalist.com/authorize-youtube.php';
    $scope = 'http://gdata.youtube.com';
    $secure = false;
    $session = true;
    return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
}

function getAuthSubHttpClient() {
    if (!isset($_SESSION['sessionToken']) && !isset($_GET['token']) ){
        echo '<a href="' . getAuthSubRequestUrl() . '">Login!</a>';
        return;
    } else if (!isset($_SESSION['sessionToken']) && isset($_GET['token'])) {
      $_SESSION['sessionToken'] = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
    }

    $httpClient = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);
    return $httpClient;
}




?>
