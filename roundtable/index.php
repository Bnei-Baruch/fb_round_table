<?php 
require('dbc.php');
require('functions.php');
require('db_functions.php');
require('init.php');

if (isset($_GET['request_ids']) && $_GET['request_ids'] != "")
	{
	$request_ids = explode(",",$_GET['request_ids']);
	$rid = $request_ids[sizeof($request_ids) - 1];
	$data = GraphAPI($rid."_".$user_id);
	if ($data['data'] != "")
		{
		$query = 'select tid from tables where tid = '.$data['data'].' and started > '.time();
		mysql_query_alert($query);
		if (mysql_affected_rows() > 0)
			echo '<script type="text/javascript">top.location = \''.$GLOBALS['fb_path'].'/table.php?tid='.$data['data'].'\';</script>';
	//	else
	// show table has ended condition
		}
	}

UpdatePartiCounters();

$query = "select fname,lname,points from users where user_id = '".$user_id."'";
$result = mysql_query_alert($query);
while ($vresult = mysql_fetch_array($result))
	{
	$points = $vresult['points'];
	$username = $vresult['fname'] . ' '.$vresult['lname'];
	}
	
$query = "select fname,user_id from users order by points desc limit 0,3";
$result = mysql_query_alert($query);
while ($vresult = mysql_fetch_array($result))
	{
	$leaderboard .= LeaderBox($vresult['user_id'],$vresult['fname']);
	}

$query = "select count,tid from parti_counters;";
$result = mysql_query_alert($query);
$s_count = 0;
while ($vresult = mysql_fetch_array($result))
	{
	$parti_counters[$vresult['tid']] = $vresult['count'];
	if ($vresult['count'] > $s_count)
		{
		$s_count = $vresult['count'];
		$s_tid = $vresult['tid'];
		}
	}
$active_tables = "";
$query = "select id,started,topic from tables order by started;";
$result = mysql_query_alert($query);
$i = 1;
if (mysql_affected_rows() > 0)
	{
	while ($vresult = mysql_fetch_array($result))
		{
		$active_tables .= ActiveTablesLine($vresult,$parti_counters);
		if ($vresult['started'] > time() && $vresult['started'] - time() < 60*60*24)
			$timers[$vresult['id']] = $vresult['started'];
		}
	$active_tables .= '<div style="float:'.FloatDir().';height:2px;width:500px;border-top:1px solid #044678;"></div>';
	}
	
//echo $_SESSION['locale']."<br />";
//echo $_SESSION['perms'].'<br />';

if ($has_session)
{
MetaHeaderExtra($GlobalKeywords . ' '.stripslashes($GlobalTitle),stripslashes($GlobalTitle) . " ". stripslashes($GlobalDesc),' '.stripslashes($GlobalTitle));
require('top.php');

echo ('
<div id="popup_cont"></div>
<div style="background-image:url(\'fp_top.jpg\');background-repeat:no-repeat;width:750px;height:650px;">
<div style="float:'.FloatDir().';margin-top:150px;margin-right:160px;">'.LevelBox($user_id,$username,$points,false).'</div>
	<div style="overflow:hidden;border:1px solid #044678;border-bottom:2px solid #044678;margin-top:30x;margin-'.FloatDirOp().':15px;width:495px;height:250px;float:'.FloatDirOp().';">
	<div style="width:500px;height:20px;border-bottom:1px solid #044678;background-color:#BAE8FF;padding-top:4px;font-family:arial;color:#044678;font-weight:bold;font-size:13px;"> <div style="float:'.FloatDir().';margin-'.FloatDir().':20px;width:60px;">משתתפים</div>  <div style="float:'.FloatDir().';margin-'.FloatDir().':140px;width:75px;">נושא השולחן</div> <div style="float:'.FloatDir().';margin-'.FloatDir().':15px;margin-'.FloatDir().':125px;width:60px;">זמן פתיחה</div> </div>
	<div style="height:220px;width:500px;overflow-x:hidden;overflow-y:scroll;">
	'.$active_tables.'
	</div>
	</div>
	<div style="float:'.FloatDir().';font-weight:bold;font-family:arial;font-size:16px;width:150px;height:25px;color:#004681;margin-'.FloatDir().':50px;">המשפיענים ביותר</div> 
	<div style="float:'.FloatDir().';width:230px;height:85px;margin-top:5px;">'.$leaderboard.'</div>
	<div style="clear:both;"></div>
	<div style="float:'.FloatDirOp().';margin-top:10px;margin-left:10px;"><a href="table.php?tid='.$s_tid.'"><img src="enter.jpg" border="0" /></a></div><div style="clear:both;"></div>
'.TopButtons().'
<div style="margin-top:20px;"><a style="border:0px;" href="http://www.arvut.org" target="_blank"><img border="0" src="bot_banner.jpg" /></a></div>
<script type="text/javascript">
	function runPageFunctions()	{
		}
	function table_redirect(tid)
		{
		if (document.getElementById(\'opening_\'+tid).innerHTML == "פתוח")	{
			top.location = \''.$GLOBALS['fb_path'].'/table.php?tid=\'+tid;	}
		else	{
			popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>שולחן זה עדיין לא פתוח</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75); }
		}');
if (is_array($timers))
	{
	foreach ($timers as $tid => $timer)
		{

		echo('startTimer(\''.$timer.'\',\'opening_'.$tid.'\',\'פתוח\');');
		}
	}
echo'</script>';
	
require('footer.php');
}
?>

