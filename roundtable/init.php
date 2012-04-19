<?php
//header("x-frame-options: SAMEORIGIN"); 

if (isset($scope_perms) && $scope_perms != "")
	$perms = $scope_perms;
else
	$perms = 'email,publish_actions';

if (isset($connect_redirect) && $connect_redirect != "")
	$redirect = $connect_redirect;
else
	$redirect = "";
$GLOBALS['connect_url'] = '//www.facebook.com/dialog/oauth?client_id='.$GLOBALS['app_id'].'&redirect_uri=http:'.$GLOBALS['fb_path'].'/'.$redirect.'&scope='.$perms;


$facebook = new Facebook(array(
  'appId'  => $GLOBALS['api_key'],
  'secret' => $GLOBALS['secret_key'],
  'cookie' => true, // enable optional cookie support
));
## Run global functions


$facebook -> getAccessToken();
$user_id = $facebook -> getUser();

if ($user_id )
	{
  	try 
		{
		$user_profile = $facebook->api('/me');
		} 
	catch (FacebookApiException $e) 
		{
		$user_id = false;
		}
	}

//print_r($user_profile);

if (!$user_id )
	{
	$has_session = false;
	echo('<script type="text/javascript">top.location = \''.$GLOBALS['connect_url'].'\'</script>');
	}
else
	{
	$has_session = true;
	RegisterUser($facebook,$user_id);
	GetLocale($user_id);
	$_SESSION['locale'] = "he_IL";
	}


if (isset($scope_perms) && $scope_perms != "")
	RequirePerms($perms);
$query = "select perms,status from users where user_id = '".$user_id."'";
$result = mysql_query_alert($query);
while ($vresult = mysql_fetch_array($result))
	{
	if ($vresult['perms'] != "")
		$_SESSION['perms'] = $vresult['perms'];	
	$_SESSION['status'] = $vresult['status'];
	}
	
$_SESSION['user_id'] = $user_id;


if (!$_SESSION['mail_reg'])
	{
	$fql = ("select email from user where uid = '".$user_id."'");
	$param = array('method' => 'fql.query','query' => $fql,'callback' => '' );
	$data   = GraphFQL($param);
	//print_r($data);
	if (is_array($data))
		{
		$query = "update users set email = '".$data[0]['email']."' where user_id = '".$user_id."'";
		mysql_query_alert($query);
		$_SESSION['mail_reg'] = true;
		}
	}



//echo $_SESSION['locale'].'<br />';

## Site properties globals



?>