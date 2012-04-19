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
	$topic = GetFromGet('topic');
	$query = "insert into suggest values ('".$topic."','".$user_id."');";
	if (mysql_query_alert($query))
		echo "OK";
	else
		echo "FAILED";
	}
else
	echo "FAILED";
?>

