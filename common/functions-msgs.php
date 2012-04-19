<?php
//CutString($desc,225,3,'<span class="ShowMoreTerm"> More...</span>'),' class="LinkText" href="term.php?guid='.$guid.'" '));

// like buttons graveyard

//<iframe src="http://www.facebook.com/plugins/like.php?href='.$GLOBALS['fb_path'].$msg['guid'].'&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;colorscheme=light&amp;height=50" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:50px;" allowTransparency="true"></iframe>

//<iframe src="http://www.facebook.com/plugins/like.php?href='.$GLOBALS['fb_path'].$msg['guid'].'&amp;layout=standard&amp;show_faces=false&amp;width=400&amp;action=like&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:400px; height:25px;" allowTransparency="true"></iframe>


function GetFollowButton($uid)
	{
	$return .= '<iframe name="dofollow_target" width="0" height="0" frameborder="0"></iframe><form name="dofollow_form"  target="dofollow_target" action="'.$GLOBALS['fb_path'].'dofollow.php" method="get" enctype = "application/x-www-form-urlencoded"><input type="hidden" name="follow_uid" value="'.$uid.'" /></form><div style="float:left;margin-bottom:5px;margin-right:5px;"><div id="do_follow_button"><a  onmouseover="this.style.cursor=\'pointer\';"  onclick="doFollow();" >follow</a></div></div>';
	return($return);
	}
	

function GetUnFollowButton($uid)
	{
	$return .= '<iframe name="unfollow_target" width="0" height="0" frameborder="0"></iframe><form name="unfollow_form"  target="unfollow_target" action="'.$GLOBALS['fb_path'].'unfollow.php" method="get" enctype = "application/x-www-form-urlencoded"><input type="hidden" name="follow_uid" value="'.$uid.'" /></form><div style="float:left;margin-bottom:5px;margin-right:5px;"><div id="do_follow_button"><a  onmouseover="this.style.cursor=\'pointer\';"  onclick="dounFollow();" >unfollow</a></div></div>';
	return($return);
	}
	

function GetUserName($user_id)
	{
	$query = "select fname,lname from users where user_id = '".$user_id."'";
	$result = mysql_query_alert($query);
	while ($vresult = mysql_fetch_array($result))
		{
		$return = $vresult['fname'].' '.$vresult['lname'];	
		}
	if (mysql_affected_rows() == 0)
		$return = false;
							
	return($return);
	}

function GetMsgOwner($guid)
	{
	$query = "select user_id from msgs where guid = '".$guid."'";
	$result = mysql_query_alert($query);
	while ($vresult = mysql_fetch_array($result))
		{
		$return = $vresult['user_id'];	
		}
	if (mysql_affected_rows() == 0)
		$return = false;
							
	return($return);
	}


function GetCommentOwner($guid)
	{
	$query = "select user_id from comments where cguid = '".$guid."'";
	$result = mysql_query_alert($query);
	while ($vresult = mysql_fetch_array($result))
		{
		$return = $vresult['user_id'];	
		}
	if (mysql_affected_rows() == 0)
		$return = false;
							
	return($return);
	}


function CollectLine($line)
	{
	global $collect_lines_ids;
	$return .= '<div id="collect_line_'.$collect_lines_ids.'" style="float:left;width:530px;opacity:1;margin-top:5px;">
	<div style="float:left;margin-right:2px;">+'.$line['collect'].'</div>
	<div style="float:left;width:20px;margin-right:5px;margin-top:1px;"><img src="'.$GLOBALS['common_path'].'like.jpg" /></div>
	<div style="float:left;width:400px;overflow:hidden;border:none;" class="msgline_msg">  '.$line['msg'].'</div>
	<div style="clear:both;"></div>
	<div style="margin-top:3px;border-bottom:1px solid #E9E9E9;width:500px;height:1px;margin-bottom:2px;"></div></div>
	<script type="text/javascript">collectValues['.$collect_lines_ids.'] = '.$line['collect'].';</script>';
	$collect_lines_ids ++;
	return($return);
	}
	
function NotifLine($line)
	{
	global $notif_lines_ids;
	if ($line['status'] == 'unread')	
		$highlight = 'style="float:left;overflow:hidden;border:none;background-color:#EDEFF4;"';
	else
		$highlight = 'style="float:left;overflow:hidden;border:none;"';
		
	$return .= '<div id="notif_line_'.$notif_lines_ids.'" style="float:left;width:530px;opacity:1;margin-top:5px;">
	<div style="float:left;width:20px;margin-right:5px;margin-top:1px;"><img src="'.$line['icon'].'" /></div>
	<div '.$highlight.' class="msgline_msg">  '.$line['msg'].'</div>
	<div style="clear:both;"></div>
	<div style="margin-top:3px;border-bottom:1px solid #E9E9E9;width:500px;height:1px;margin-bottom:2px;"></div></div>';
	$notif_lines_ids ++;
	return($return);
	}
	
	
