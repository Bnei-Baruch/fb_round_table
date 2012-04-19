<?php
$levels[1]['xp'] = 0;
$levels[1]['desc']  = '';
$levels[2]['xp'] = 15;
$levels[2]['desc']  =  '';
$levels[3]['xp'] = 35;
$levels[3]['desc']  =  '';
$levels[4]['xp'] = 60;
$levels[4]['desc']  =  '';
$levels[5]['xp'] = 110;
$levels[5]['desc']  =  '';
$levels[6]['xp'] = 100000;
$levels[6]['desc']  =  '';


function LevelBar_RT($points)
	{

	global $levels;
	$bar_width = 90;
	$current_level = GetLevel($points);
	if ($points > 0)
		$level_bar_tip = ($points - $levels[$current_level]['xp']).'/'.($levels[($current_level + 1)]['xp'] - $levels[($current_level)]['xp']).' XP for level '.($current_level + 1);
	else
		$level_bar_tip = '';
	$progress = $points - $levels[$current_level]['xp'];
	$end = $levels[($current_level + 1)]['xp']  - $levels[$current_level]['xp'] ;
	$done_portion = floor($progress/$end*$bar_width);
	//echo $done_portion.'<br />'.$bar_width.'<br />'.$progress/$end.'<br />';
	//onmouseover="Tip(\''.$level_bar_tip.'\');" onmouseout="UnTip();"
	$return.=('<div style="float:left;width:'.$done_portion.'px;height:10px;border-top-left-radius:5px;border-bottom-left-radius:5px;background-image:url(filler.jpg);background-repeat:repeat-x;" id="doneportion"></div><script type="text/javascript">var bar_width = '.$bar_width.';var current_level = '.$current_level.';var points = '.$points.';</script>');
	return($return);
	
	}
	
function GetLevel($points)
	{
	global $levels;
	$i = 0;
	foreach ($levels as $level)
		{
		$i ++;
		//echo 'gonna cmpr '.$points.' with '.$level['xp'].'<br />';
		if ($points >= $level['xp'])
			$return = $i;
		else
			break;
		}
	//echo $return.'<br />';
	return($return);
	}
	
	
function AddScore($uid,$score)
	{
	$query = "update users set points = points + ".$score." where user_id = '".$uid."'";
	if (mysql_query_alert($query))
		$return = true;
	else
		$return = false;
	}
	
function DecScore($uid,$score)
	{
	$query = "update users set points = points - ".$score." where user_id = '".$uid."'";
	if (mysql_query_alert($query))
		$return = true;
	else
		$return = false;
	}
	
function GenerateTid()
	{
	$len = 9;
	$i = 0;
	$cguid = "";
	while ($i < $len)
		{
		$digit = (mt_rand(0,9));
		$cguid .= $digit;
		$i ++;
		}
	return($cguid);	
	}
	
function InsertEvent($tid,$eid,$data,$user_id)
	{
	$query = "insert into archive (tid,eid,created,data,user_id) values ('".$tid."','".$eid."','".time()."','".$data."','".$user_id."');";
	mysql_query_alert($query);
	$query = "insert into stream (tid,eid,created,data,user_id) values ('".$tid."','".$eid."','".time()."','".$data."','".$user_id."');";
	mysql_query_alert($query);
	
	$query = "insert into live (tid,eid,created,data,user_id) values ('".$tid."','".$eid."','".time()."','".$data."','".$user_id."');";
	if (mysql_query_alert($query))
		return("OK");
	else
		return("FAILED");	
	}
function GetFromGet($string)
	{
	if (isset($_GET[$string]) && $_GET[$string] != "")
		return(StripUserInput($_GET[$string]));
	else
		return(false);
			
	}
	
function ApplyEvent($eid,$data,$time,$uid,$sid)
	{
	global $main_chat_cont,$side_chat_cont;
	if ($eid == 11 or $eid == 14)
		{
		$main_chat_cont .= ChatLine($uid,$data,$time,$eid,$sid);
		}
	if ($eid == 10)
		{
		$side_chat_cont .= ChatLine($uid,$data,$time,$eid,$sid);	
		}
	}
	
