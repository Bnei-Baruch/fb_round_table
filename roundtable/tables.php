<?php 
require('dbc.php');
require('functions.php');
require('init.php');

//echo $_SESSION['locale']."<br />";
//echo $_SESSION['perms'].'<br />';

$tid = 0;
if ($has_session)
{
MetaHeaderExtra($GlobalKeywords . ' '.stripslashes($GlobalTitle),stripslashes($GlobalTitle) . " ". stripslashes($GlobalDesc),' '.stripslashes($GlobalTitle));
require('top.php');

echo ('
<div id="popup_cont"></div>
<div style="background-image:url(\'fp_blank.jpg\');width:750px;height:560px;margin:0px;border:0px;">');
echo '
<div style="position:absolute;left:0px;top:150px;width:575px;height:400px;overflow-y:scroll;">
<b>שולחנות פתוחים</b><br />';

$query = "select * from tables order by started;";
$result = mysql_query_alert($query);
$i = 1;
if (mysql_affected_rows() > 0)
	{
	while ($vresult = mysql_fetch_array($result))
		{
		echo '<div style="float:'.FloatDir().';width:260px;margin-'.FloatDir().':10px;height:81px;background-image:url(\'table_select.jpg\');margin-bottom:10px;cursor:pointer;" onclick="top.location = \''.$GLOBALS['fb_path'].'/table.php?tid='.$vresult['id'].'\';"><div style="float:'.FloatDir().';margin-'.FloatDir().':40px;margin-top:26px;width:50px;height:25px;font-family:arial;font-size:15px;font-weight:bold;color:#3b5998;">100</div><div style="float:'.FloatDir().';width:165px;height:80px;font-family:arial;color:#3b5998;"><span style="font-weight:bold;">סטטוס השולחן:</span>פתוח<br /><span style="font-weight:bold;">נושא השולחן:</span>'.$vresult['topic'].'</div></div>';
		if ($i % 2 == 0)
			echo '<div style="clear:both;"></div>';
		$i ++;
		}
	}
else
	{
	echo 'No opened tables<br />';
	}
	


echo('</div></div><a style="border:0px;" href="http://www.arvut.org" target="_blank"><img src="bot_banner.jpg" /></a>
<div style="position:absolute;left:490px;top:120px;width:90px;height:30px;cursor:pointer;"></div>
<div style="position:absolute;left:395px;top:120px;width:90px;height:30px;cursor:pointer;" onclick="coming_soon();"></div>
<div style="position:absolute;left:308px;top:120px;width:80px;height:30px;cursor:pointer;" onclick="inviteFriends();"></div>
<div style="position:absolute;left:128px;top:120px;width:175px;height:30px;cursor:pointer;" onclick="coming_soon();"></div>

');


	
require('footer.php');
}
?>