function MsgLine($msg,$user_name,$comments_num)
	{
	global $admin_list;
	if ($comments_num == '')
		$comments_num = 0;
	if ($comments_num == 1)
		$comment_str = 'comment';
	else	
		$comment_str = 'comments';
	$timestr = GetTimeStr($msg['time']);
	$return .= '<div style="float:left;width:515px;border:none;margin-top:5px;" onmouseover="HighlightMsgButtons(\'msg_buttons_'.$msg['guid'].'\');" onmouseout="HideMsgButtons(\'msg_buttons_'.$msg['guid'].'\');" ><div id="msgline_flag_cont_'.$msg['guid'].'"></div><div id="msgline_delete_cont_'.$msg['guid'].'" style="float:right;"></div><div id="msg_buttons_'.$msg['guid'].'"  style="float:right;opacity:0;"><div style="margin-bottom:5px;"><a class="none" style="border:1px solid white;" onmouseover="Tip(\'Flag\');" onmouseout="UnTip();" href="Javascript:FlagScreen(\''.$msg['guid'].'\',\'\',\''.$msg['user'].'\')"><img alt="flag" align="top" src="'.$GLOBALS['common_path'].'flag.jpg" width="15" border="0" /></a></div>';
	if ($msg['user'] == $_SESSION['user_id'] or in_array($_SESSION['user_id'],$admin_list))
	$return.= ('<div><a style="border:1px solid white;"  class="none" onmouseover="Tip(\'Delete\');" onmouseout="UnTip();" href="Javascript:ConfirmDelete(\'msgline_delete_cont_'.$msg['guid'].'\',\''.$GLOBALS['fb_path'].'?'.RebuildQS(array('delete_msg' => $msg['guid'])).'\')"><img alt="trash" align="top" src="'.$GLOBALS['common_path'].'images/garbage.jpg" width="11" border="0" /></a></div>');																																															
	$return .= '</div><div style="float:left;margin-right:10px;"><a  href="'.$GLOBALS['fb_path'].$msg['user'].'"  target="_top"><img border="0" width="50" src="http://graph.facebook.com/'.$msg['user'].'/picture" /></a></div><div style="float:left;">	<fb:like href="'.$GLOBALS['fb_path'].$msg['guid'].'" layout="box_count" show-faces="false" width="55" action="like" colorscheme="light" font="lucida grande"></fb:like><!--<iframe src="http://www.facebook.com/plugins/like.php?href='.$GLOBALS['fb_path'].$msg['guid'].'&amp;layout=box_count&amp;show_faces=true&amp;width=55&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=65" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:55px; height:65px;" allowTransparency="true"></iframe>--></div><div style="float:left;width:380px;overflow:hidden;" class="msgline_msg"><a onmouseover="this.style.textDecoration = \'underline\';" onmouseout="this.style.textDecoration = \'none\';" style="color:#3B5998;" href="'.$GLOBALS['fb_path'].$msg['user'].'" target="_top">'.$user_name['fname']. ' '.$user_name['lname'].':</a> <a href="'.$GLOBALS['fb_path'].$msg['guid'].'" target="_top" class="msgline_msg" > '.$msg['msg'].'</a></div><div style="clear:both;"></div><div style="float:left;margin-left:0px;"><a href="'.$GLOBALS['fb_path'].$msg['guid'].'" target="_top" style="font-size:11px;color:#777777;" class="msgline_msg" >'.$timestr.'</a> · <a href="'.$GLOBALS['fb_path'].$msg['guid'].'" target="_top" style="font-size:11px;color:#3B5998;" class="msgline_msg" >'.$comments_num.' '.$comment_str.'</a> · <a onclick="javascript:ShareMsg(\''.ConvertQs($msg['msg']).'\',\''.$msg['guid'].'\');" style="font-size:11px;color:#3B5998;" class="msgline_msg" onmouseover="this.style.cursor=\'pointer\';">Share</a></div><div style="clear:both;"></div><div style="margin-top:3px;border-bottom:1px solid #E9E9E9;width:485px;height:1px;margin-bottom:2px;"></div></div></div>';	
	return($return);
	}
	