function PostDiscussion($tid)
	{
	if ($tid != "")
		{
		$attach = array("discussion" => 'http:'.$GLOBALS['site_path']."table.php?tid=".$tid);
		return (GraphAPIPost('me/'.$GLOBALS['app_name'].':join',$attach));
		}
	else
		return(false);
	}
function ActiveTablesLine($data,$count)
	{
	if ($count[$data['id']] == "")
		$count[$data['id']] = 0;
	
	//d 'compr '.time().' with '.$data['started'].'<br />';
	if (time() > $data['started'])
		$opening = "פתוח";
	elseif($data['started'] - time() > 60*60*24)
		$opening = '<span style="color:red;font-weight:bold;">'. date('H:i d/m',$data['started']).'</span>';
	else
		$opening = "";
	return('<div onmouseover="this.style.backgroundColor=\'#00457B\';this.style.color =\'white\';" onmouseout="this.style.backgroundColor=\'white\';this.style.color =\'#00457B\';" onclick="table_redirect(\''.$data['id'].'\');" style="cursor:pointer;float:'.FloatDir().';height:25px;width:500px;border-top:1px solid #044678;font-family:arial;font-size:14px;color:#00457B;"><div style="float:'.FloatDir().';border-left:1px solid #044678;width:60px;height:20px;font-weight:bold;font-size:14px;font-family:arial;padding-right:15px;padding-top:5px;"><img align="top" src="parti_ppl.jpg" /> '.	$count[$data['id']] .'</div><div style="float:'.FloatDir().';border-left:1px solid #044678;width:325px;height:20px;padding-top:5px;text-align:center;">'.$data['topic'].'</div><div style="float:'.FloatDir().';padding-top:5px;text-align:center;font-weight:bold;color:green;" id="opening_'.$data['id'].'">'.$opening .'</div> </div><div style="clear:both;"></div>');
	}
	
function inviteBox($uid,$fname)
	{
	// '.$uid.'
	global $tid; 
	$return = "";
	$return .= '<div onclick="invite_single(\'100000419723809\');" style="margin-'.FloatDir().':7px;float:'.FloatDir().';text-align:center;cursor:pointer;border:1px solid #aee7fe;border-bottom:3px solid #aee7fe;width:70px;height:96px;background-color:#EBF8FE;font-family:ariel;font-size:12px;font-weight:bold;color:#00457c;"><div style="overflow:hidden;margin-bottom:2px;height:15px;">'.$fname.'</div><img src="//graph.facebook.com/'.$uid.'/picture" width="50" style="margin-bottom:5px;" /><img src="invite_me.jpg" /></div>';
	return($return);
	}
	
function LeaderBox($uid,$fname)
	{
	$return = "";
	$return .= '<div style="margin-'.FloatDir().':7px;float:'.FloatDir().';text-align:center;cursor:pointer;border:1px solid #aee7fe;border-bottom:3px solid #aee7fe;width:65px;height:76px;background-color:#EBF8FE;"><a href="//www.facebook.com/'.$uid.'" style="font-family:ariel;font-size:12px;font-weight:bold;color:#00457c;" target="_blank"> <div style="overflow:hidden;margin-bottom:2px;">'.$fname.'</div><img src="//graph.facebook.com/'.$uid.'/picture" width="50" style="margin-bottom:5px;" border="0" /></a></div>';
	return($return);
	}
	
