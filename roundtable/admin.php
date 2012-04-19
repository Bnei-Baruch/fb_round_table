<?php 
require('dbc.php');
require('functions.php');
$scope_perms = "email,create_event,publish_actions";
$connect_redirect = "admin.php";
require('init.php');

//echo $_SESSION['locale']."<br />";

ReloadPerms($user_id);
MetaHeaderExtra($GlobalKeywords . ' '.stripslashes($GlobalTitle),stripslashes($GlobalTitle) . " ". stripslashes($GlobalDesc),' '.stripslashes($GlobalTitle));
if ($has_session && CheckPerms('superadmin'))
{
$open_msg = "";
$invites_send = false;
if (isset($_POST['table_topic']))
	{
	$can_open = true;
	$tid = GenerateTid();
	$started = StripUserInput($_POST['table_starting']);
	$state = "active";
	$type = StripUserInput($_POST['table_type']);
	$topic = StripUserInput($_POST['table_topic']);
	$invites = StripUserInput($_POST['invites_c']);
	$invites_array = explode(',',$invites);
	$invites_for_request = array();
	if ($topic == "" or strlen($topic) == 0)
		{
		$can_open = false;
		$error = "יש לבחור נושא לשולחן";
		}
	if ($started == "" or $started == 0)
		{
		$can_open = false;
		$error = "יש לבחור זמן פתיחה לשולחן";
		}
	if ($started < time() - 60*60*3)
		{
		$can_open = false;
		$error = "יש לבחור זמן פתיחה בעתיד";
		}
	if ($started < (time() + 60*60*12) && $type == "preord")
		{
		$can_open = false;
		$error = "אירוע מתוכנן מראש יש לקבוע לפחות יום אחד קדימה";
		}
	$query = "insert into tables  values ('".$tid."','".$started."',0,'".$state."','".$type."',0,1,8,'".$invites."','".$topic."');";
	mysql_query_alert($query);
	$event_open_msg = "";
	if ($type == "preord" && $can_open)
		{
		$attach = array("name" => " שולחן עגול בפייסבוק בנושא ".$topic,"description" => "שולחן עגול בפייסבוק בנושא ".$topic."\n להשתתפות בדיון יש להיכנס ל http:".$GLOBALS['fb_path']."/table.php?tid=".$tid,"start_time" => ($started - 60*60*2),"end_time" => ($started - 60*60),"location" => "אפליקציית שולחן עגול","privacy" => "OPEN" );
		$event = GraphAPIPost('me/events',$attach); 
	//	$facebook->api($event['id'].'/picture','POST',$attach,'http:'.$GLOBALS['site_path'].'achivment.jpg');  ???
	//	if ($invites != "")
	//		$facebook->api($event['id'].'/invited?users='.$invites,'POST',$attach); 
		if (!$event)
			$event_open_msg = '<br /><span style="color:red;">אירעה בעיה בפתיחת אירוע פייסבוק, יש לפתוח את האירוע ידנית</span>';
		}
	if ($can_open)
		$open_msg = '<div style="float:'.FloatDir().';font-size:14px;font-weight:bold;font-family:arial;">השולחן נפתח בהצלחה!'.$event_open_msg .'</div><div style="clear:both;"></div>';
	else
		$open_msg = '<div style="float:'.FloatDir().';font-size:14px;font-weight:bold;font-family:arial;"><span style="color:red;">* '.$error.'</span></div><div style="clear:both;"></div>';
	
	if ($can_open)
		{
		$fql = ("select uid2 from friend where uid1 = '".$user_id."';");
		$param = array('method' => 'fql.query','query' => $fql,'callback' => '' );
		$friend_users = GraphFQL($param);
		foreach ($friend_users as $row)
			{
		
			if (in_array($row['uid2'],$invites_array))
				{
				
				if (!in_array($row['uid2'],$invites_for_request) && $row['uid']  != "")
					array_push($invites_for_request,$row['uid']);
				}
			}
		$fql = ("select uid from user where uid in (".$invites.") AND is_app_user = 1");
		$param = array('method' => 'fql.query','query' => $fql,'callback' => '' );
		$appusers = GraphFQL($param);
		foreach ($appusers  as $row)
			{
			if (in_array($row['uid'],$invites_array))
				{
				if (!in_array($row['uid'],$invites_for_request) && $row['uid']  != "")
					array_push($invites_for_request,$row['uid']);
				}
			}
		$invites_send = implode(",",$invites_for_request);
		
		}	
	}

require('top.php');

echo '
 <link type="text/css" rel="stylesheet" href="jscal/css/jscal2.css" />
    <link type="text/css" rel="stylesheet" href="jscal/css/border-radius.css" />
    <script src="jscal/js/jscal2.js"></script>
    <script src="jscal/js/unicode-letter.js"></script>
	<script src="jscal/js/lang/en.js"></script>

<div id="popup_cont" ></div>
<div style="background-image:url(\'fp.jpg\');width:750px;height:550px;">

<div style="margin-top:165px;margin-'.FloatDirOp().':15px;width:500px;height:250px;float:'.FloatDirOp().';font-family:arial;font-weight:bold;font-size:12px;color:#00437C;">

<span style="font-size:16px;color:#00457F;">פתיחת שולחן חדש</span><br /><br />
<form enctype="multipart/form-data" method="post" action="" id="open_table">

<div style="margin-top:10px;float:'.FloatDir().';text-align:'.FloatDirOp().';width:100px;height:20px;padding-top:3px;">נושא השולחן: </div><div style="margin-top:10px;float:'.FloatDir().';margin-right:10px;"> <input style="border:1px solid black;" id="table_topic" name="table_topic" type="text" size="50" maxlength="50" /></div><div style="clear:both;"></div>
<div style="margin-top:10px;float:'.FloatDir().';text-align:'.FloatDirOp().';width:100px;height:20px;padding-top:3px;">סוג השולחן: </div> <div style="margin-top:10px;float:'.FloatDir().';margin-right:10px;"> <select id="table_type" name="table_type" style="width:120px;border:1px solid black;"><option value="ffa">חופשי לכולם</option><option value="inv">מוזמנים בלבד</option><option value="preord">מתוכנן מראש</option></select></div><div style="clear:both;"></div>
<div style="margin-top:10px;float:'.FloatDir().';text-align:'.FloatDirOp().';width:100px;height:20px;padding-top:3px;">תאריך פתיחה: </div> <div style="margin-top:10px;float:'.FloatDir().';margin-right:10px;">  <div style="width:120px;border:1px solid black;height:20px;padding-top:3px;cursor:pointer;" id="picked_time_show" onclick="pickTimePopup();">'.date('H:i d/m/y',(time() + 60*60*3)).' <img style="margin-'.FloatDir().':5px;" src="cal.jpg" align="top" /></div><input id="table_starting" name="table_starting" type="hidden" size="20" value="'.time().'" /></div><div style="clear:both;"></div>
<div id="add_inv_title"></div>

<input type="hidden" name="invites_c" id="invites_c" />
<div style="float:'.FloatDirOp().';margin-'.FloatDirOp().':50px;margin-top:25px;"><a href="#" onclick="do_open_table();"><img src="create_table.jpg" border="0" /></a></div><br />
'.$open_msg.'
</div></form>
</div>
</div>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />

';


echo '
<script type="text/javascript">
var addinvs = 0;
var invites = Array();
var inv_r = "";
var friends = Array();

document.getElementById("table_type").onchange=function()
	{ 
	if (document.getElementById("table_type").value == "inv" || document.getElementById("table_type").value == "preord")
		{
		document.getElementById(\'add_inv_title\').innerHTML = 	\'<div style="margin-top:10px;float:'.FloatDir().';text-align:'.FloatDirOp().';width:100px;height:20px;padding-top:3px;">מוזמנים</div> <div style="margin-top:10px;float:'.FloatDir().';margin-right:10px;">  <div  id="add_inv_slot"></div></div><div style="clear:both;"></div>\';
		document.getElementById(\'add_inv_slot\').innerHTML = 	\'<input type="text" id="table_add_inv" name="table_add_inv" size="30" /> <a onclick="AddInv();" style="cursor:pointer;">'.GetTrans(91).'</a><br />\';
		}
	else
		{
		document.getElementById(\'add_inv_title\').innerHTML = \'\';
		document.getElementById(\'add_inv_slot\').innerHTML = 	\'\';	
		}
	}

function AddInv()
	{
	
	if (document.getElementById(\'table_add_inv\').value != "")
		{
		id = document.getElementById(\'table_add_inv\').value; //fer sure	
		
		
		if (id.search(/facebook.com\/profile.php\?id=(\d+)/i) != -1) {
			id_a = id.match(/facebook.com\/profile.php\?id=(\d+)/i);	
			do_add_invite(id_a[1]);  }
		else
			{
			if (id.search(/facebook.com\/(.*)?/i) != -1) 
				{
				id_a = id.match(/facebook.com\/(.*)?/i);	
				FB.api("/"+id_a[1], function(response) {
					do_add_invite(response.id);   });
				}
	
			}
		}

	
	}

function do_add_invite(id)
	{
	var inv_c = "";

	if (!invites[id] && id != "" && id != undefined) 
		{
		invites[id] = true;
		document.getElementById(\'add_inv_slot\').innerHTML = \'<div id="inv_slot_\'+id+\'" > <div style="float:right;margin-bottom:3px;" ><input type="hidden" id="inv_\'+addinvs+\'" name="inv_\'+addinvs+\'" value="\'+id+\'" /></div><div style="float:right;"><img src="//graph.facebook.com/\'+id+\'/picture" align="top" />\'+get_username(id)+\' </div><div style="float:left;"><a onclick="RemInv(\\\'\'+id+\'\\\');" style="cursor:pointer;">X מחק</a></div><br /></div><div style="clear:both;"></div>\' + document.getElementById(\'add_inv_slot\').innerHTML;
		addinvs ++;
		for (key in invites) 
			{
			if (invites[key]) 	{
				inv_c += key + ",";   }
			}
		inv_c = inv_c.slice(0,(inv_c.length - 1));
	
		document.getElementById(\'invites_c\').value = inv_c;
		}
	
	}
	
	
function RemInv(id)
	{
	document.getElementById(\'inv_slot_\'+id).innerHTML = "";
	invites[id] = false; 
	}

function getDateInfo(date, wantsClassName) {
  var as_number = Calendar.dateToInt(date);
	
};

function do_open_table()
	{
	
	open_table.submit();
	}
 

function pickTimePopup()
	{
	popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>בחירת תאריך פתיחה</div><br /><div  dir="ltr" style="text-align:center;dir:ltr;margin-bottom:10px;" id="cal_cont"></div>\'+popup_button_line(\''.GetTrans(90).'\',\'doPickTime();\')+\'\'+popup_button_line(\''.GetTrans(79).'\',\'close_popup();\')+\'</div>\',300,325,200,300);	 
	CALENDAR = Calendar.setup({
			cont: "cal_cont",
			weekNumbers: false,
			selectionType: Calendar.SEL_SINGLE,
			showTime: 24,	});
 
	}
	

function doPickTime()
	{
	if (CALENDAR.selection.print("%s") != "" && CALENDAR.selection.print("%s") != 0)
		{
		document.getElementById("table_starting").value = (CALENDAR.selection.print("%s"));
		var pickedTime = new Date(CALENDAR.selection.print("%s")*1000);
		document.getElementById("picked_time_show").innerHTML = (pickedTime.getDate() + "/"+(pickedTime.getMonth()+1)+"/"+(pickedTime.getFullYear() % 1000) + " "+pickedTime.getHours()+":"+pickedTime.getMinutes());
		close_popup();
		}
	}
function runPageFunctions()
	{';
	if ($invites_send)
		echo 'FB.ui({method: \'apprequests\',to:\''.$invites_send.'\', display:\'popup\',message: \'בואו להשתתף איתי בשולחן ולהיות משפיעים חברתית\'});';
	echo'
	}
var CALENDAR = "";
</script>';

require('footer.php');
}
else
	{
	require('top.php');
	echo('<div id="popup_cont" ></div>
	<div style="background-image:url(\'fp.jpg\');width:750px;height:550px;">
	<div style="margin-top:165px;margin-'.FloatDirOp().':15px;width:500px;height:250px;float:'.FloatDirOp().';font-family:arial;font-weight:bold;font-size:12px;color:#00437C;">
	<span style="font-size:16px;color:#00457F;">אין לך את ההרשאות לפתוח שולחן חדש</span><br />
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