function CommentLine($msg,$user_name,$owner)
	{
	global $admin_list;
	$return .= '<div style="float:left;width:515px;border:none;margin-top:5px;" onmouseover="HighlightMsgButtons(\'msg_buttons_'.$msg['guid'].'\');" onmouseout="HideMsgButtons(\'msg_buttons_'.$msg['guid'].'\');" ><div id="msgline_delete_cont_'.$msg['guid'].'" style="float:right;"></div><div id="msg_buttons_'.$msg['guid'].'"  style="float:right;opacity:0;">';
	if ($msg['user'] == $_SESSION['user_id'] or $owner == $_SESSION['user_id']  or in_array($_SESSION['user_id'],$admin_list))
	$return.= ('<div><a style="border:1px solid white;"  class="none" onmouseover="Tip(\'Delete\');" onmouseout="UnTip();" href="Javascript:ConfirmDelete(\'msgline_delete_cont_'.$msg['guid'].'\',\''.$GLOBALS['fb_path'].'?'.RebuildQS(array('delete_comment' => $msg['guid'])).'\')"><img alt="trash" align="top" src="'.$GLOBALS['common_path'].'images/garbage.jpg" width="11" border="0" /></a></div>');																																															
	$return .= '</div><div style="float:left;margin-right:5px;"><a href="'.$GLOBALS['fb_path'].$msg['user'].'"  target="_top"><img border="0" width="50" src="http://graph.facebook.com/'.$msg['user'].'/picture" /></a></div><div style="float:left;width:380px;overflow:hidden;" class="msgline_msg"><a style="color:#3B5998;" href="'.$GLOBALS['fb_path'].$msg['user'].'" target="_top" onmouseover="this.style.textDecoration = \'underline\';" onmouseout="this.style.textDecoration = \'none\';">'.$user_name.':</a> '.$msg['msg'].'</div><div style="clear:both;"></div><div style="margin-top:3px;border-bottom:1px solid #E9E9E9;width:485px;height:1px;margin-bottom:2px;"></div></div>';	
	return($return);
	}
	
	
function AddCommentBox($uid,$guid)
	{
	if ($uid != '')
		$piccy = 'http://graph.facebook.com/'.$uid.'/picture';
	else
		$piccy = $GLOBALS['common_path'].'silhouette.jpg';
		
	$return .= '<iframe name="comment_form_frame" width="0" height="0" frameborder="0"></iframe><div style="float:left;width:550px;border:none;margin-top:5px; onmouseover="HighlighCommentButton(\'commentbutton\');" onmouseout="HideMsgButtons(\'commentbutton\');""></div><div style="float:left;margin-right:5px;"><form name="comment_form"  target="comment_form_frame" action="'.$GLOBALS['fb_path'].'submit_comment.php" method="get" enctype = "application/x-www-form-urlencoded"><a href="'.$GLOBALS['fb_path'].$uid.'"  target="_top"><img border="0" width="50" src="'.$piccy.'" /></a><textarea  onclick="ClearCommentText(this);"  id="comment_input" name="comment_input" style="width:420px;height:36px;border:1px solid #B4BBCD;overflow:hidden;color:#777777;font-family:arial;padding:5px;margin-left:2px;">Add a comment...</textarea><input type="hidden" name="comment_uid" id="comment_uid" value="'.$uid.'" /><input type="hidden" name="comment_guid" id ="comment_guid" value="'.GenerateCGuid().'" /><input type="hidden" name="guid_comment_box" id="guid_comment_box" value="'.$guid.'" /><div style="clear:both;"></div><div id="commentbuttoncont" style="float:right;margin-top:2px;height:30px;overflow:hidden;" onclick="CommentButton();" onmouseover="this.style.cursor=\'pointer\';" ><img src="'.$GLOBALS['common_path'].'comment.jpg" /></div></form>
	<div style="clear:both;"></div></div>';																												
	return($return);
	}
	
function NewLine($msg)
	{
	$return .= '<div style="margin-top:5px;"></div>
	<fb:like href="'.$GLOBALS['fb_path'].$msg['guid'].'" layout="button_count" show-faces="false" width="90" action="like" colorscheme="light" font="lucida grande"></fb:like>
	<!--<iframe src="http://www.facebook.com/plugins/like.php?href='.$GLOBALS['fb_path'].$msg['guid'].'&amp;layout=button_count&amp;show_faces=false&amp;width=90&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:90px; height:20px;" allowTransparency="true"></iframe>--><a style="font-size:13px;font-weight:bold;" href="'.$GLOBALS['fb_path'].$msg['guid'].'" target="_top" >'.CutString($msg['msg'],200,3,'<span style="color:#3B5998;"> More...</span>').'</a><div style="clear:both;"></div>
	<div style="margin-top:5px;border-bottom:1px solid #E9E9E9;width:150px;height:1px;margin-bottom:2px;"></div><div style="clear:both;"></div>';
	return($return);
	}

