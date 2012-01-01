

<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'C:\tools\ZendGdata-1.11.1\library');
require_once( "ezsql/ezsql.php" );
require_once 'Zend/Loader.php'; // the Zend dir must be in your include_path
Zend_Loader::loadClass('Zend_Gdata_YouTube');

Zend_Loader::loadClass('Zend_Gdata_AuthSub');

uploadToYouTube("C:/Users/zarar/Desktop/f.mp4");

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




function uploadToYouTube($filePath) {

     $httpClient = Zend_Gdata_AuthSub::getHttpClient('1/EmEsNf2sLlu69lmHWDm8soZSefIsa_759fjO-iHAdhY');
	// Note that this example creates an unversioned service object.
	// You do not need to specify a version number to upload content
	// since the upload behavior is the same for all API versions.
	$yt = new Zend_Gdata_YouTube($httpClient, 'Carousel', 'ytapi-ZararSiddiqi-DeveloperKey-7h546r09-0', 'AI39si6u1BZQQilPQ8Oa4qAKSnREMRWuwTOxndlTVmquAaabeNwgDoBXaQTiE7IoUU_8FoEB_iJ8fHRTCx4FvjdcfPYYT9-WYA');

	// create a new VideoEntry object
	$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();

	// create a new Zend_Gdata_App_MediaFileSource object
	$filesource = $yt->newMediaFileSource($filePath);
	$filesource->setContentType('video/x-ms-wmv');
	// set slug header
	$filesource->setSlug('smugmug');

	// add the filesource to the video entry
	$myVideoEntry->setMediaSource($filesource);

	$myVideoEntry->setVideoTitle('My Test Movie');
	$myVideoEntry->setVideoDescription('My Test Movie');
	// The category must be a valid YouTube category!
	$myVideoEntry->setVideoCategory('Autos');

	// Set keywords. Please note that this must be a comma-separated string
	// and that individual keywords cannot contain whitespace
	$myVideoEntry->SetVideoTags('cars, funny');

	// set some developer tags -- this is optional
	// (see Searching by Developer Tags for more details)
	$myVideoEntry->setVideoDeveloperTags(array('mydevtag', 'anotherdevtag'));

	// set the video's location -- this is also optional
	$yt->registerPackage('Zend_Gdata_Geo');
	$yt->registerPackage('Zend_Gdata_Geo_Extension');
	$where = $yt->newGeoRssWhere();
	$position = $yt->newGmlPos('37.0 -122.0');
	$where->point = $yt->newGmlPoint($position);
	$myVideoEntry->setWhere($where);

	// upload URI for the currently authenticated user
	$uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';

	// try to upload the video, catching a Zend_Gdata_App_HttpException,
	// if available, or just a regular Zend_Gdata_App_Exception otherwise
	try {
	  $newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
	} catch (Zend_Gdata_App_HttpException $httpException) {
	  echo $httpException->getRawResponseBody();
	} catch (Zend_Gdata_App_Exception $e) {
		echo $e->getMessage();
    }
}

?>