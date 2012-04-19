<?php
//header("x-frame-options: SAMEORIGIN"); 

//$perms = 'user_photos,publish_stream';
$GLOBALS['connect_url'] = '//www.facebook.com/dialog/oauth?client_id='.$GLOBALS['app_id'].'&redirect_uri=http:'.$GLOBALS['fb_path'];


$facebook = new Facebook(array(
  'appId'  => $GLOBALS['api_key'],
  'secret' => $GLOBALS['secret_key'],
  'cookie' => true, // enable optional cookie support
));
## Run global functions


$facebook -> getAccessToken();
$user_id = $facebook -> getUser();
//echo ' haz '.$user_id.'<br />';

if (!$user_id )
	{
	$has_session = false;
	}
else
	{
	$has_session = true;
	GetLocale($user_id);
	}


//$has_session = RequirePerms($perms);

$_SESSION['user_id'] = $user_id;


//echo $_SESSION['locale'].'<br />';

## Site properties globals



?>