function TopThinkersLine($uid,$points,$fname,$lname)
	{
	$return .= '<div style="float:left;width:730px;">
	<div style="float:left;margin-right:10px;"><a href="'.$GLOBALS['fb_path'].$uid.'"  target="_top"><img border="0" width="50" src="http://graph.facebook.com/'.$uid.'/picture" /></a></div>
	<div style="float:left;width:545px;overflow:hidden;" class="msgline_msg"><a style="color:#3B5998;" href="'.$GLOBALS['fb_path'].$uid.'" target="_top" onmouseover="this.style.textDecoration = \'underline\';" onmouseout="this.style.textDecoration = \'none\';">'.$fname. ' '.$lname.'</a><br />
'.$points.' points<br />
</div>
	<div style="float:right;"><fb:like href="'.$GLOBALS['fb_path'].$uid.'" layout="button_count" show-faces="false" width="100" action="like" colorscheme="light" font="lucida grande"></fb:like><!--<iframe src="http://www.facebook.com/plugins/like.php?href='.$GLOBALS['fb_path'].$uid.'&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;colorscheme=light&amp;height=50" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:50px;" allowTransparency="true"></iframe>--></div>
	</div>
	<div style="clear:both;"></div>
	<div style="margin-top:5px;border-bottom:1px solid #E9E9E9;width:600px;height:1px;margin-bottom:10px;"></div>
 ';	
	return($return);
	}
	
function StreamMenu($order)
	{
	global $orders,$template_phrases;
	if ($order == 'top')
		$order_button = '<a style="color:#3B5998;"  href="'.$GLOBALS['fb_path'].'?view=recent" target="_top">Recent '.$template_phrases[2].'</a>';
	if ($order == 'recent')
		$order_button = '<a style="color:#3B5998;" href="'.$GLOBALS['fb_path'].'?view=top" target="_top">Top '.$template_phrases[2].'</a>';
		
	$return .='
	<div style="width:702px;">
	<div class="msgline_line" style="float:left;font-size:19px;width:530px;"><img src="" width="15" height="15" /> '.$orders[$order]['feed'].' '.$template_phrases[2].'</div>
	<div class="msgline_line" style="float:left;font-size:19px;margin-left:3px;width:160px;">'.$order_button.'</div>
	</div>
	<div style="clear:both;"></div>
	<div style="border-bottom:1px solid #AAAAAA;width:702px;height:1px;margin-bottom:0px;"></div>
	<div style="clear:both;"></div>
	';
	return($return);	
	}
	
function ProfileMenu($profile_name)
	{
	global $template_phrases;
	$return .='
	<div style="width:702px;">
	<div class="msgline_line" style="float:left;font-size:19px;"><img src="" width="15" height="15" />'.$profile_name.'\'s '.$template_phrases[2].'</div></div>
	<div style="clear:both;"></div>
	<div style="border-bottom:1px solid #AAAAAA;width:702px;height:1px;margin-bottom:10px;"></div>
	<div style="clear:both;"></div>
	';
	return($return);	
	}
	
function TopThinkersMenu()
	{
	global $template_phrases;
	$return .='
	<div style="width:702px;">
	<div class="msgline_line" style="float:left;font-size:19px;"><img src="" width="15" height="15" />'.$template_phrases[1].'</div></div>
	<div style="clear:both;"></div>
	<div style="border-bottom:1px solid #AAAAAA;width:702px;height:1px;margin-bottom:10px;"></div>
	<div style="clear:both;"></div>
	';
	return($return);	
	}
	
function AddLikeBox()
	{
	global $template_phrases;
	$guid = GenerateCGuid();
	$return .= '<div id="txtarea_div" style="float:left;margin-bottom:0px;width:750px;">
	<iframe name="like_form_frame" width="0" height="0" frameborder="0"></iframe>
	<form name="like_form"  target="like_form_frame" action="'.$GLOBALS['fb_path'].'submit.php" method="get" enctype = "application/x-www-form-urlencoded">
<div style="float:left;margin-left:20px;">
<textarea  onclick="ClearText(this);"  id="like_input" name="like_input" style="width:690px;height:30px;border:1px solid #B4BBCD;overflow:hidden;color:#777777;font-family:arial;padding:5px;">'.$template_phrases[6].'</textarea><input type="hidden" name="uid" id="uid" value="" /><input type="hidden" name="guid" id="addguid" value="'.$guid.'" />
</div>
	</div>
	<div style="clear:both;"></div>
	<div id="sharebuttonline" style="height:30px;overflow:hidden;width:750px;padding-top:5px;padding-bottom:5px;"><div id="sharebuttoncont" style="width:58px;float:right;margin-right:25px;margin-bottom:10px;">
	<div style="float:right;">	<img src="'.$GLOBALS['common_path'].'share.jpg" style="float:left;" onmouseover="this.style.cursor=\'pointer\';" onclick="ShareButtonF();" /></div></div><div style="float:right;margin-right:10px;padding-top:2px;"> <input type="checkbox" id="post_to_mywall" name="post_to_mywall" value="posttomywall" checked="yes" />Post '.$template_phrases[3].' to my wall</div>
	</div><div style="clear:both;"></div>
	</form>
 ';	
	return($return);
	}
	//<div style="border-bottom:1px solid #E9E9E9;width:600px;height:1px;margin-bottom:10px;"></div>
	
	