function ChatLine($uid,$text,$time,$eid,$sid,$js = false)
	{
	global $chat_lines;
	$chat_lines ++;
	if ($js)
		{
		$chat_lines = "'+side_chat_lines+'";
		$uid = "'+uid+'";
		$text = "";
		$sid = "'+csid+'";
		$date = "'+ctime+'";
		}
	else
		$date = date('H:i',$time);
	if ($eid == 10)
		$return = ('<div onmouseover="high_side_btns(\'side_btns_'.$chat_lines.'\');" onmouseout="hide_side_btns(\'side_btns_'.$chat_lines.'\');" onclick="side_options_popup('.$uid.',\'side_inv_'.$chat_lines.'\');" style="float:right;margin-right:10px;font-family:arial;font-size:12px;font-weight:bold;color:#00457c;cursor:pointer;"><div style="float:'.FloatDir().';width:140px;"><div style="float:'.FloatDir().';"><a href="//www.facebook.com/profile.php?id='.$uid.'" target="_blank"><img src="//graph.facebook.com/'.$uid.'/picture" border="0" width="25" height="25" /></a></div><div style="float:'.FloatDirOp().';height:10x;font-size:8px;width:100px;text-align:'.FloatDirOp().';" id="side_btns_'.$chat_lines.'">.</div><div style="float:'.FloatDir().';"> <a href="//www.facebook.com/profile.php?id='.$uid.'" style="color:#00457c;" target="_blank">'.getPartiUsername($uid).'</a></div></div><div style="clear:both;"></div><span style="font-weight:normal;color:#777777;">'.$text.'</span><br /><div style="float:'.FloatDir().';margin-'.FloatDirOp().':3px;font-weight:normal;" id="like_counter_'.$sid.'">__likes_'.$sid.'__</div><div style="float:'.FloatDir().';"><img src="like.jpg" border="0" /></div><div style="margin-'.FloatDir().':2px;float:'.FloatDir().';font-weight:normal;cursor:pointer;" onclick="submit_like(\''.$sid.'\');" onmouseover="this.style.color=\'red\';" onmouseout="this.style.color=\'#00457c\';" id="like_'.$sid.'">__like_button_'.$sid.'__</div></div><div style="clear:both;"><div style="width:100%;height:16px;"><div style="margin-top:7px;border-top:1px solid #f0f0f0;width:70%;margin-right:5px;margin-bottom:7px;float:'.FloatDir().';"></div><div style="float:'.FloatDir().';margin-'.FloatDir().':2px;margin-'.FloatDirOp().':2px;color:#818181;font-size:11px;font-family:arial;font-weight:bold;" >'.$date.'</div></div></div><div style="clear:both;"></div><input id="uid_'.$sid.'" type="hidden" value="'.$uid.'" />');
	if ($eid == 11)
		$return = ('<div style="float:right;margin-right:5px;"><img src="//graph.facebook.com/'.$uid.'/picture" width="25" height="25" /></div><div style="float:right;margin-right:10px;font-family:arial;font-size:12px;font-weight:bold;color:#00457c;width:250px;">'.getPartiUsername($uid).' <span style="color:#000000;font-weight:bold;">כותב/ת:</span><br /><span style="font-weight:normal;color:#333333;">'.$text.'</span><br /><div style="float:'.FloatDir().';margin-'.FloatDirOp().':3px;font-weight:normal;" id="like_counter_'.$sid.'">__likes_'.$sid.'__</div><div style="float:'.FloatDir().';"><img src="like.jpg" border="0" /></div><div style="margin-'.FloatDir().':2px;float:'.FloatDir().';font-weight:normal;cursor:pointer;" onclick="submit_like(\''.$sid.'\');" onmouseover="this.style.color=\'red\';" onmouseout="this.style.color=\'#00457c\';" id="like_'.$sid.'">__like_button_'.$sid.'__</div></div><div style="clear:both;"></div><div style="width:100%;height:16px;"><div style="margin-top:7px;border-top:1px solid #f0f0f0;width:85%;margin-right:5px;margin-bottom:7px;float:'.FloatDir().';"></div><div style="float:'.FloatDir().';margin-'.FloatDir().':5px;color:#818181;font-size:11px;font-family:arial;font-weight:bold;" >'.date('H:i',$time).'</div></div><div style="clear:both;"></div><input id="uid_'.$sid.'" type="hidden" value="'.$uid.'" />');
	if ($eid == 14)
		$return = ('<div style="background-color:#f0f0f0;"><div style="float:right;margin-right:5px;"><img src="//graph.facebook.com/'.$uid.'/picture" width="25" height="25" /></div><div style="float:right;margin-right:10px;font-family:arial;font-size:12px;font-weight:bold;color:red;width:250px;">'.getPartiUsername($uid).' <span style="color:#000000;font-weight:bold;">מתפרצ/ת:</span><br /><span style="font-weight:normal;color:#333333;">'.$text.'</span><br /><div style="float:'.FloatDir().';margin-'.FloatDirOp().':3px;font-weight:normal;color:#00457c;" id="like_counter_'.$sid.'">__likes_'.$sid.'__</div><div style="float:'.FloatDir().';"><img src="like.jpg" border="0" /></div><div style="margin-'.FloatDir().':2px;float:'.FloatDir().';font-weight:normal;cursor:pointer;color:#00457c;" onclick="submit_like(\''.$sid.'\');" onmouseover="this.style.color=\'red\';" onmouseout="this.style.color=\'#00457c\';" id="like_'.$sid.'">__like_button_'.$sid.'__</div></div><div style="clear:both;"></div><div style="width:100%;height:16px;"><div style="margin-top:7px;border-top:1px solid white;width:85%;margin-right:5px;margin-bottom:7px;float:'.FloatDir().';"></div><div style="float:'.FloatDir().';margin-'.FloatDir().':5px;color:#818181;font-size:11px;font-family:arial;font-weight:bold;" >'.date('H:i',$time).'</div></div><div style="clear:both;"></div></div><input id="uid_'.$sid.'" type="hidden" value="'.$uid.'" />');
	
	if ($js)
		return(addslashes($return));
	else
		return($return);
	}
	

