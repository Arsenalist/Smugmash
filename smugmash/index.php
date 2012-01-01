<?php
require_once( "phpSmug/phpSmug.php" );


session_start();


echo '<pre>';
//var_dump($_SESSION);
echo '</pre>';
$has_token = isset($_SESSION['token']);
$has_youtube_session = isset($_SESSION['sessionToken']);

if ($has_token) {
    $f = new phpSmug("APIKey=zuos1B2jSMrWQcSnHKAetCj69RCBUTSy", "AppName=Carousel", "OAuthSecret=5848bbe7ddd01af294e4d128ab57a5ec");
    $token = $_SESSION['token'];
    $f->setToken( "id={$token['Token']['id']}", "Secret={$token['Token']['Secret']}" );
}

$album = '';
if (isset($_SESSION['album'])) {
	$album = $_SESSION['album'];
}


?>
<html>
<head>
<title>Smugmash - by Zarar Siddiqi</title>
<script language="javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"></script>
<style type="text/css">
body {
	background: black;
	color: white;
	font-size: 24px;
}
#top {
  font-family: helvetica;
  border-bottom: 1px solid #ccc;
  font-size: 24px;
}

a, a:visited {
	font-size: 24px;
   color: white;
}

.section {
  margin: 20px 0 ;
}

.container {
}

.submit {
padding: 5px 15px;
background: #222;
color: #ccc;
font-size: 20px;
border: 1px solid #ddd;
}
.submit:hover {
  color: #eee;
  border-color: #fff;
}

.error {
   color: red;
}
.message {
   color: green;
}
.contact {
   font-size: 10px;
   color: #ddd;
}
</style>



<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-20492401-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>


<meta name="google-site-verification" content="S8Clp7CcZw6DkOJJ5-tn-52iCuGEpK-pqhsbWu1ZIaE" />
</head>
<body>
<div id="top">
<h1>Smugmash - Turn your SmugMug galleries into YouTube videos</h1>
</div>

<?php if (isset($_REQUEST['message'])) {?>
<div class="message"><?php echo $_REQUEST['message'];?></div>
<?php } ?>

<?php if (isset($_REQUEST['error'])) {?>
<div class="error"><?php echo $_REQUEST['error'];?></div>
<?php } ?>

<div class="container">
<form method="POST" action="done.php" id="fullform">

<div class="section">
<?php if ($has_token) {

	// get albums
	$albums = $f->albums_get( 'Heavy=True' );
	?>
	Step 1: SmugMug Authorized!
	<select name="album" id="album">
		<option value="">All Public Albums</option>
	<?php foreach ( $albums as $a ) { ?>
		<option <?php echo $album == $a['id'] ? 'selected' : '';?> key="<?php echo $a['Key'];?>" passworded="<?php echo $a['Passworded'] ? 'true' : 'false';?>" value="<?php echo $a['id'];?>"><?php echo $a['Title'];?></option>
	<?php } ?>
	</select>
	<span id="password" style="display:none">
	This album as a password: <input type="password" name="password" value="<?php echo isset($_SESSION['pswd']) ? $_SESSION['pswd'] : ''; ?>"/>
	</span>
	<input id="key" type="hidden" name="key"/>

<?php } else { ?>
	Step 1: <a href="authorize-smugmug.php">Authorize with SmugMug</a>
<?php } ?>
</div>
<div class="section">
<?php if ($has_youtube_session) { ?>
	Step 2: YouTube Account Authorized!
<?php } else { ?>
	Step 2: <a href="authorize-youtube.php" id="youtube">Authorize with YouTube</a>
<?php } ?>
</div>
<div class="section">
<?php if ($has_token && $has_youtube_session) { ?>
Step 3 Enter an email to be notified when video is ready (optional): <input type="text" name="email" value=""/>
<p>
<input type="submit" id="submit" class="submit" value="Submit"/>
</p>


<?php } ?>

</div>
</form>


</div>
<script>
$.ajaxSetup({
  async: false
});
    $("select").change(function () {
          var str = "";
          $("select option:selected").each(function () {
                if ($(this).attr('passworded') == 'true') {
                	$('#password').show();
                } else {
                	$('#password').hide();
                }
                $('#key').val($(this).attr('key'));
				$.post("ajax-controller.php", { action: 'save-album', album: $('#album').val() } );
              });
        })
        .trigger('change');


	$('#youtube').click(function(){
	    $.post("ajax-controller.php", { action: 'save-password', pswd: $('#password > input').val() } );

	}).trigger('click');
</script>

<div class="contact">Contact: @zararsiddiqi</div>

</body>



</html>