function CountLikes($url)
	{
	$page = false;
	$max_retries = 3;
		while ( (!$page) && $retries < $max_retries)
			{
			try 
				{ $lines = file("https://graph.facebook.com/?ids=".$url);}
			catch(Exception $e)
				{ $exception = $e->getMessage(); }
			foreach ($lines as $line)
				{
				$response .= $line;	
				}
			$page =  json_decode($response,true);
			$likes = $page[$url]['likes'];
			$retries ++;
			}
			
	return($likes);
	}


function CountLikesBatched($murls,$update,$report_top = false)
	{
	global $template_phrases;
	foreach ($murls as $urls)
		{
		$response = '';
		//echo 'started ith '.$urls.'<br />';
		try 
			{ $lines = file("https://graph.facebook.com/?ids=".$urls);}
		catch(Exception $e)
			{ $exception = $e->getMessage(); }
		foreach ($lines as $line)
			{
			$response .= $line;	
			}
		
		$page =  json_decode($response,true);
		$i = 0;
	//	print_r ($page);
		foreach ($update as $key => $row)
			{
			$url = $GLOBALS['fb_path'].$key;
			//echo $url.'<br />';
			$like = $page[$url]['likes'];
			//echo $like.'<br />';
			if ($like != '' && $like > 0)
				{
			//	echo 'i can has '.$like.' on '.$key.'<br />';
				$likes[$key] = $like;
				}
			$i ++;
			}
		}
	return($likes);
	}
	
	
function GetLevel($points)
	{
	global $levels;
	$i = 0;
	foreach ($levels as $level)
		{
		$i ++;
		if ($points >= $level['xp'])
			$return = $i;
		else
			break;
		}
	return($return);
	}
	
function GiveCollect($uid,$guid,$points)
	{
//	echo ('give collect started with '.$uid.' and '.$guid.' and '.$points.'<br />');
	if ($uid != '' && $guid != '' && $points > 0)
		{			
		$query = "select guid from collect where guid = '".$guid."'";
		mysql_query_alert($query);
		if (mysql_affected_rows() == 0)
			{
			$query = "insert into collect values ('".$guid."','".$uid."','".$points."',0,".time().")";
			mysql_query_alert($query);
			}
		if (mysql_affected_rows() > 0)
			{
			$query = "update collect set points = ".$points." where guid = '".$guid."'";
			$result = mysql_query_alert($query);
			}
		}
	return(true);
	}
	
function GivePoints($uid,$points)
	{
//	CreateAlert("give points startd with ".$uid. ' '.$points);
	if ($uid != '' && $points > 0)
		{
			
		$query = "update users set points = points + ".$points." where user_id = ".$uid;
		$result = mysql_query_alert($query);
	//	if (mysql_affected_rows() == 0)
//				CreateAlert($query.' failed '.mysql_error());
//		else
//			CreateAlert($query." query affected ".mysql_affected_rows()." rows");
		}
	}

function TakePoints($uid,$points)
	{
//	CreateAlert("give points startd with ".$uid. ' '.$points);
	if ($uid != '' && $points > 0)
		{
			
		$query = "update users set points = points - ".$points." where user_id = ".$uid;
		$result = mysql_query_alert($query);
	//	if (mysql_affected_rows() == 0)
//				CreateAlert($query.' failed '.mysql_error());
//		else
//			CreateAlert($query." query affected ".mysql_affected_rows()." rows");
		}
	}
		
	
	
	
	
function AddMsg($uid)
	{
	//CreateAlert("ran with ".$uid);
	$query = "update users set msgs = msgs + 1 where user_id = ".$uid;
	$result =  mysql_query_alert($query);
//	if (mysql_affected_rows() == 0)
//		CreateAlert($query.' failed '.mysql_error());
	return($result);
	}

