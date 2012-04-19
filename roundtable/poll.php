<?php 
require('dbc.php');
require('functions.php');
require('init_mini.php');

$tid = GetFromGet('tid');
$sid = GetFromGet('sid');
$return = "";
$return .= "{";

if ($has_session)
{
$query = "select sid,eid,data,user_id from live where tid = '".$tid."' AND sid > ".$sid;
$result = mysql_query_alert($query);
if (mysql_affected_rows() > 0)
	{
	$return .= '"events": [';
	while ($vresult = mysql_fetch_array($result))
		{
		$return .=   '{"sid" : "'.$vresult['sid'].'" , "eid" : "'.$vresult['eid'].'" , "data" : "'.addslashes(str_replace("\n","",$vresult['data'])).'", "user_id" : "'.$vresult['user_id'].'"} ,';
		if ($vresult['sid'] > $sid)
			$sid = $vresult['sid'];
		}
	$return = chop($return,",");
	$return .= '], "sid": {"latest": "'.$sid.'"} }';
	}
else
	$return = '{ "sid": {"latest": "'.$sid.'"} }';
echo $return;
}
else
{
return(false);	
}
?>