function getPartiUsername($uid)
	{
	global $parti_info;
	if (isset($parti_info[$uid]['name']) && $parti_info[$uid]['name'] != "")
		return($parti_info[$uid]['name']);
	else
		{
		$parti_info[$uid]['name'] = getUsername($uid);
		return($parti_info[$uid]['name']);
		}
	}
	
function SeatLine($spot)
	{
	$return = "";
	$return .= '<div style="width:100px;height:150px;text-align:center;"><div style="height:20px;" id="spot_mic_'.$spot.'"></div><div style="font-family:arial;font-size:12px;color:#3b5998;font-weight:bold;text-align:center;overflow:hidden;width:100px;height:30px;" id="spot_name_'.$spot.'"></div><div style="text-align:center;height:60px;margin-right:17px;margin-left:17px;" id="spot_'.$spot.'"> </div><div id="ctrl_btns_'.$spot.'" style="text-align:none;" ></div></div>';
	return($return);
	}
	
function HostLine()
	{
	$return = "";
	$return .= '<div style="width:200px;margin:auto;"><div style="width:200px;font-family:arial;font-size:12px;color:#3b5998;font-weight:bold;text-align:center;" id="spot_name_0"></div><div id="spot_0" style="margin-bottom:2px;padding-top:2px;text-align:center;"></div><div id="ctrl_btns_0" style="text-align:none;" ></div></div>';
	return($return);
	}
	
function getUsername($uid)
	{
	$return = false;
	$query = "select fname,lname from users where user_id = '".$uid."'";
	$result = mysql_query_alert($query);
	while ($vresult = mysql_fetch_array($result))
		{
		$return = $vresult['fname']. ' '.$vresult['lname'];
		}
		
	return($return);
	}
	
function CloseTable($tid)
	{
	$query = "select id from tables where id = '".$tid."'";
	$result = mysql_query_alert($query);
	if (mysql_affected_rows() > 0)
		{
		$query = "delete from live where tid = '".$tid."'";
		mysql_query_alert($query);
		$query = "delete from stream where tid = '".$tid."'";
		mysql_query_alert($query);
		$query = "insert into tables_archive select * from tables where id = '".$tid."'";
		mysql_query_alert($query);
		if (mysql_affected_rows() > 0)
			{
			$query = "delete from tables where id = '".$tid."'";
			mysql_query_alert($query);
			if (mysql_affected_rows() > 0)
				$return = "OK";
			else
				$return = "FAIL";
			}
		else
			$return = "FAIL";
			
		$query = "delete from live_parti where tid = '".$tid."'";
		mysql_query_alert($query);
		}
	else
		$return = "FAIL";
		
	return($return);
	}
	