function CountUserLikesBatched($user_id,$report_top = false,$max = 20)
	{
	global $template_phrases;
$query = "select guid,spread from msgs where user_id = '".$user_id."'";
$result = mysql_query_alert($query);
//echo $query.'<br />';
$i = 0;
$k = 0;
while ($vresult = mysql_fetch_array($result))
	{
	$update[$vresult['guid']]['likes']  = $vresult['spread'];
	if ($i % 50 == 0 && $i > 0)
		{
		$murls[$k] = chop($murls[$k],',');
		$k ++ ;
		}
	$murls[$k] .= $GLOBALS['fb_path'].$vresult['guid'].',';
	$i ++;
	}
$murls[$k] = chop($murls[$k],',');
//$urls = chop($urls,',');
$total = mysql_affected_rows();
$i = 0;
print_r ($murls);
$likes = CountLikesBatched($murls,$update);
echo sizeof($likes).'<br />';
foreach ($likes as $key => $row)
	{
	//echo $key .' r:'.$row.' g:'.$update[$key]['likes'].'<br />';
	$update[$key]['gap'] = ($row - $update[$key]['likes']);
	$update[$key]['points'] = $row;	
	}


	$query = "select p_likes from users where user_id = '".$user_id."'";
	$result = mysql_query_alert($query);
	//echo $query.'<br />';
	while ($vresult = mysql_fetch_array($result))
		{
		$p_likes = $vresult['p_likes'];		
		}
		
	$dp_likes = (CountLikes($GLOBALS['fb_path'].$user_id)) ;
	if ( ($dp_likes - $p_likes) > 0)
		{
		//echo "giving ".$uid." points ".(5 *  $dp_likes)." for ".$uid."<br />";
		$query = "update users set p_likes = ".$dp_likes.",scanned = ".time()." where user_id = ".$user_id;
		mysql_query_alert($query);
		$givepoints += (5 *  ($dp_likes - $p_likes)); 
		}
	else
		{
		$query = "update users set scanned = ".time()." where user_id = ".$user_id;
		mysql_query_alert($query);
		}
	
foreach ($update as $guid => $likes)
		{
		if ($likes['gap'] > 0)
			{
			//echo "giving ".$uid." points ".$likes['gap']." for ".$guid.' giving collect '.$likes['points'].'<br />';
			$query = "update msgs set spread = spread + ".$likes['gap'].",scanned = ".time()." where guid = '".$guid."'";
			mysql_query_alert($query);
			GiveCollect($user_id,$guid,$likes['points']);
			}
		else
			{
			$query = "update msgs set scanned = ".time()." where guid = '".$guid."'";
			mysql_query_alert($query);
			}
		}
	return(true);	
	}
	
function CountUserLikes($uid,$report_top = false)
	{
	global $template_phrases;
	$query = "select guid,spread from msgs where user_id = '".$uid."'";
	$result = mysql_query_alert($query);
	//echo $query.'<br />';
	while ($vresult = mysql_fetch_array($result))
		{
		$update[$vresult['guid']]['likes']  = $vresult['spread'];
		}
	$total = mysql_affected_rows();
	$i = 0;
	foreach ($update as $guid => $likes)
		{
		//echo $guid .' has '. $likes['likes'] .' now counting<br />';
		$d_likes = CountLikes($GLOBALS['fb_path'].$guid);	
		$update[$guid]['gap'] = ($d_likes - $likes['likes']);
		$update[$guid]['points'] = $d_likes;
		$i ++;
		if ($report_top)
			{
			echo('<script type="text/javascript">parent.parent.document.getElementById(\'scannew\').innerHTML = \'<img src="'.$GLOBALS['common_path'].'loading.gif" width="20" height="20" /> Scanned '.$i.' / '.$total.' '.$template_phrases[2].'.\';</script>');
			}
		}
		
	// so i dont lock the database
	$query = "select p_likes from users where user_id = '".$uid."'";
	$result = mysql_query_alert($query);
	//echo $query.'<br />';
	while ($vresult = mysql_fetch_array($result))
		{
		$p_likes = $vresult['p_likes'];		
		}
		
	$dp_likes = (CountLikes($GLOBALS['fb_path'].$uid)) ;
	if ( ($dp_likes - $p_likes) > 0)
		{
		//echo "giving ".$uid." points ".(5 *  $dp_likes)." for ".$uid."<br />";
		$query = "update users set p_likes = ".$dp_likes.",scanned = ".time()." where user_id = ".$uid;
		mysql_query_alert($query);
		$givepoints += (5 *  ($dp_likes - $p_likes)); 
		}
	else
		{
		$query = "update users set scanned = ".time()." where user_id = ".$uid;
		mysql_query_alert($query);
		}
		
	foreach ($update as $guid => $likes)
		{
		if ($likes['gap'] > 0)
			{
			//echo "giving ".$uid." points ".$likes['gap']." for ".$guid."<br />";
			$query = "update msgs set spread = spread + ".$likes['gap'].",scanned = ".time()." where guid = '".$guid."'";
			mysql_query_alert($query);
			//$givepoints += $likes['gap'];
			GiveCollect($uid,$guid,$likes['points']);
			}
		else
			{
			$query = "update msgs set scanned = ".time()." where guid = '".$guid."'";
			mysql_query_alert($query);
			}
		}

//	if ($givepoints > 0)
//		GivePoints($uid,$givepoints);
	return($givepoints);
	}
	
	
