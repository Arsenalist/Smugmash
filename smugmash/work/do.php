<?php

if ($argc < 2) {
echo "Supply request id\n";
exit;
}

$request_id = $argv[1];
require_once( "../ezsql/ezsql.php" );

set_include_path(get_include_path() . PATH_SEPARATOR . '/home1/raptorsr/tools/ZendGdata-1.11.1/library');
require_once 'Zend/Loader.php'; // the Zend dir must be in your include_path
Zend_Loader::loadClass('Zend_Gdata_YouTube');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

try {

$request = $db->get_row("select * from requests where id = ". $request_id);
$db->query($db->prepare("update requests set status = 'started' where id = %d", $request->id));



    require_once( "../phpSmug/phpSmug.php" );
    $f = new phpSmug("APIKey=zuos1B2jSMrWQcSnHKAetCj69RCBUTSy", "AppName=Carousel", "OAuthSecret=5848bbe7ddd01af294e4d128ab57a5ec");
    $f->setToken( "id={$request->smugmug_token}", "Secret={$request->smugmug_secret}" );

$image_urls = array();
if ($request->smugmug_album_id == '') {
    $video_title = 'My Smugmash';
    $albums = $f->albums_get(array( "Heavy" => "1"));    
    foreach ($albums as $album) {
       if ($album['Passworded'] == false) {
          echo $album['Title'] . " is eligible to be downloaded\n";
          $args = array("AlbumID" => $album['id'], "AlbumKey" => $album['Key'], "Heavy" => "1");
          $albumImages = $f->images_get($args);
          $albumImages = $albumImages['Images'];
          echo "Image count = " . count($albumImages);
          if ($albumImages != NULL) {
	          foreach ($albumImages as $ai) {
		     $image_urls[] = $ai['SmallURL'];
          	}
	  }
       }
    }
} else {
		$args = array("AlbumID" => $request->smugmug_album_id, "AlbumKey" => $request->smugmug_album_key, "Heavy" => "1");
		if ($request->smugmug_album_password != '') {
		   $args['Password'] = $request->smugmug_album_password;
		}
		$images = $f->images_get($args);


		foreach ( $images['Images'] as $image ) {
			$image_urls[] = $image['SmallURL'];
		}

                        $args = array("AlbumID" => $request->smugmug_album_id, "AlbumKey" => $request->smugmug_album_key);
                        if ($request->smugmug_album_password != '') {
                           $args['Password'] = $request->smugmug_album_password;
			   $video_title = "My Smugmash";
                        } else {
				// bug
				$album_object = $f->albums_getInfo($args);
				$video_title = "My Smugmash - " . $album_object['Title'];

			}



}
$max_images = 1000;
if (count($image_urls) > $max_images) {
 // array_splice($image_urls, $max_images);
}


$db->query($db->prepare('update requests set num_images = %d where id = %d', count($image_urls), $request->id));

echo "Downloading images ...\n";
downloadImages($request->session_id, $image_urls);
echo "Creating video...\n";
$video_file = createVideo($request->session_id);
echo "Uploading to YouTube...\n";
$video_url = uploadToYoutube($request->youtube_session_token, $video_file, $video_title);
echo "Updating status and deleting images from db...\n";
$db->query($db->prepare('update requests set status = "done", video_url = %s where id = %d', $video_url, $request->id));
$db->query($db->prepare('delete from images where id = %d', $request->id));
echo "Removing images files...\n";
exec('rm -f ' . $request->session_id . '_*.jpg');
echo "Done.\n";
if (trim($request->email) != '') {
  $headers = 'From: Smugmash <zarars@gmail.com>' . "\r\n";
echo "Sending notification email...\n";
  mail ($request->email , 'Smugmash is ready!' , $video_url, $headers);
echo "Done\n";
}
} catch (Exception $e) {
   //var_dump($e);
   $db->query($db->prepare('update requests set status = "failed" , status_text = %s where id = %d', $e->getMessage(), $request->id));
}

function createVideo($sessionId) {
 
  exec('/home1/raptorsr/tools/ffmpeg-0.6.1/mytmp/usr/local/bin/ffmpeg  -y -s 480x360  -f image2 -r 4 -i ' . $sessionId . '_%05d.jpg -i audio.mp3  -shortest -acodec copy -mbd rd -flags +mv4+aic -trellis 2 -cmp 2 -subcmp 2 -g 300 -pass 1/2 ' . $sessionId . '.mp4');

  return $sessionId . '.mp4';
}

function downloadImages($sessionId, $results){
        $i=0;
        foreach ($results as $im) {
           $i++;
           // echo "Downloading: "  . $im->image_url . "\n";
           $fileName = $sessionId . '_' . str_pad($i, 5, "0", STR_PAD_LEFT) . '.jpg';
           echo 'File name to be converted is ' . $fileName . "\n";
           try {
              copy($im, $fileName);
		exec('convert ' . $fileName . ' -resize 480x360 -background black -compose Copy -gravity center -extent 480x360 -quality 92 ' . $fileName);
           } catch (Exception $e) {
              $i--;
              echo 'Caught exception: ',  $e->getMessage(), "\n";
           }
        }

}

function uploadToYoutube($youtubeSessionToken, $filePath, $video_title) {
        echo "Sending $filePath to YouTube using token '$youtubSessionToken'\n";
        $httpClient = Zend_Gdata_AuthSub::getHttpClient($youtubeSessionToken);
        echo "Created HTTP Client for YouTube\n";
	// Note that this example creates an unversioned service object.
	// You do not need to specify a version number to upload content
	// since the upload behavior is the same for all API versions.
	$yt = new Zend_Gdata_YouTube($httpClient, 'Carousel', 'ytapi-ZararSiddiqi-DeveloperKey-7h546r09-0', 'AI39si6u1BZQQilPQ8Oa4qAKSnREMRWuwTOxndlTVmquAaabeNwgDoBXaQTiE7IoUU_8FoEB_iJ8fHRTCx4FvjdcfPYYT9-WYA');
        echo "Created YT object\n";
	// create a new VideoEntry object
	$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();

	// create a new Zend_Gdata_App_MediaFileSource object
	$filesource = $yt->newMediaFileSource($filePath);
	$filesource->setContentType('video/mp4');
	// set slug header
	$filesource->setSlug('smugmug');

	// add the filesource to the video entry
	$myVideoEntry->setMediaSource($filesource);

	$myVideoEntry->setVideoTitle($video_title);
	$myVideoEntry->setVideoDescription('My Smugmash - created at http://smugmash.arsenalist.com');
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
//	try {
	  $newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
          return $newEntry->getVideoWatchPageUrl();
//	} catch (Zend_Gdata_App_HttpException $httpException) {
//	  echo $httpException->getRawResponseBody();
//	} catch (Zend_Gdata_App_Exception $e) {
//		echo $e->getMessage();
//    }
}


?>
