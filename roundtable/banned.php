<?php 
require('dbc.php');
require('functions.php');
$scope_perms = "email,create_event,publish_actions";
$connect_redirect = "admin.php";
require('init.php');
$rows = 0;

function BannedRow($uid,$tid,$topic,$name)
	{
	global $rows;
	$return = "";
	$return .= '<div id="banned_row_'.$rows.'" style="height:70px;width:500px;background-color:#EDF8F5;"><div style="float:right;margin-top:10px;margin-right:10px;"><img src="//graph.facebook.com/'.$uid.'/picture" border="0" /></div><div style="margin-top:10px;margin-right:5px;float:right;font-family:arial;font-weight:bold;font-size:12px;color:#00437C;">משתמש: '.$name.'<br /><span style="color:black;">שולחן: '.$topic.'</span></div><div style="float:left;margin-top:40px;cursor:pointer;" onclick="remove_ban(\''.$tid.'\',\''.$uid.'\',\''.$rows.'\');" ><img src="remove_block.jpg" /></div></div><div style="clear:both;"></div>';
	$rows ++;
	return ($return);
	}
//echo $_SESSION['locale']."<br />";
$query = "select banned,tid from bans order by timestamp desc;";
$result = mysql_query_alert($query);
while ($vresult = mysql_fetch_array($result))
	{
	$bans[$i]['uid'] = $vresult['banned'];
	$bans[$i]['tid'] = $vresult['tid'];
	$i ++;
	}

$query = "select topic,id from tables where id in (select tid from bans);";
$result = mysql_query_alert($query);
while ($vresult = mysql_fetch_array($result))
	{
	$topics[$vresult['id']] = $vresult['topic'];
	}
	
$query = "select user_id,fname, lname from users where user_id in (select banned from bans);";
$result = mysql_query_alert($query);
while ($vresult = mysql_fetch_array($result))	
	{
	$names[$vresult['user_id']] = $vresult['fname'].' '.$vresult['lname'];
	}
//print_r($topics);
ReloadPerms($user_id);
MetaHeaderExtra($GlobalKeywords . ' '.stripslashes($GlobalTitle),stripslashes($GlobalTitle) . " ". stripslashes($GlobalDesc),' '.stripslashes($GlobalTitle));
if ($has_session && CheckPerms('superadmin'))
{
$open_msg = "";


require('top.php');


echo '
<div id="popup_cont" ></div>
<div style="background-image:url(\'fp.jpg\');width:750px;height:550px;">
<div style="margin-top:200px;margin-right:150px;float:right;">';
foreach ($bans as $row)
	{
	echo BannedRow($row['uid'],$row['tid'],$topics[$row['tid']],$names[$row['uid']]);
	}
echo '</div></div>
<script type="text/javascript">
function remove_ban(tid,uid,row)
	{
	var url = "do.php?eid=64&tid="+tid+"&data="+uid;
	var xmlObject = initXmlObject();	
	xmlObject.onreadystatechange=function()
		{
		if(xmlObject.readyState == 4)
			{
			if (xmlObject.responseText.search("OK") != -1)
				{
				document.getElementById(\'banned_row_\'+row).style.height = \'0px\';
				document.getElementById(\'banned_row_\'+row).innerHTML = \'\';
				popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>החסימה הוסרה</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75,200,300);
				}
			}
		}
	xmlObject.open("GET", url);
	xmlObject.send();	
	}

function runpageFunctions() { }

</script>
';
require('footer.php');
}
else
	{
	require('top.php');
	echo('<div id="popup_cont" ></div>
	<div style="background-image:url(\'fp.jpg\');width:750px;height:550px;">
	<div style="margin-top:165px;margin-'.FloatDirOp().':15px;width:500px;height:250px;float:'.FloatDirOp().';font-family:arial;font-weight:bold;font-size:12px;color:#00437C;">
	<span style="font-size:16px;color:#00457F;">אין לך את ההרשאות לנהל את דף החסומים</span><br />
	</div></div>
	
	');
	require('footer.php');
	}
	
	
if (false)
{

echo'
<div style="clear:both;"></div><br />
<b>Open tables:</b><br />
<div style="float:left;width:300px;">Topic</div><div style="float:left;width:75px;">Started</div><div style="float:left;width:60px;">State</div><div style="float:left;width:60px;">Type</div><div style="float:left;width:150px;">Invites</div><div style="clear:both;"></div>';
	
$query = "select * from tables";
$result = mysql_query_alert($query);
if (mysql_affected_rows() > 0)
	{
	while ($vresult = mysql_fetch_array($result))
		{
		echo '<div style="float:left;width:300px;">'.$vresult['topic'].'</div><div style="float:left;width:75px;">'.date('d|m|y H:i:s',($vresult['started'] + 60)).'</div><div style="float:left;width:60px;">'.$vresult['state'].'</div><div style="float:left;width:60px;">'.$vresult['type'].'</div><div style="float:left;width:150px;">'.$vresult['invites'].'</div><div style="clear:both;"></div>';		
		}
	}
else
	{
	echo 'No opened tables<br />';
	}
}

?>

