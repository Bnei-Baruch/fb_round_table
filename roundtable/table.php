<?php 
require('dbc.php');
require('functions.php');
require('init.php');
$tid = StripUserInput($_GET['tid']);

if ($tid != "")
{

$query = "select * from tables where id = ".$tid;
$result =  mysql_query_alert($query);
if (mysql_affected_rows() > 0)
	{
	
	while ($vresult = mysql_fetch_array($result))
		{
		$topic = $vresult['topic'];
		$t_state = $vresult['state'];
		$t_type = $vresult['type'];
		$max_seats = $vresult['max_seats'];
		$invites = $vresult['invites'];
		$started = $vresult['started'];
		}
	if ($started  > time())
		{
		$table_state = false;
		$table_error = "FUTURE";
		}
	else
		$table_state = true;
	}
else
	$table_state = false;
}
else
	$table_state = false;

MetaHeaderExtra($GlobalKeywords . ' '.stripslashes($topic),stripslashes($topic) . " ". stripslashes($GlobalDesc),' '.stripslashes($topic),false,false,'discussion',false,false,'onbeforeunload = "close_down();"');
//echo $_SESSION['locale']."<br />";
//echo $_SESSION['perms'].'<br />';


if ($has_session)
{
PostDiscussion($tid);
$fql = ("select uid from user where is_app_user = 1 AND uid in (select uid2 from friend where uid1 = '".$user_id."');");
$param = array('method' => 'fql.query','query' => $fql,'callback' => '' );
$friend_users = GraphFQL($param);

if (is_array($friend_users))
	{
	$inv_excludes = "";
	foreach ($friend_users as $row) 
		$inv_excludes .= $row['uid'].','; 
	$inv_excludes = chop($inv_excludes,",");
	}
	
require('top.php');
$user_id = $_SESSION['user_id'];
$side_chat_cont = '';
$chat_lines = 0;
$init_speaker = false;
$fql = ("select sex,first_name,last_name from user where uid = '".$user_id."'");
$param = array('method' => 'fql.query','query' => $fql,'callback' => '' );
$user_info = GraphFQL($param);



$fql = ("select uid,first_name from user where is_app_user = 0 AND uid in (select uid2 from friend where uid1 = '".$user_id."');");
$param = array('method' => 'fql.query','query' => $fql,'callback' => '' );
$friend_info = GraphFQL($param);
$inv_boxes = "";
$inv_picks = array();
if (sizeof($friend_info) < 6)
	$picks = sizeof($friend_info) + 1;
else
	$picks = 6;
$pstart = rand(0,sizeof($friend_info));
$i = 0;
while ($i < $picks)
	{
	if ($pstart  + $i > sizeof($friend_info))
		$p = $i;
	else
		$p = $pstart  + $i;
	$inv_picks[$i]['uid'] = $friend_info[$p]['uid'];
	$inv_picks[$i]['fname'] = $friend_info[$p]['first_name'];
	if ($friend_info[$p]['uid'] != "" && $friend_info[$p]['first_name'] != "")
		$inv_boxes .= inviteBox( $friend_info[$p]['uid'],$friend_info[$p]['first_name']);
	$i ++;
	}

$query = "select count(tid) from connections where tid = '".$tid."'";
$result = mysql_query_alert($query);
$vresult = mysql_fetch_row($result);
$parti_num = $vresult[0];

$query = "select * from live_parti where tid = ".$tid;
$result = mysql_query_alert($query);
$i = 0;
while ($vresult = mysql_fetch_array($result))
	{
//	echo ' gona fill '.$vresult['uid'].' with '. $vresult['name'];
	$parti_info[$vresult['uid']]['name'] = $vresult['name'];
	$i ++;
	}
$parti_num -= $i;
$query = "select points from users where user_id = '".$user_id."'";
$result = mysql_query_alert($query);
while ($vresult = mysql_fetch_array($result))
	{
	$points = $vresult['points'];
	}
	

	
if ($table_state)
{
echo '<script type="text/javascript">
var like_state = Array();';
$query = "select sid,eid,data,user_id,created from live where tid = ".$tid." order by sid";
$result =  mysql_query_alert($query);
while ($vresult = mysql_fetch_array($result))
	{
	if ($vresult['eid'] == 10 or $vresult['eid'] == 11 or $vresult['eid'] == 14)
		{
		ApplyEvent($vresult['eid'],$vresult['data'],($vresult['created'] + 60*60*2),$vresult['user_id'],$vresult['sid']);
		$like_iter[$vresult['sid']] = $vresult;
		}
	if ($vresult['eid'] == 60)
		$l_question = $vresult['data'];
	if ($vresult['eid'] == 61)
		$init_speaker = $vresult['data'];
	if ( ($vresult['eid'] == 3 or  $vresult['eid'] == 5 or $vresult['eid'] == 62 ) && $vresult['data'] == $init_speaker)
		$init_speaker = false;
	if ($vresult['eid'] == 15)
		{ 
		$db_like_data = json_decode($vresult['data']);
		$like_iter[$db_like_data -> {'sid'}]['likes'] ++;
		if ($user_id == $db_like_data -> {'uid'}) 
			{
			$side_chat_cont = str_replace('__like_button_'.$db_like_data -> {'sid'}.'__','',$side_chat_cont); 
			$main_chat_cont = str_replace('__like_button_'.$db_like_data -> {'sid'}.'__','',$main_chat_cont); 
			}
		else
			{
			if ($user_id == $vresult['user_id'])
				{
				echo 'like_state['.$db_like_data -> {'sid'}.'] = true;';
				$side_chat_cont = str_replace('__like_button_'.$db_like_data -> {'sid'}.'__',GetTrans(89),$side_chat_cont); 
				$main_chat_cont = str_replace('__like_button_'.$db_like_data -> {'sid'}.'__',GetTrans(89),$main_chat_cont); 
				}
			else
				{
				$side_chat_cont = str_replace('__like_button_'.$db_like_data -> {'sid'}.'__',GetTrans(88),$side_chat_cont); 
				$main_chat_cont = str_replace('__like_button_'.$db_like_data -> {'sid'}.'__',GetTrans(88),$main_chat_cont);
				}
			}
		}
	$latest = $vresult['sid'];
	}

if (is_array($like_iter))
{
foreach ($like_iter as $sid => $row)
	{
	//print_r($row);
	if ($row['eid'] == 10 or $row['eid'] == 11 or $row['eid'] == 14)
		{
		//echo 'likes_'.$sid.' replace with '.$row['likes'].'<br />';
		if ($row['likes'] > 0)
			{
			$side_chat_cont = str_replace('__likes_'.$sid.'__',$row['likes'],$side_chat_cont); 
			$main_chat_cont = str_replace('__likes_'.$sid.'__',$row['likes'],$main_chat_cont); 
			}
		else
			{
			$side_chat_cont = str_replace('__likes_'.$sid.'__','0',$side_chat_cont);
			$main_chat_cont = str_replace('__likes_'.$sid.'__','0',$main_chat_cont);
			}
		}
	if ($user_id == $row['user_id'])
		{
		$side_chat_cont = preg_replace('/__like_button_'.$sid .'__/','',$side_chat_cont); 
		$main_chat_cont = preg_replace('/__like_button_'.$sid .'__/','',$main_chat_cont); 
		}
	else
		{
		$side_chat_cont = preg_replace('/__like_button_'.$sid .'__/',GetTrans(88),$side_chat_cont); 
		$main_chat_cont = preg_replace('/__like_button_'.$sid .'__/',GetTrans(88),$main_chat_cont); 
		}
		
	}
}

$main_chat_popup = '<div style="float:'.FloatDir().';width:312px;height:55px;background-color:#EEEFF4;padding-right:5px;padding-top:5px;padding-left:5px;" ><textarea maxlength="125"  onclick="FocusClearType(\'main_input\');" onblur="resetInput(\'main_input\',\''.GetTrans(51).'\');" style="color:#777777;width:300px;height:30px;resize: none;border:1px solid #BEC7D8;border-bottom:2px solid #BEC7D8;overflow:hidden;" id="main_input" />'.GetTrans(51).'</textarea><br /> <a style="cursor:pointer;float:'.FloatDirOp().';font-family:arial;font-weight:bold;font-size:14px;color:#3b5998;margin-'.FloatDirOp().':10px;" onclick="send_text();" id="send_button" onmouseover="javascript:this.style.color=\'red\';" onmouseout="javascript:this.style.color=\'#3b5998\';">'.GetTrans(54).'</a></div>';
$side_chat_popup = '<img src="sc_sep.jpg" /><textarea style="color:#777777;width:135px;height:60px;resize: none;border:1px solid #BEC7D8;border-bottom:2px solid #BEC7D8;overflow:hidden;" id="side_input" onclick="FocusClearType(\'side_input\');" onblur="resetInput(\'side_input\',\''.GetTrans(51).'\');">'.GetTrans(51).'</textarea><br /> <a style="cursor:pointer;float:'.FloatDirOp().';font-family:arial;font-weight:bold;font-size:14px;color:#3b5998;margin-'.FloatDirOp().':10px;" onclick="send_text();" id="send_button_side" onmouseover="javascript:this.style.color=\'red\';" onmouseout="javascript:this.style.color=\'#3b5998\';" >'.GetTrans(54).'</a>';
if (isset($l_question ) && $l_question  != "")
	$lq_html = $l_question ;
else
	$lq_html = "מחכה לשאלה מנחה...";
	
$inv_ar = explode(",",$invites);	
echo('
	 var latest_poll = "'.$latest.'";
	 var invites = Array();
	 var table_type = "'.$t_type.'";
	 var main_chat_popup = \''.addslashes($main_chat_popup).'\';
	 var side_chat_popup = \''.addslashes($side_chat_popup).'\';
	 function runPageFunctions()
		{
		return(true);
		}
	 ');
	 
if ($init_speaker )	{
	echo 'var init_speaker = '.$init_speaker.';'; }
else	{
	echo 'var init_speaker = 0;'; }
	 
if (is_array($inv_ar) && count($inv_ar) > 0)
	{
	foreach ($inv_ar as $uid)
		{
		if ($uid != "")
			echo 'invites['.$uid.'] = true;';
		}
	 
	}
	
if ($_SESSION['status'] == "")
	$u_status = "NEW";
else
	$u_status = $_SESSION['status'];

echo 'user_status = \''.$u_status.'\';
</script>';
	
//print_r($user_info);
echo ('
<div id="popup_cont"></div>
<div style="width:750px;height:40px;float:'.FloatDir().';font-family:arial;background-color:yellow;cursor:pointer;margin-bottom:2px;"><a href="//www.facebook.com/messages/594261257" target="_blank" style="text-decoration:none;">'.GetTrans(73).'</a></div><div style="clear:both;"></div>
<div style="z-index:-1;position:absolute;width:750px;height:190px;background-image:url(\'bg_top_heb.jpg\');background-repeat:no-repeat;"><a href="'.$GLOBALS['fb_path'].'" target="_blank"><div style="position:absolute;width:165px;height:175px;cursor:pointer;"  ></div></a><div style="margin:0px;padding:0px;width:152px;height:730px;max-height:730px;overflow:hidden;float:'.FloatDir().';" >
<div style="background-color:#EDF7FE;width:152px;height:40px;margin-top:190px;padding-top:5px;font-family:arial;font-weight:bold;font-size:18px;color:#3b5998;"><img src="crowd_chat.jpg" style="margin-'.FloatDir().':3px;" /> צ\'אט קהל (<span id="crowd_counter">'.$parti_num.'</span>)<br /><img src="sc_sep.jpg" /></div><div id="side_chat" style="background-color:#EDF7FE;height:500px;width:152px;overflow-y:hidden;overflow-x:hidden;">'.$side_chat_cont.'</div><div id="side_chat_div" style="background-color:#EDF8FE;text-align:center;"></div></div>
<div style="float:'.FloatDir().';width:590px;height:740px;max-height:740px;margin:0px;padding:0px;">
<div style="padding-'.FloatDir().':25px;float:'.FloatDir().';margin-top:10px;font-family:arial;font-weight:bold;font-size:18px;color:#3b5998;text-align:'.FloatDir().';padding-top:3px;">הנושא:<span style="font-weight:normal;" id="chat_topic">'.$topic.'</span></div><div style="clear:both;"></div>
<div style="padding-'.FloatDir().':25px;margin-bottom:25px;cursor:pointer;float:'.FloatDir().';margin-top:2px;font-family:arial;font-weight:bold;font-size:18px;color:#3b5998;" onclick="change_lq();">שאלה:<span style="font-weight:normal;" id="leading_question">'.$lq_html.'</span></div><div style="clear:both;"></div>
<div style="float:'.FloatDir().';width:132px;height:650px;">
<div style="float:'.FloatDir().';padding-'.FloatDir().':17px;padding-top:45px;">'.SeatLine(1).'</div>																																																			
<div style="float:'.FloatDir().';padding-'.FloatDir().':0px;padding-top:35px;">'.SeatLine(3).'</div>																																																			
<div style="float:'.FloatDir().';padding-'.FloatDir().':17px;padding-top:35px;">'.SeatLine(5).'</div></div> 
<div style="float:'.FloatDir().';width:320px;height:650px;text-align:center;">
<div style="float:none;width:320px;height:105px;text-align:center;"><div style="float:none;margin:auto;">'.HostLine().'</div></div> 
<div style="float:'.FloatDir().';height:25px;width:314px;font-family:arial;font-weight:bold;font-size:13px;color:#ffffff;background-color:#00457C;border-top:1px solid #9EB8CE;border-right:1px solid #9EB8CE;text-align:'.FloatDir().';padding-top:7px;padding-right:7px;">הנושא:<span style="font-weight:normal;" >'.$topic.'</span><br /></div>
<div style="text-align:'.FloatDir().';width:320px;height:350px;border:1px solid #999999;overflow:auto;overflow-x:hidden;max-height:350px;" id="main_chat">'.$main_chat_cont.'
</div>
<div id="main_chat_div"></div>
<div style="float:'.FloatDir().';width:320px;height:160px;text-align:center;margin-top:2px;"><div style="float:'.FloatDir().';margin-'.FloatDirOp().':100px;margin-'.FloatDir().':5px;">'.SeatLine(7).'</div><div style="float:'.FloatDir().';">'.SeatLine(8).'</div></div> 
</div> 
<div style="float:'.FloatDirOp().';width:132px;height:650px;">
<div style="float:'.FloatDirOp().';height:25px;width:140px;"><img src="rules.jpg" style="margin-'.FloatDirOp().':3px;cursor:pointer;"  onclick=" rules_popup();" /><img style="cursor:pointer;margin-'.FloatDirOp().':3px;" src="help.jpg" onclick="virtualtour_popup();" /><img src="home.jpg" style="cursor:pointer;"  onclick="top.location = \''.$GLOBALS['fb_path'].'\';" /></div>	
<div style="float:'.FloatDirOp().';padding-'.FloatDirOp().':25px;padding-top:20px;">'.SeatLine(2).'</div>																																																			
<div style="float:'.FloatDirOp().';padding-'.FloatDirOp().':5px;padding-top:35px;">'.SeatLine(4).'</div>																																																			
<div style="float:'.FloatDirOp().';padding-'.FloatDirOp().':25px;padding-top:35px;">'.SeatLine(6).'</div>
</div> 																																																																																																																																																																																														
<div style="clear:both;"></div>
</div>	 

<div style="float:'.FloatDir().';width:750px;height:110px;padding-top:5px;" >'.LevelBox($user_id,$user_info[0]['first_name'] .' '.$user_info[0]['last_name'],$points).$inv_boxes.'<img style="margin-top:5px;cursor:pointer;" src="invite_bot.jpg" onclick="inviteFriends();" /></div>   
<a style="border:0px;" href="http://www.arvut.org" target="_blank"><img border="0" src="bot_banner.jpg" /></a>

');
if ($user_id == 594261257)
	echo '<div id ="debugz" style="width:700px;height:200px;border:1px solid black;overflow:scroll"></div>';
	
	echo('
<script type="text/javascript">
connect_table("'.$tid.'");
</script>

');
}
else
{
if (isset($table_error) && $table_error != "")
	{
	if ($table_error == "FUTURE")
		echo 'שולחן זה אינו פעיל, הוא ייפתח ב <span>'.date('H:i d/m',$started).'</span>';
	}
else
	echo 'הדיון המבוקש הסתיים או שאינו קיים<br />';	
}

//<a href="javascript:levelup_popup(1);">CLICK MEH</a><br />

//<a href="javascript:connect_table(\''.$tid.'\');">FU</a><br />
	//  <a href="#" onclick="start_poller();">Start Poller</a><br />
 	  // <a href="#" onclick="stop_poller();">Stop Poller</a><br />
	  //<div id="control_text"></div>
	  //<div id="close_table_button"> <a href="#" onclick="close_table();">'.GetTrans(55).'</a><br /></div>
require('footer.php');
}
?>

