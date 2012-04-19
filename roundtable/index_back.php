<?php 
require('dbc.php');
require('functions.php');
require('db_functions.php');
require('init.php');

//echo $_SESSION['locale']."<br />";
//echo $_SESSION['perms'].'<br />';
UpdatePartiCounters();
$tid = 0;
if ($has_session)
{
MetaHeaderExtra($GlobalKeywords . ' '.stripslashes($GlobalTitle),stripslashes($GlobalTitle) . " ". stripslashes($GlobalDesc),' '.stripslashes($GlobalTitle));
require('top.php');

echo ('
<div id="popup_cont"></div>
<div style="background-image:url(\'fp.jpg\');width:750px;height:600px;"></div>
<div style="position:absolute;left:490px;top:120px;width:90px;height:30px;cursor:pointer;"></div>
<div style="position:absolute;left:395px;top:120px;width:90px;height:30px;cursor:pointer;" onclick="virtualtour_popup();"></div>
<div style="position:absolute;left:308px;top:120px;width:80px;height:30px;cursor:pointer;" onclick="inviteFriends();"></div>
<div style="position:absolute;left:128px;top:120px;width:175px;height:30px;cursor:pointer;" onclick="coming_soon();"></div>
<div style="position:absolute;left:0px;top:120px;width:122px;height:30px;cursor:pointer;" onclick="coming_soon();"></div>
<div style="position:absolute;left:0px;top:330px;width:170px;height:210px;cursor:pointer;" onclick="suggest_topic_popup();"></div>
<div style="position:absolute;left:175px;top:330px;height:200px;width:400px;cursor:pointer;color:#00457c;font-family:arial;font-size:20px;font-weight:bold;text-align:center;padding-top:15px;" onclick="top.location=\''.$GLOBALS['fb_path'].'/table.php?tid=0\'">כנסו להתנסות בשולחן לדוגמא</div>
<div style="position:absolute;top:150px;height:380px;width:175px;cursor:pointer;" onclick="inviteFriends();"></div>
<a style="border:0px;" href="http://www.arvut.org" target="_blank"><img src="bot_banner.jpg" /></a>

	');
	
	
require('footer.php');
}
?>

