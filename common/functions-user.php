<?php

function RequirePerms($perms, $uid = "me()")
	{
	if ($perms != "")
		{
		$missing = false;
		$fql = "select ".$perms." from permissions where uid = ".$uid;
		$param = array('method' => 'fql.query','query' => $fql,'callback' => '' );
		$perms = GraphFQL($param);
	//	print_r($perms);
		foreach ($perms[0] as $perm => $has)
			{
		//	echo $perm .' und '.$has.'<br />';
			if ($has == 0)
				{
				$missing = true;
				$scope .= $perm.',';
				}
			}
		$scope = chop($scope,',');
		if ($missing)
			{
			echo('<script type="text/javascript">top.location = \'//www.facebook.com/dialog/oauth?client_id='.$GLOBALS['app_id'].'&redirect_uri=http:'.$GLOBALS['fb_path'].'&scope='.$scope.'\';</script>');
			return(false);
			}
		else
			return(true);
		}
	else
		return(false);
	
	}
	
function UnRegisterUser($user_id)
{
if (isset($user_id) && $user_id != '')
	{
	$query = "insert into removed_users select * from users where user_id = '".$user_id."';";
	mysql_query_alert($query);
	
	$query = "delete from users where user_id = '".$user_id."';";
	
	mysql_query_alert($query);
	}
return(true);
}

function RegisterUser($facebook,$user_id)
{	

if (isset($user_id) && $user_id != '')
	{
	
	$query = "select user_id from users where user_id = '".$user_id."'";
	mysql_query_alert($query);
	if (mysql_affected_rows() == 0)
		{
			//echo "select first_name,last_name from user where uid = '".$user_id."'<br />;
		$fql = ("select first_name,last_name,locale from user where uid = '".$user_id."'");
		$param = array('method' => 'fql.query','query' => $fql,'callback' => '' );
		$user_info = GraphFQL($param);
		//$facebook->api_client-> users_getInfo($user_id,'last_name, first_name,username,locale');
		if (is_array($user_info))
			{
			$query = "insert into users values ($user_id,'','".ConvertQs($user_info[0]['first_name'])."','".ConvertQs($user_info[0]['last_name'])."','".$_GET['fb_sig_locale']."',".time().",".time().",'','','".$_GET['s']."',0,'".$_GET['ref']."',0,1,'".$_GET['ref']."');";
			mysql_query_alert($query,array(1062));
			}
		else
			{
			$query = "insert into users values ($user_id,'','','','',".time().",".time().",'','','".$_GET['s']."',0,'".$_GET['ref']."',0,1,'".$_GET['ref']."');";
			mysql_query_alert($query,array(1062));
			}
		$query = "delete from removed_users where user_id = '".$user_id."';";
		mysql_query_alert($query);
		
		}
	else
		{
		if (isset($_GET['ref']) != '' && isset($_GET['ref']))
			$query = "update users set lastlogin = ".time().",lastref = '".$_GET['ref']."' where user_id = '".$user_id."'";
		else
			$query = "update users set lastlogin = ".time()." where user_id = '".$user_id."'";
		mysql_query_alert($query);
		}
	
	@mysql_select_db('top');	
	$query = "insert ignore into uids values ($user_id);";
	mysql_query_alert($query,array(1062));
	
	if (isset($GLOBALS['database']) && $GLOBALS['database'] != '')
		$db = $GLOBALS['database'];
	else
		$db = $database;
	
	@mysql_select_db($db);
	}
return(true);
}

function RegisterUserGraph($facebook,$user_id)
{	
	if (isset($user_id) && $user_id != '')
	{
		$query = "select user_id from users where user_id = '".$user_id."'";
		mysql_query_alert($query);
		if (mysql_affected_rows() == 0)
		{
			//echo "select first_name,last_name from user where uid = '".$user_id."'<br />;
			$fql= "select first_name,last_name,locale from user where uid = '".$user_id."'";
			$param = array('method' => 'fql.query','query' => $fql,'callback' => '' );
			$user_info = GraphFQL($param);
			//$facebook->api_client-> users_getInfo($user_id,'last_name, first_name,username,locale');
			if (is_array($user_info))
			{
				$query = "insert into users values ($user_id,'','".ConvertQs($user_info[0]['first_name'])."','".ConvertQs($user_info[0]['last_name'])."','".$_REQUEST['fb_sig_locale']."',".time().",".time().",'','','',0,'".$_GET['ref']."',0,1,'".$_REQUEST."');";
				mysql_query_alert($query,array(1062));
			}else{
				$query = "insert into users values ($user_id,'','','','',".time().",".time().",'','','',0,'".$_REQUEST['ref']."',0,1,'".$_GET['ref']."');";
				mysql_query_alert($query,array(1062));
			}
			$query = "delete from removed_users where user_id = '".$user_id."';";
			mysql_query_alert($query);
			
		}else{
			if (isset($_GET['ref']) != '' && isset($_GET['ref']))
				$query = "update users set lastlogin = ".time().",lastref = '".$_GET['ref']."' where user_id = '".$user_id."'";
			else
				$query = "update users set lastlogin = ".time()." where user_id = '".$user_id."'";
			mysql_query_alert($query);
		}
		
		@mysql_select_db('top');	
		$query = "insert ignore into uids values ($user_id);";
		mysql_query_alert($query,array(1062));
		
		if (isset($GLOBALS['database']) && $GLOBALS['database'] != '')
			$db = $GLOBALS['database'];
		else
			$db = $database;
		
		@mysql_select_db($db);
	}
	return(true);
}

?>