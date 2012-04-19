<?php

function AddConnection($uid,$tid)
	{
	$time = time();
	$query = "replace into connections values ('".$uid."','".$tid."','".$time."');";
	return (mysql_query_alert($query));
	}

function RemConnection($uid,$tid)
	{
	$query = "delete from connections where user_id = '".$uid."' AND tid = '".$tid."';";
	mysql_query_alert($query);
	
	return (true);
	}
	
function UpdatePartiCounters()
	{
	$query = 'select id from tables;';
	$result = mysql_query_alert($query);
	while ($vresult = mysql_fetch_array($result))
		{
		$counters[$vresult['id']] = 0;
		}
		
	$query = "select tid from connections;";
	$result = mysql_query_alert($query);
	while ($vresult = mysql_fetch_array($result))
		{
		$counters[$vresult['tid']] ++;
		}
	if (is_array($counters))
		{
		foreach ($counters as $tid => $count)
			{
			if ($count == "")
				$count = 0;
			$query = "replace into parti_counters values ('".$tid."','".$count."')";
			mysql_query_alert($query);
			}
		}
	}
?>