function TrackUserLikes($uid)
	{
	$query = "select msg,guid,spread from msgs where user_id = '".$uid."'";
	$result = mysql_query_alert($query);
	//echo $query.'<br />';
	while ($vresult = mysql_fetch_array($result))
		{
		$update[$vresult['guid']]['likes']  = $vresult['spread'];
		$update[$vresult['guid']]['msg'] = $vresult['msg'];
		}
	foreach ($update as $guid => $likes)
		{
		//echo $guid .' has '. $likes['likes'] .' now counting<br />';
		$d_likes = CountLikes($GLOBALS['fb_path'].$guid);	
		echo $likes['msg'].'<br /><br /> now has '.$d_likes.'<br />';
		}
	}
	
function RegisterUserTC($facebook,$user_id)
{	

if (isset($user_id) && $user_id != '')
	{
	
	$query = "select user_id from users where user_id = '".$user_id."'";
	mysql_query_alert($query);
	if (mysql_affected_rows() == 0)
		{
			//echo "select first_name,last_name from user where uid = '".$user_id."'<br />;
		$user_info =  retryFQL("select first_name,last_name,locale from user where uid = '".$user_id."'",3,false);
		//$facebook->api_client-> users_getInfo($user_id,'last_name, first_name,username,locale');
		if (is_array($user_info))
			{
			$query = "insert into users values ($user_id,'','".ConvertQs($user_info[0]['first_name'])."','".ConvertQs($user_info[0]['last_name'])."','".$user_info[0]['locale']."',".time().",".time().",'','','".$_GET['s']."',0,'".$_GET['ref']."',0,1,'".$_GET['ref']."',0,0,'".$_GET['ref']."');";
			mysql_query_alert($query,array(1062));
			}
		else
			{
			$query = "insert into users values ($user_id,'','','','',".time().",".time().",'','','',0,0,0,1,'".$_GET['ref']."',0,0,'".$_GET['ref']."');";
			mysql_query_alert($query,array(1062));
			}
		$query = "delete from removed_users where user_id = '".$user_id."';";
		mysql_query_alert($query);
		
		}
	else
		{
		if ($_GET['ref'] != '' && isset($_GET['ref']))
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

function LevelBar($points)
	{
	global $levels;
	$bar_width = 113;
	$current_level = GetLevel($points);
	if ($points > 0)
		$level_bar_tip = ($points - $levels[$current_level]['xp']).'/'.($levels[($current_level + 1)]['xp'] - $levels[($current_level)]['xp']).' XP for level '.($current_level + 1);
	else
		$level_bar_tip = '';
	$progress = $points - $levels[$current_level]['xp'];
	$end = $levels[($current_level + 1)]['xp']  - $levels[$current_level]['xp'] ;
	$done_portion = floor($progress/$end*$bar_width);
	//echo $done_portion.'<br />'.$bar_width.'<br />'.$progress/$end.'<br />';
	$return.=('<div onmouseover="Tip(\''.$level_bar_tip.'\');" onmouseout="UnTip();" style="margin-left:5px;float:left;font-size:9px;"><div style="float:left;margin-right:5px;text-align:center;font-size:10px;font-weight:bold;" id="bar_points">'.$points.'<br />Points</div><div style="float:left;padding-top:5px;"><div style="width:'.$bar_width.'px;height:27px;background-image:url(barbg.jpg);"><div style="float:left;width:'.$done_portion.'px;height:20px;margin-left:5px;margin-top:6px;background-image:url(filler.jpg);background-repeat:repeat-x;" id="doneportion"></div>																																																																						</div></div><div style="float:left;text-align:center;margin-left:5px;font-size:10px;font-weight:bold;" id="leveldesc">Level '.$current_level.'<br />'.$levels[$current_level]['desc'].'</div></div><script type="text/javascript">var bar_width = '.$bar_width.';var current_level = '.$current_level.';var user_points = '.$points.';</script>');
	return($return);
	}
	
//<div style="float:left;font-size:15px;padding-top:9px;position:relative;left:5px;width:'.$bar_width.'px;text-align:center;" id="bar_xp">'.($points - $levels[($current_level)]['xp']).' / '.($levels[($current_level + 1)]['xp'] - $levels[($current_level)]['xp']).' XP</div>
//document.getElementById(\'bar_xp\').innerHTML = (bar_progress) + \' / \' + (level_xp[(current_level + 1)] - level_xp[current_level])+\' XP \';

function DeleteMsg($guid,$user_id)
	{
	if ($guid != '')
		{
		$query = "delete from msgs where guid = '".$guid."'";
		mysql_query_alert($query);
		if (mysql_affected_rows() > 0)
			TakePoints($user_id,5);
		$query = "delete from comments where guid  = '".$guid."'";
		mysql_query_alert($query);
		$query = "delete from flags where guid  = '".$guid."'";
		mysql_query_alert($query);
		}
	}
	
function DeleteComment($guid,$user_id)
	{
	if ($guid != '')
		{
		$query = "delete from comments where cguid = '".$guid."'";
		mysql_query_alert($query);
		if (mysql_affected_rows() > 0)
			TakePoints($user_id,1);
		}
	}
	
	
function RefreshButton()
	{
	return('<img border="0" border="0" style="color:red;" src="'.$GLOBALS['common_path'].'refresh.jpg" onmouseover="Tip(\'Check for likes\');this.style.cursor=\'pointer\';" onmouseout="UnTip();" onclick="javascript:Scan_Now();" />');	
	}

function LikeCounter($likes)
	{
	if ($likes > 0)
		$return = '<a href="'.$GLOBALS['fb_path'].'collect.php" target="_top" onmouseover="Tip(\'You have '.$likes.' new likes\');" onmouseout="UnTip();"><img  border="0" src="'.$GLOBALS['common_path'].'like.jpg" width="16" /><span style="color:red;"> '.$likes.'</span></a>';
	else
		$return = '<a onmouseover="Tip(\'You have no new likes\');" onmouseout="UnTip();"><img border="0" src="'.$GLOBALS['common_path'].'like.jpg" width="16"  />0</a>';
	return($return);
	}
	
function NotifCounter($notifs)
	{
	if ($notifs > 0)
		$return = '<a href="'.$GLOBALS['fb_path'].'notifications.php" target="_top" onmouseover="Tip(\'You have '.$notifs.' notifications\');" onmouseout="UnTip();" ><img src="'.$GLOBALS['common_path'].'notifications.jpg" border="0" width="16" /><span style="color:red;"> '.$notifs.'</span></a>';
	else
		$return = '<a href="'.$GLOBALS['fb_path'].'notifications.php" target="_top" onmouseover="Tip(\'You have no new notifications\');" onmouseout="UnTip();"><img src="'.$GLOBALS['common_path'].'notifications.jpg" border="0" width="16" /> 0</a>';	
	return($return);
	}
	
function GetNotifMsg($type,$data)
	{
	//echo 'started ith '.$guid.'<br />';
	global $template_phrases;
	if ($type == 1)
		{
		//$commentor_id = GetCommentOwner($guid);
		//$user_name = GetUserName($commentor_id);
		$return = '<a href="'.$GLOBALS['fb_path'].$data['commentor_id'].'" target="_top" onmouseover="this.style.textDecoration = \'underline\';" onmouseout="this.style.textDecoration = \'none\';" style="color:#3B5998;">'.$data['user_name'].'</a> has commented on your <a href="'.$GLOBALS['fb_path'].$data['guid'].'" target="_top" onmouseover="this.style.textDecoration = \'underline\';" onmouseout="this.style.textDecoration = \'none\';" style="color:#3B5998;">'.$template_phrases[3].'</a>';
		}
		
	if ($type == 2)
		{
		//$commentor_id = GetCommentOwner($guid);
		//$user_name = GetUserName($commentor_id);
		$return = '<a href="'.$GLOBALS['fb_path'].$data['commentor_id'].'" target="_top" onmouseover="this.style.textDecoration = \'underline\';" onmouseout="this.style.textDecoration = \'none\';" style="color:#3B5998;">'.$data['user_name'].'</a> has commented in your <a href="'.$GLOBALS['fb_path'].$data['guid'].'" target="_top" onmouseover="this.style.textDecoration = \'underline\';" onmouseout="this.style.textDecoration = \'none\';" style="color:#3B5998;">thread</a>';
		}
		
		
	return($return);
	}
	
function GetNotifIcon($type)
	{
	//echo 'started ith '.$guid.'<br />';
	global $template_phrases;
	if ($type == 1)
		$return = $GLOBALS['common_path'].'comment_ico.jpg';
	if ($type == 2)
		$return = $GLOBALS['common_path'].'comment_ico.jpg';
		
	return($return);
	}
	

?>