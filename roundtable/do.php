<?php 
// Event IDs breaddown:
// 1: Join table
// 2: take sit 
// 3: get up from sit
// 5: disconnect from table
// 6: user invited to table
// 10/11: main/side chat message added
// 12: parti asks permission to ask a question
// 13: parti asks permission to comment
// 14: burst talk
// 15: like
// 16: unlike
// 50: table closed 
// 60: leading question changed
// 61: speaker changed
// 62: user kicked
// 63: user banned
// 64: user unbanned
// 71: set user state

require('dbc.php');
require('functions.php');
require('init_mini.php');
require('db_functions.php');

$return = "";


if ($has_session)
{ 
$eid = GetFromGet('eid');
if ($eid == 1)
	{
	$tid = GetFromGet('tid');
	$query = "select id from tables where id = '".$tid."'";
	mysql_query_alert($query);
	if (mysql_affected_rows() > 0)
		{
		$return .= '{ "spots": [';
		$query = "select uid,spot from live_parti where tid ='".$tid."'";
		$result = mysql_query_alert($query);
		while ($vresult = mysql_fetch_array($result))
			{
			$return .=   '{"spot" : "'.$vresult['spot'].'", "uid": "'.$vresult['uid'].'"},';
			}
		$return = chop($return,',');
		$return .= '], "join": {"joined": "OK"} }';
		InsertEvent($tid,1,"",$user_id);
		AddConnection($user_id,$tid);
		}
	else
		$return .= '{"join": {"joined": "FAILED"} }';
	echo $return;
	}
	
if ($eid == 2)
	{
	$data = GetFromGet('data');
	$tid = GetFromGet('tid');
	$return .= '{ "sit": [';
	if ($data != "")
		{
		$query = "select uid from live_parti where tid = '".$tid."' AND spot = '".$data."'";
		mysql_query_alert($query);
		
		if (mysql_affected_rows() == 0)
			{
			$query = "select tid from bans where tid = '".$tid."' AND banned = '".$user_id."'";
			mysql_query_alert($query);
			if (mysql_affected_rows() > 0)
				$return .= '{"spot" : "'.$data.'"}], "state": {"sitting": "BANNED"} }';
			else
				{
				InsertEvent($tid,$eid,$data,$user_id);
				$query = "insert into live_parti values ('".$tid."','".$user_id."','".$data."','".time()."','".getUsername($user_id)."','');";
				mysql_query_alert($query);
				if (mysql_affected_rows() > 0)
					$return .=   '{"spot" : "'.$data.'"} ], "state": {"sitting": "OK"} }';
				else
					$return .= '], "state": {"sitting": "FAILED"} }';
				}
			}
		else
			$return .= '], "state": {"sitting": "FAILED"} }';
		}
	else
		$return .= '], "state": {"sitting": "FAILED"} }';
		
	echo $return;
	}
	
	
if ($eid == 3)
	{
	$data = GetFromGet('data');
	$tid = GetFromGet('tid');
	if ($data != "")
		{
		if (InsertEvent($tid,$eid,$data,$user_id) == "OK")
			{
			$query = "delete from live_parti where tid = '".$tid."' AND uid = '".$user_id."'";
			mysql_query_alert($query);
			if (mysql_affected_rows() > 0)
				$return = "OK";
			else
				$return = "FAILED";
			}
		else
			$return = "FAILED";
		}
	else
		$return = "FAILED";
		
	echo $return;
	}
	
if ($eid == 5)
	{
	$data = GetFromGet('data');
	$tid = GetFromGet('tid');
	InsertEvent($tid,5,$data,$user_id);
	$query = "delete from connections where user_id = '".$user_id."' AND tid = '".$tid."';";
	mysql_query_alert($query);
	
	if ($data != "" && $data != "CROWD")
		{	
		if (InsertEvent($tid,3,$data,$user_id) == "OK")
			{
			$query = "delete from live_parti where tid = '".$tid."' AND uid = '".$user_id."'";
			mysql_query_alert($query);
			if (mysql_affected_rows() > 0)
				$return = "OK";
			else
				$return = "FAILED";
			}
		else
			$return = "FAILED";
		}
	else
		$return = "OK";
		
	echo $return;

	}
	
if ($eid == 6)
	{
	$data = GetFromGet('data');
	$tid = GetFromGet('tid');
	// secure here 
	if ($data != "")
		{
		$query = "update tables set invites = concat(invites,',".$data."') where id = '".$tid."'";
		if (mysql_query_alert($query))
			{
			$return = InsertEvent($tid,$eid,$data,$user_id);
			}
		else
			$return = "FAILED";
		}
	else	
		$return = "FAILED";
	echo $return;
	}
	
if ($eid >= 10 && $eid <= 14)
	{
	$data = GetFromGet('data');
	$tid = GetFromGet('tid');
	if (stristr($data,GetTrans(51)))
		$data = str_replace(GetTrans(51),"",$data);
	$query = "select tid from bans where tid = '".$tid."' AND banned = '".$user_id."'";
	mysql_query_alert($query);
	if (mysql_affected_rows() > 0)
		$return = "BANNED";
	else
		$return = InsertEvent($tid,$eid,$data,$user_id);
	echo $return;
	}

if ($eid == 15)
	{
	$data = GetFromGet('data');
	$like_data = json_decode(urldecode(stripslashes($data)) );
	$tid = GetFromGet('tid');
	$can_like = true;
	$query = "select data,user_id from live where eid = 15 and tid = '".$tid."'";
	$result = mysql_query_alert($query);
	while ($vresult = mysql_fetch_array($result))
		{
		$db_like_data = json_decode($vresult['data']);
		//echo 'cmpr '. $db_like_data -> {'sid'} .' with '.$like_data -> {'sid'} .' and '.$db_like_data -> {'uid'}.' with '.$like_data -> {'uid'}.'<br />';
		if ($db_like_data -> {'sid'} == $like_data -> {'sid'} && $vresult['user_id'] == $user_id )
			$can_like = false;
		}	
	if ($can_like)
		{
		// add only if not unliked than add score
		AddScore($like_data -> {'uid'},1);
		$return = InsertEvent($tid,$eid,$data,$user_id);
		}
	else
		$return = "FAIL";
	echo $return;
	}

if ($eid == 16)
	{
	$data = GetFromGet('data');
	$tid = GetFromGet('tid');
	$like_data = json_decode(urldecode(stripslashes($data)) );
	$query = "select data,user_id,sid from live where eid = 15 and tid = '".$tid."' and user_id = '".$user_id."'";
	$result = mysql_query_alert($query);
	while ($vresult = mysql_fetch_array($result))
		{
		$db_like_data = json_decode($vresult['data']);
		if ($db_like_data -> {'sid'} == $like_data -> {'sid'} && $vresult['user_id'] == $user_id)
			$sid_to_delete = $vresult['sid'];
		}
	$query = "delete from live where sid = '".$sid_to_delete."' AND tid = '".$tid."'";
	$result = mysql_query_alert($query);
	//delete from stream,archive too?
	//$query = "delete from stream where eid = 15 and tid = '".$tid."' and sid = ".($like_data -> {'sid'});
	//$result = mysql_query_alert($query);
	if (mysql_affected_rows() > 0)
		{
		DecScore($like_data -> {'uid'},1);
		$return = InsertEvent($tid,$eid,$data,$user_id);
		}
	else
		$return = false;
	echo $return;
	}

	
if ($eid == 50)
	{
	$tid = GetFromGet('tid');
	$return = CloseTable($tid);	
	echo $return;
	}
	
if ($eid == 60)
	{
	$tid = GetFromGet('tid');
	$data = GetFromGet('data');
	$query = "select spot from live_parti where tid = '".$tid."'";
	$result = mysql_query_alert($query);
	$vresult = mysql_fetch_row($result);
	if ($vresult['spot'] == 0)
		$return = InsertEvent($tid,$eid,$data,$user_id);
	else
		$return = "FAILED";
	echo $return;
	}

	
if ($eid == 61)
	{
	$tid = GetFromGet('tid');
	$data = GetFromGet('data');
	$query = "select spot from live_parti where tid = '".$tid."'";
	$result = mysql_query_alert($query);
	$vresult = mysql_fetch_row($result);
	if ($vresult['spot'] == 0)
		$return = InsertEvent($tid,$eid,$data,$user_id);
	else
		$return = "FAILED";
	echo $return;
	}

if ($eid == 62)
	{
	$tid = GetFromGet('tid');
	$data = GetFromGet('data');	
	if ($data != "")
		{
		if (InsertEvent($tid,62,$data,$user_id) == "OK")
			{
			$query = "delete from live_parti where tid = '".$tid."' AND spot = '".$data."'";
			mysql_query_alert($query);
			if (mysql_affected_rows() > 0)
				$return = "OK";
			else
				$return = "FAILED";
			}
		else
			$return = "FAILED";
		}
	else
		$return = "FAILED";
		
	echo $return;
	}
	
if ($eid == 63)
	{
	$tid = GetFromGet('tid');
	$data = GetFromGet('data');	
	if ($data != "")
		{
		$query = "insert into bans values ('".$tid."','".$data."','".$user_id."','".time()."');";
		if (mysql_query_alert($query))
			{
			if (InsertEvent($tid,63,$data,$user_id) == "OK")
				$return = "OK";
			else
				$return = "FAILED";
			}
		else
			$return = "FAILED";
		}
	else
		$return = "FAILED";
		
	echo $return;
	}
	
if ($eid == 64)
	{
	$tid = GetFromGet('tid');
	$data = GetFromGet('data');	
	if ($data != "")
		{
		$query = "delete from bans where tid = '".$tid."' AND banned = '".$data."'";
		if (mysql_query_alert($query))
			{
			if (InsertEvent($tid,64,$data,$user_id) == "OK")
				$return = "OK";
			else
				$return = "FAILED";
			}
		else
			$return = "FAILED";
		}
	else
		$return = "FAILED";
		
	echo $return;
	}
	
	
if ($eid == 71)
	{
	$tid = GetFromGet('tid');
	$data = GetFromGet('data');	
	if ($data != "")
		{
		$query = "update users set status = '".$data."' where user_id = '".$user_id."'";
		mysql_query_alert($query);
		}
	else
		$return = "FAILED";
		
	echo $return;
	}

}
else
{
echo "FAIL_NO_SESSION";	
}
?>