function FloatDir()
	{
	if (isset($GLOBALS['float']) && $GLOBALS['float'] != '')
		return($GLOBALS['float']);
	else
		{
		if ($_SESSION['locale_dir'] == 'rtl')
			return('right');
		else
			return('left');
		}
	}
	
function FloatDirOp()
	{
	if (FloatDir() == 'right')
		return('left');
	else
		return('right');
	}
	
function ReloadPerms($user_id)
	{
	$query = "select perms from users where user_id = '".$user_id."'";
	$result = mysql_query_alert($query);
	while ($vresult = mysql_fetch_array($result))
		{
		if ($vresult['perms'] != "")
			$_SESSION['perms'] = $vresult['perms'];	
		}	
	}
	
function CheckPerms($level)
	{
	if ($level == 'mod' && ($_SESSION['perms'] == 'mod' or $_SESSION['perms'] == 'admin' or $_SESSION['perms'] == 'superadmin'))
		return(true);
	if ($level == 'admin' && ($_SESSION['perms'] == 'admin' or $_SESSION['perms'] == 'superadmin'))
		return(true);
	if ($level == 'superadmin' && $_SESSION['perms'] == 'superadmin')
		return(true);
	return(false);
	}
	
function LevelBox($user_id,$username,$points,$background = true)
	{
	global $levels;
	if ($background)
		$bg = 'background-image:url(\'bg_bottom.jpg\');';
	else
		$bg = "";
	return('<div style="float:'.FloatDir().';width:188px;height:90px;padding-'.FloatDir().':5px;padding-top:15px;cursor:pointer;'.$bg.'background-repeat:no-repeat;background-position:right; " onclick="how_to_get_points_popup();"><div style="border:4px solid grey;border-radius: 30px;width:50px;margin-top:15px;float:'.FloatDir().';text-align:center;"><img src="//graph.facebook.com/'.$user_id.'/picture" style="border-radius: 30px;" width="50" /></div><div style="float:'.FloatDir().';width:120px;height:80px;"><div style="float:'.FloatDir().';" ><span style="font-family:ariel;font-size:12px;font-weight:bold;color:#00457c;">'.$username.' </span></div><div style="clear:both;"></div><div style="float:'.FloatDir().';height:60px;width:120px;"><div style="float:'.FloatDirOp().';font-family:arial;font-weight:bold;font-size:14px;color:#3b5998;margin-top:5px;width:100px;"><div style="float:'.FloatDirOp().';"> <span id="bar_points">'.$points.'</span>/<span id="next_level_points">'.$levels[(GetLevel($points) + 1)]['xp'] .'</span> <img align="top" src="like.jpg" /></div><div style="float:'.FloatDir().';width:15px;height:15px;"><img id="level_img" src="level_'.GetLevel($points).'.jpg" /></div></div><div style="clear:both;"></div><div style="margin-top:5px;width:100px;height:10px;border-radius:5px;border:1px solid #BEC7D8;border-bottom:2px solid #BEC7D8;background-color:#02457A;float:'.FloatDirOp().';">'.LevelBar_RT($points ).'</div></div></div></div>');
	}
	
function TopButtons()
	{
	$return = "";
	$return .= '<div style="position:absolute;left:490px;top:120px;width:90px;height:30px;cursor:pointer;"></div>
	<div style="position:absolute;left:395px;top:120px;width:90px;height:30px;cursor:pointer;" onclick="virtualtour_popup();"></div>
	<div style="position:absolute;left:308px;top:120px;width:80px;height:30px;cursor:pointer;" onclick="inviteFriends();"></div>
	<div style="position:absolute;left:128px;top:120px;width:175px;height:30px;cursor:pointer;" onclick="how_to_get_points_popup();"></div>
	<div style="position:absolute;left:0px;top:120px;width:122px;height:30px;cursor:pointer;" onclick="top.location = \''.$GLOBALS['fb_path'].'\';"></div>';
	return($return);
	}
	require('../common/functionso.php');

?>