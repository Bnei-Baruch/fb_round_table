<?php
echo('
<div dir="'.$_SESSION['locale_dir'].'" >

<script src="json2.js" type="text/javascript"></script>
<script type="text/javascript">
FB_Path = \''.$GLOBALS['fb_path'].'\';
session_perms = \''.$_SESSION['perms'].'\';
</script>
');
$auth = true;

	
echo('<div style="height:0px;width:0px;position:absolute;" id="fb-root"></div>
<script type="text/javascript">
	 var level_xp = new Array();
	 var level_desc = new Array();
	 ');

$i = 0;
foreach ($levels as $level)
	{
	echo('level_xp['.($i + 1).'] = '.$level['xp'].'; level_desc['.($i + 1).'] = \''.$level['desc'].'\';');
	$i ++;	
	}
echo('
var CountStepper = -1;
var SetTimeOutPeriod = (Math.abs(CountStepper)-1)*1000 + 990;

var dnow = new Date();
var do_polling = false;
var tid = "'.$tid.'";
var usernames_cache = Array();
var seats_state = Array();
var client_state = "DISCO";
var auth_user_id  = "'.$user_id.'";
var current_seat = "DISCO";
var sync_state = true;
var enable_lq_change = true;
var hosting_state = false;
var temp_cs_spot = 0;
var input_started =  new Object;
var side_chat_lines = 26000;
var poller_interval = 1000;
var last_poll_time = 0;
var start_time = new Date();
var current_speaker = 0;

var connect_retries = 0;

function evalJSON(string)
	{
	try {
        JSON.stringify(string);
       return(eval(\'(\'+string+\')\'));
    } catch (ex) {
        return false;
    }
	
	}
	
function retryFQL(fql,callback)
	{
	FB.api(
	  {
		method: "fql.query",
		query: fql
	  },
	  function(response) {
	 // alert(response);
		 callback(response);
	  }
	);
	
	}
	
function fetch_username(id)
	{
	//alert(id);
	retryFQL("SELECT name,uid FROM user WHERE uid="+id+";",ss_username);
	

	return("<span id=\"ss_"+id+"\"></span>");
	}

function ss_username(response)
	{
	document.getElementById(\'ss_\'+response[0].uid).innerHTML = response[0].name;
	if (usernames_cache[response[0].uid]) 	{
		usernames_cache[response[0].uid].name = response[0].name; }
	else 
		{
		usernames_cache[response[0].uid] = Array();
		usernames_cache[response[0].uid].name = response[0].name;
		}
	}
	
function get_username(id)
	{
	//alert("started w/"+id);
	if (usernames_cache[id] && usernames_cache[id].name) {
			//alert("getting from cash w/"+usernames_cache[id].name);
		return(usernames_cache[id].name); }
	else {
			//alert("fetching...");
		return(fetch_username(id)); }
	}
	
	
function getQueryVariable(variable,qs) {
        var query = qs;
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable) {
                return unescape(pair[1]);
            }
        }
     
    }	


function initXmlObject()
	{
	
	var xmlObject = GetXmlHttpObject();
	if(xmlObject == null) // no ajax support
		{
		return (false);
		}	
	else
		return(xmlObject);
	}
	
function GetXmlHttpObject()
	{
	if (window.XMLHttpRequest)
		{
		// code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
		}
	if (window.ActiveXObject)
		{
		// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
		}

	return null;
	}
	
	
	
function send_action(tid,eid,data)
	{
	add_debug("send started "+tid+ " w "+eid+" and "+data);
	var xmlObject = initXmlObject();
	//xmlObject.responseText = "";
	//alert(tid);
	var url = \'do.php?eid=\'+eid+\'&tid=\'+tid+\'&data=\'+data;
	//alert(url);
	if (eid == 11 || eid == 14)
		{
		document.getElementById(\'main_input\').disabled = true;
		document.getElementById(\'send_button\').innerHTML = \'<img src="fbloader.gif" />\';
		//document.getElementById(\'control_text\').innerHTML = "Sending your comment...";
		}
	
	if (eid == 10)
		{
		document.getElementById(\'side_input\').disabled = true;
		document.getElementById(\'send_button_side\').innerHTML = \'<img src="fbloader.gif" />\';
		}
	//alert(eid);
	xmlObject.onreadystatechange=function()
		{
		if(xmlObject.readyState == 4)
			{
			if (eid == 15)
				{
			//	alert(eid + xmlObject.responseText);
				
				}
			if (eid == 16)
				{
			
			//	alert(xmlObject.responseText);
				}
			if (eid == 10)
				{
				if (xmlObject.responseText.search("OK") != -1)
					{
					document.getElementById(\'side_input\').value = "";
					}
				if (xmlObject.responseText.search("BANNED") != -1) {
					you_are_banned(); }
				document.getElementById(\'side_input\').disabled = false;
				document.getElementById(\'send_button_side\').innerHTML = "'.GetTrans(54).'";
				
				}
				
			if (eid == 11 || eid == 14)
				{
				if (xmlObject.responseText.search("OK") != -1)
					{
					document.getElementById(\'main_input\').value = "";
					}
				if (xmlObject.responseText.search("BANNED") != -1) {
					you_are_banned(); }	
				else
					{
					//document.getElementById(\'control_text\').innerHTML = "Failed sending your comment, please try again";
					}
				document.getElementById(\'main_input\').disabled = false;
				document.getElementById(\'send_button\').innerHTML = "'.GetTrans(54).'";
				}	
			if (eid == 1)
				{
				//alert(xmlObject.responseText);
				if (xmlObject.responseText.search("FAIL_NO_SESSION") == -1)	
					{
					var joined  = "";
					var temp_data = xmlObject.responseText;
					var data = evalJSON(temp_data);
					//alert(data);
					for (row in data["join"])
						{
						joined = data["join"][row];
						}
					//alert(joined);
					if (joined.search("OK") != -1)
						{
						change_client_state("CROWD");
						for (i=0;i<9;i++)
							{
							free_seat(i); 
							}
						for (row in data["spots"])
							{
							if (data["spots"][row]["uid"] == auth_user_id) 
								{
								current_seat = data["spots"][row]["spot"];
								change_client_state("PARTI");
								if (current_seat == 0) 
									{
									hosting_state = true;
									high_ctrl_btns_all(); 
									}
								
								}
							occupy_seat(data["spots"][row]["spot"],data["spots"][row]["uid"]);
							}
						
						if (init_speaker != 0 && seats_state[init_speaker] == "TAKEN") {
							do_change_speaker(init_speaker,true);  }
						start_poller(tid);
						start_super_poll();
						pop_side_chat();
						document.getElementById(\'main_chat\').scrollTop = document.getElementById(\'main_chat\').scrollHeight;
						document.getElementById(\'side_chat\').scrollTop = document.getElementById(\'side_chat\').scrollHeight;	
						}
					else
						{
						change_client_state("DISCO");
						}
					}
				else
					{
					top.location = \''.$GLOBALS['fb_path'].'/table.php?tid=\'+tid;
					}
				}
				
			
			if (eid == 2)
				{
				var temp_data = xmlObject.responseText;
				var res = evalJSON(temp_data);
				for (row in res["state"]) {
					sitted = res["state"][row]; }
					
				for (row in res["sit"]) {
					spot = res["sit"][row]["spot"]; }	
						
				if (sitted.search("OK") != -1)
					{
					if (spot == 0)
						{
						//alert("you are the host now");
						hosting_state = true;
						high_ctrl_btns_all();
						}
					change_client_state("PARTI");
					occupy_seat(spot,auth_user_id);
					current_seat = spot;
				
					}
				if (sitted.search("BANNED") != -1)	
					{
					free_seat( spot);
					you_are_banned(); 
					}
				}	
				
			if (eid == 3)
				{
				if (xmlObject.responseText.search("OK") != -1)
					{
					
					if (current_seat == 0) {
					shut_ctrl_btns_all(); }
					shut_parti_buttons(current_seat);
					free_seat(current_seat);
					change_client_state("CROWD");
					hosting_state = false;
				
					}
			
				}
				
				
				
			if (eid == 6)
				{
				if (xmlObject.responseText.search("OK") != -1) {
					popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>'.GetTrans(70).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75);	}
				}
				
			if (eid == 12 || eid == 13)
				{
				high_parti_btns(current_seat); 
				}
				
			if (eid == 50)
				{
				//alert(xmlObject.responseText);
				if (xmlObject.responseText.search("OK") != -1)
					{
					
					}
				
				}
			if (eid == 60) {
				enable_lq_change = true; }
				
			if (eid == 61) 
				{
				//alert(xmlObject.responseText);
				if (hosting_state)	
					{
					do_change_speaker(temp_cs_spot); 
					}
				}
				
			if (eid == 63)
				{
				
				close_popup();
				if (xmlObject.responseText.search("OK") != -1)	{
					popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>'.GetTrans(81).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75,200,300);	 }
				else {
					}
				}
			}
		}
	xmlObject.open("GET", url, sync_state);
	xmlObject.send();	
	}
	
function perm_ask()
	{
	if (current_speaker == current_seat)	{
			popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>'.GetTrans(87).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75,200,300);	 }
	else
		{
		ChangeElement(\'ctrl_btns_\'+current_seat,\'<img src="fbloader.gif" />\');
		send_action(tid,12,current_seat);	
		}
	}
	
function perm_comment()
	{
	if (current_speaker == current_seat)	{
			popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>'.GetTrans(87).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75,200,300);	 }
	else
		{
		ChangeElement(\'ctrl_btns_\'+current_seat,\'<img src="fbloader.gif" />\');
		send_action(tid,13,current_seat);	
		}
	}
	
function virtualtour_popup()
	{
	popup(\'<br /><iframe width="700" height="500" src="http://www.youtube.com/embed/fdh50t5aAcE?autoplay=1" frameborder="0" allowfullscreen></iframe><div style="margin-top:5px;">\'+popup_button_line(\'סגור חלון\',\'close_popup();\')+\'</div>\',720,550,15,50);
	}

function how_to_get_points_popup()
	{
	popup(\'<br /><img src="how_points.jpg" border="0" /><div style="margin-top:5px;">\'+popup_button_line(\'סגור חלון\',\'close_popup();\')+\'</div>\',720,550,15,50);
	}
	
function rules_popup()
	{
	popup(\'<br /><b>שבעת עקרונות השולחן העגול </b><br /><br /><div style="padding:10px;text-align:'.FloatDir().';">1. אני מתחייב/ת לכבד את כל יושבי השולחן על אף דעותיהם השונות ולהתייחס לכל אחד ואחת מהם בכבוד.<br /><br />2. אני מתחייב/ת להשתדל ולקיים את הדיון סביב השולחן מתוך הרצון להגיע להסכמה הדדית בינינו, כמו בני משפחה שיושבים לדיון סביב שולחן אחד.<br /><br />3. אני מתחייב/ת להציג את דעותיי ועמדותיי בצורה אמיתית והוגנת ללא סילוף עובדתי.<br /><br />4. אני מתחייב/ת להישאר נאמן לדעותיי אך לתת מקום שווה לכל אחד שחושב אחרת ממני.<br /><br />5. אני מתחייב/ת לכבד את חוקי השולחן. לעמוד בזמן שהוקצב לי ולא להתפרץ לדבריו של מישהו אחר<br /><br />6. אני מתחייב/ת לא לנהוג באלימות מילולית או פיזית כלפי איש מיושבי השולחן או מקהל הצופים<br /><br />7. אני מבין/ה שבדרך יהיו בינינו חילוקי דעות ומחלוקות אבל אני מתחייב/ת שלא לפרק את אווירת המשפחה הזו, כי בית לא מפרקים</div><div style="clear:both;"></div><div style="margin-top:5px;">\'+popup_button_line(\'סגור חלון\',\'close_popup();\')+\'</div>\',620,425,15,50);
	}

function shut_parti_buttons(ssspot)
	{
	ChangeElement(\'ctrl_btns_\'+ssspot,"");
	}
	
function change_client_state(state)
	{
	client_state = state;
	//alert(state);
	if (state == "PARTI"){
		}
	if (state == "CROWD")
		{
		current_seat = "CROWD";
		}		
	}
	
//tid,stamp
function poll_events()
	{
	//alert(tid + " " + latest_poll);
	var xmlObject = initXmlObject();
	var url = \'poll.php?tid=\'+tid+\'&sid=\'+latest_poll ;

	xmlObject.onreadystatechange=function()
		{
		if(xmlObject.readyState == 4)
			{
			//alert(xmlObject.responseText);
			proc_events(xmlObject.responseText);			
			}
		}
	xmlObject.open("GET", url, true);
	xmlObject.send();		
	}

function add_debug(info)
	{
	if (auth_user_id == 594261257)
		{
		document.getElementById(\'debugz\').innerHTML += info + "<br />";
		}
	}
function proc_events(response)
	{
	add_debug(response);
	var data = evalJSON(response);
	var new_events = false;
	for (row in data["events"])
		{	
		for (row in data["sid"])
			{
			add_debug(data["sid"][row] + " plox "+latest_poll);
			if (data["sid"][row] > latest_poll)	
				{
				latest_poll = data["sid"][row]; 
				new_events = true;
				}
			}
		
		if (new_events)
			{
			for (row in data["events"])
				{
				var event_time = new Date();
				
				if (data["events"][row]["eid"] == 1)
					{
					inc_crowd_counter();
					}
					
				if (data["events"][row]["eid"] == 2)
					{
					dec_crowd_counter();
					//alert(data["events"][row]["user_id"] + " sat at " +data["events"][row]["data"]);
					if (auth_user_id != data["events"][row]["user_id"]) 
						{
						occupy_seat(data["events"][row]["data"],data["events"][row]["user_id"]); 
						if (hosting_state) {
						high_ctrl_btns(data["events"][row]["data"]); }
						}
					}
				
				if (data["events"][row]["eid"] == 3)
					{
					inc_crowd_counter();
					//alert(data["events"][row]["user_id"] + " got up from " +data["events"][row]["data"]);
					if (auth_user_id != data["events"][row]["user_id"]) 
						{
						if (hosting_state) {
						shut_ctrl_btns(data["events"][row]["data"]); }
						free_seat(data["events"][row]["data"]); 
						}
					}
				
				if (data["events"][row]["eid"] == 5)
					{
					dec_crowd_counter();
					}
				
				if (data["events"][row]["eid"] == 6)
					{
					//alert(data["events"][row]["data"]);
					invites[data["events"][row]["data"]] = true;
					if (auth_user_id == data["events"][row]["data"]) 
						{
						popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>'.GetTrans(71).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75);
						for (i in seats_state)
							{
							if (seats_state[i] == "OPEN")	{
								free_seat(i); }
							}
						}
					}	
					
				if (data["events"][row]["eid"] == 10)
					{
					side_chat_lines ++;
					document.getElementById(\'side_chat\').innerHTML += \'<div onmouseover="high_side_btns(\\\'side_inv_\'+side_chat_lines+\'\\\');" onmouseout="hide_side_btns(\\\'side_inv_\'+side_chat_lines+\'\\\');" onclick="side_options_popup(\\\'\'+data["events"][row]["user_id"]+\'\\\',\\\'side_inv_\'+side_chat_lines+\'\\\');" style="float:right;margin-right:10px;font-family:arial;font-size:12px;font-weight:bold;color:#00457c;cursor:pointer;"><div style="float:'.FloatDir().';width:140px;"><div style="float:'.FloatDir().';"><a href="//www.facebook.com/profile.php?id=\'+data["events"][row]["user_id"]+\'\" target="_blank"><img src="//graph.facebook.com/\'+data["events"][row]["user_id"]+\'/picture" width="25" height="25" /></a></div><div style="float:'.FloatDirOp().';height:10x;font-size:8px;width:100px;text-align:'.FloatDirOp().';" id="side_inv_\'+side_chat_lines+\'">.</div><div style="float:'.FloatDir().';"> <a href="//www.facebook.com/profile.php?id=\'+data["events"][row]["user_id"]+\'\" style="color:#00457c;" target="_blank">\'+ get_username(data["events"][row]["user_id"]) +\'</a></div></div><div style="clear:both;"></div><span style="font-weight:normal;color:#777777;">\'+data["events"][row]["data"]+\'</span></div><div style="float:'.FloatDir().';margin-'.FloatDir().':10px;font-family:arial;font-weight:normal;font-size:12px;color:#00457C;" id="like_counter_\'+data["events"][row]["sid"]+\'">0</div>\'+get_like_button(data["events"][row]["sid"],data["events"][row]["user_id"])+\'<div style="clear:both;"><div style="width:90%;height:16px;"><div style="margin-top:7px;border-top:1px solid #f0f0f0;width:70%;margin-right:5px;margin-bottom:7px;float:'.FloatDir().';"></div><div style="float:'.FloatDirOp().';margin-'.FloatDir().':2px;margin-'.FloatDirOp().':2px;color:#818181;font-size:11px;font-family:arial;font-weight:bold;" >\'+event_time.getHours()+\':\'+ourGetMinutes(event_time)+\'</div></div></div><div style="clear:both;"></div><input id="uid_\'+data["events"][row]["sid"]+\'" type="hidden" value="\'+data["events"][row]["user_id"]+\'" />\';
					document.getElementById(\'side_chat\').scrollTop = document.getElementById(\'side_chat\').scrollHeight;	
					}
				if (data["events"][row]["eid"] == 11)
					{
					
					document.getElementById(\'main_chat\').innerHTML += \'<div style="float:right;margin-right:5px;"><img src="//graph.facebook.com/\'+data["events"][row]["user_id"]+\'/picture" width="25" height="25" /></div><div style="float:right;margin-right:10px;font-family:arial;font-size:12px;font-weight:bold;color:#00457c;width:250px;"> \'+get_username(data["events"][row]["user_id"])+\'<span style="color:#000000;font-weight:bold;"> כותב/ת:</span><br /><span style="font-weight:normal;color:#333333;">\'+ data["events"][row]["data"]+\'</span></div><div style="clear:both;"></div><div style="float:'.FloatDir().';margin-'.FloatDir().':40px;font-family:arial;font-weight:normal;font-size:12px;color:#00457C;" id="like_counter_\'+data["events"][row]["sid"]+\'">0</div>\'+get_like_button(data["events"][row]["sid"],data["events"][row]["user_id"])+\'<br /><div style="width:100%;height:16px;"><div style="margin-top:7px;border-top:1px solid #f0f0f0;width:85%;margin-right:5px;margin-bottom:7px;float:'.FloatDir().';"></div><div style="float:'.FloatDir().';margin-'.FloatDir().':5px;color:#818181;font-size:11px;font-family:arial;font-weight:bold;" >\'+event_time.getHours()+\':\'+ourGetMinutes(event_time)+\'</div></div><div style="clear:both;"></div><input id="uid_\'+data["events"][row]["sid"]+\'" type="hidden" value="\'+data["events"][row]["user_id"]+\'" />\';
					document.getElementById(\'main_chat\').scrollTop =document.getElementById(\'main_chat\').scrollHeight;
					}
				
				if (data["events"][row]["eid"] == 12)
					{
					if (current_speaker != data["events"][row]["data"])	{
						document.getElementById(\'spot_mic_\'+data["events"][row]["data"]).innerHTML = \'<img src="perm_ask.png" />\'; }
					}
				
				if (data["events"][row]["eid"] == 13)
					{
					if (current_speaker != data["events"][row]["data"])	{
						document.getElementById(\'spot_mic_\'+data["events"][row]["data"]).innerHTML = \'<img src="perm_comment.png" />\'; }
					}
				
				if (data["events"][row]["eid"] == 14)
					{
					document.getElementById(\'main_chat\').innerHTML += \'<div style="background-color:#f0f0f0;"><div style="float:right;margin-right:5px;"><img src="//graph.facebook.com/\'+data["events"][row]["user_id"]+\'/picture" width="25" height="25" /></div><div style="float:right;margin-right:10px;font-family:arial;font-size:12px;font-weight:bold;color:red;width:250px;"> \'+get_username(data["events"][row]["user_id"])+\'  <span style="color:#000000;font-weight:bold;">מתפרצ/ת</span><br /><span style="font-weight:normal;color:#333333;">\'+ data["events"][row]["data"]+\'</span></div><div style="clear:both;"></div><div style="float:'.FloatDir().';margin-'.FloatDir().':40px;font-family:arial;font-weight:normal;font-size:12px;color:#00457C;" id="like_counter_\'+data["events"][row]["sid"]+\'">0</div>\'+get_like_button(data["events"][row]["sid"],data["events"][row]["user_id"])+\'<br /><div style="width:100%;height:16px;"><div style="margin-top:7px;border-top:1px solid white;width:85%;margin-right:5px;margin-bottom:7px;float:'.FloatDir().';"></div><div style="float:'.FloatDir().';margin-'.FloatDir().':5px;color:#818181;font-size:11px;font-family:arial;font-weight:bold;" >\'+event_time.getHours()+\':\'+ourGetMinutes(event_time)+\'</div></div></div><div style="clear:both;"></div><input id="uid_\'+data["events"][row]["sid"]+\'" type="hidden" value="\'+data["events"][row]["user_id"]+\'" />\';
					document.getElementById(\'main_chat\').scrollTop =document.getElementById(\'main_chat\').scrollHeight;
					}
				
				if (data["events"][row]["eid"] == 15)
					{
					//alert(data["events"][row]["data"]);
					var temp_like_data = evalJSON(data["events"][row]["data"]);

					if (document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML == "") {
						document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML = 0; }
					document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML = parseInt(document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML) + 1;
					if (temp_like_data["uid"] == auth_user_id)
						{
						AddScore(1);
						}
					}
				
				if (data["events"][row]["eid"] == 16)
					{
					//alert(data["events"][row]["data"]);
					var temp_like_data = evalJSON(data["events"][row]["data"]);
					if (temp_like_data["uid"] == auth_user_id)
						{
						DecScore(1);
						}
					if (! (document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML == "")) 
						{
						//if (parseInt(document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML) == 1)	{
						//	document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML = "" }
						//else	{
						if (parseInt(document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML) > 0)	{
							document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML = parseInt(document.getElementById(\'like_counter_\'+temp_like_data["sid"]).innerHTML) - 1;  }
						}
				
					}
					
				if (data["events"][row]["eid"] == 60)
					{
					document.getElementById(\'leading_question\').innerHTML = data["events"][row]["data"];
					document.getElementById(\'main_chat\').innerHTML += \'<div style="float:right;margin-right:5px;"><img src="//graph.facebook.com/\'+data["events"][row]["user_id"]+\'/picture" width="25" height="25" /></div><div style="float:right;margin-right:10px;font-family:arial;font-size:12px;font-weight:bold;color:#00457c;width:250px;"> \'+get_username(data["events"][row]["user_id"])+\' <span style="color:#000000;font-weight:bold;">שינה את השאלה המנחה: </span><br /><span style="font-weight:normal;color:#333333;">\'+ data["events"][row]["data"]+\'</span></div><div style="clear:both;"></div><div style="margin-top:15px;border-top:1px solid #f0f0f0;width:95%;margin-right:5px;margin-bottom:5px;"></div><div style="clear:both;"></div>\';
					document.getElementById(\'main_chat\').scrollTop = document.getElementById(\'main_chat\').scrollHeight;
					}
				if (data["events"][row]["eid"] == 61)
					{
					if (!hosting_state)	{
						do_change_speaker(data["events"][row]["data"]); }
					}
					
				if (data["events"][row]["eid"] == 62)
					{	
					if (hosting_state) {
						shut_ctrl_btns(data["events"][row]["data"]); }	
					shut_parti_buttons(data["events"][row]["data"]);
					free_seat(data["events"][row]["data"]); 
					if (current_seat == data["events"][row]["data"] ) {
					change_client_state("CROWD"); }
					inc_crowd_counter();
					}
				}	
			}
		new_events = false;
		}
	if (do_polling)
		{
		clearTimeout(nextPoll);
		nextPoll = setTimeout("poll_events()",poller_interval);
		var timer = new Date();
		last_poll_time = timer.getTime();
		
		connect_retries = 0;
		}
			
	}
	
function ourGetMinutes(gTime)
	{
	if (gTime.getMinutes() > 9)
		return(gTime.getMinutes());
	else
		return("0" + gTime.getMinutes() );
	}

function add_invite(uid)
	{
	//alert(uid);	
	}
	
function user_chat_name_pic(uid)
	{
	return(\'<div style="float:right;margin-right:2px;"><img src="//graph.facebook.com/\'+uid+\'/picture" width="25" /></div><div style="float:right;margin-right:3px;font-family:arial;font-size:12px;font-weight:bold;color:#00457c;">\' +get_username(uid))+\'</div><div style="clear:both;"></div>\';	
	}
	
function start_poller(tid)
	{
	//alert("poller started");
	do_polling = true;
	nextPoll = setTimeout("poll_events(tid)",poller_interval);
	}
	
function stop_poller()
	{
	clearTimeout(nextPoll);
	do_polling = false;
	}

function super_poller()
	{
	var now = new Date();
	// alert("time since last poll: "+ (now.getTime() - last_poll_time));
	if ( (now.getTime() - last_poll_time) > (50000) ) 
		{ 
		if (connect_retries < 3)
			{
			stop_poller();
			start_poller();
			add_debug("restarting poller , connection retries is "+ connect_retries);
			connect_retries ++;
			}
		else
			{
			popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;font-family;arial;"><div>'.GetTrans(82).'</div><br />\'+popup_button_line(\''.GetTrans(83).'\',\'location.reload(true);close_popup();\')+\'</div>\',300,100); 
			}
		}
	clearTimeout(nextSuperPoll);
	nextSuperPoll = setTimeout("super_poller()",(poller_interval * 2));
	}
	
function start_super_poll()
	{
	nextSuperPoll = setTimeout("super_poller()",(poller_interval * 2));
	}
	
	
function connect_table(tid)
	{
	//document.getElementById(\'control_text\').innerHTML = "Connecting to table";
	//alert("connect started");
	send_action(tid,1);	
	if (user_status == "NEW")
		{
		popup(\'<div style="float:'.FloatDir().';width:150px;height:150px;"><img src="achivment.jpg" /></div><div style="float:'.FloatDir().';width:180px;height:150px;font-family:arial;font-size:14px;font-weight;bold;color:#3b5998;padding-top:10px;">'.GetTrans(74).'</div><div style="clear:both;"></div><div style="font-family:arial;font-size:14px;font-weight;bold;color:#3b5998;">'.GetTrans(75).'</div><div style="text-align:'.FloatDir().';cursor:pointer;color:#3b5998;background-color:white;padding-'.FloatDir().':5px;font-family:arial;font-weight:bold;" onmouseover="this.style.color=\\\'white\\\';this.style.backgroundColor=\\\'#315999\\\';"  onmouseout="this.style.color=\\\'#3b5998\\\';this.style.backgroundColor=\\\'white\\\';" onclick="skip_badge(1);" >'.GetTrans(45).'</div><div style="text-align:'.FloatDir().';cursor:pointer;color:#3b5998;background-color:white;padding-'.FloatDir().':5px;font-family:arial;font-weight:bold;" onmouseover="this.style.color=\\\'white\\\';this.style.backgroundColor=\\\'#315999\\\';"  onmouseout="this.style.color=\\\'#3b5998\\\';this.style.backgroundColor=\\\'white\\\';"   onclick="share_badge(1);" >'.GetTrans(44).'</div>\',350,225,125,100);
		}
	}

function skip_badge(bid)
	{
	send_action(tid,71,"starter");
	close_popup();
	}
	
function share_badge(bid)
	{
	close_popup();
	//alert(\'http://'.$GLOBALS['fb_path'].'\');
	FB.ui({method: \'feed\', display:\'popup\',link: \'http:'.$GLOBALS['fb_path'].'/\', picture: \'http:'.$GLOBALS['site_path'].'/achivment.jpg\', name: \'גם אני משפיע חברתי\', caption: \'הצטרפתי לשיחה בשולחן העגול והתחלתי להשפיע חברתית\', description: \'\'});
	send_action(tid,71,"starter");
	}
function send_text()
	{
	//alert(client_state);
	if (client_state == "CROWD" && document.getElementById(\'side_input\').value != \''.GetTrans(51).'\') {
		send_action(tid,10,encodeURIComponent(document.getElementById(\'side_input\').value)); }
	if (client_state == "PARTI" && document.getElementById(\'main_input\').value != \''.GetTrans(51).'\') 
		{
		if (!hosting_state && current_speaker != current_seat) {
			burst_screen(); }
		else {
			
		send_action(tid,11,encodeURIComponent(document.getElementById(\'main_input\').value));  }
		}
	
	}
	
function occupy_seat(spot,uid)
	{
	if (spot == 0) {
		var host_text = "מנחה הדיון: "; }
	else {
		var host_text = ""; }
	
	document.getElementById(\'spot_name_\'+spot).innerHTML = \'<a href="//www.facebook.com/\'+uid+\'" style="font-family:arial;font-size:12px;color:#3b5998;font-weight:bold" target="_blank" border="0">\'+host_text + get_username(uid)+\'</a>\';
	ChangeElement(\'spot_\'+spot,\'<div style="border:4px solid grey;border-radius: 30px;-webkit-border-radius:30px;width:50px;margin:auto;text-align:center;float:none;" id="spot_border_\'+spot+\'" ><a href="//www.facebook.com/\'+uid+\'" target="_blank" border="0"><img   style="border-radius: 30px;" id="user_img_\'+spot+\'" src="//graph.facebook.com/\'+uid+\'/picture"  width="50" border="0" /></a></div>\');
	
	if (uid == auth_user_id)	
		{
		hide_side_chat();
		pop_main_chat();
		if (spot == 0)	{
			ChangeElement(\'ctrl_btns_\'+spot,\'<a onclick="javascript:getup();" style="cursor:pointer;font-family:arial;font-size:11px;color:#3b5998;" onmouseover="javascript:this.style.color=\\\'red\\\';" onmouseout="javascript:this.style.color=\\\'#3b5998\\\';" ><img align="top" src="getup.jpg" border="0" /> '.GetTrans(56).'</a>\'); } 
		else {
			high_parti_btns(spot); }
		}
	else  {
		var parti_btns_text = ""  }

	seats_state[spot] = "TAKEN";
	
	}
function high_parti_btns(ssspot)
	{
	ChangeElement(\'ctrl_btns_\'+ssspot,\'<a onmouseover="javascript:this.style.color=\\\'red\\\';" onmouseout="javascript:this.style.color=\\\'#3b5998\\\';" onclick="javascript:perm_ask();" style="cursor:pointer;font-family:arial;font-size:11px;color:#3b5998;"><img align="top" src="ask_q.jpg" border="0" /> '.GetTrans(58).'</a><br /><a onclick="javascript:perm_comment();" onmouseover="javascript:this.style.color=\\\'red\\\';" onmouseout="javascript:this.style.color=\\\'#3b5998\\\';" style="cursor:pointer;font-family:arial;font-size:11px;color:#3b5998;width:100px;"><img align="top" src="ask_c.jpg" border="0" /> '.GetTrans(59).'</a><br /><a onmouseover="javascript:this.style.color=\\\'red\\\';" onmouseout="javascript:this.style.color=\\\'#3b5998\\\';" onclick="javascript:getup();" style="cursor:pointer;font-family:arial;font-size:11px;color:#3b5998;"><img align="top" src="getup.jpg" border="0" /> '.GetTrans(56).'</a>\');  
	}
function free_seat(spot)
	{
	seats_state[spot] = "OPEN";
	ChangeElement(\'spot_name_\'+spot,"");
	if (spot == current_seat)
		{
		hide_main_chat();
		pop_side_chat();
		}
		
	if (spot == 0)	
		{
		document.getElementById(\'spot_\'+spot).innerHTML = "<a style=\"cursor:pointer;\" onclick=\"javascript:sit_here("+spot+");\"><img src=\"host_sit_heb.jpg\" /></a>";
		}
	else
		{
		if (table_type == "ffa" || (invites[auth_user_id])) {						
			document.getElementById(\'spot_\'+spot).innerHTML = "<a style=\"cursor:pointer;\" onclick=\"javascript:sit_here("+spot+");\"><img src=\"sit_here_heb.jpg\" /></a>";  }
		else {
			document.getElementById(\'spot_\'+spot).innerHTML = "<img src=\"seat_res.png\" />";	}
		ChangeElement(\'spot_mic_\'+spot,"");
		if (current_speaker == spot) {
			current_speaker = 0; }
		}

	
	}

function sit_here(spot)
	{
	var go  = true;
	if (spot == 0 )
		{
		if (session_perms != "superadmin" && session_perms != "admin")
			{
			go = false; 
			popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>אין לך את ההרשאה להיות מנחה</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75); 
			} 
		}
	if (go)
		{
		if (client_state == "PARTI")	{
			popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>'.GetTrans(68).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75); }
		else
			{
			document.getElementById(\'spot_\'+spot).innerHTML = \'<img src="fbloader.gif" />\';
			send_action(tid,2,spot);
			}
		}
	}
	
function getup()
	{
	ChangeElement(\'ctrl_btns_\'+current_seat,\'<img src="fbloader.gif" />\');
	send_action(tid,3,current_seat);
	}
	
function close_table()
	{
	send_action(tid,50);
	}
	
function change_speaker(ssspot)
	{
	ChangeElement(\'ctrl_btns_\'+ssspot,\'<img src="fbloader.gif" />\');
	temp_cs_spot = ssspot;
	send_action(tid,61,ssspot);	
	}
	
function kick_user(ssspot)
	{
	document.getElementById(\'ctrl_btns_\'+ssspot).innerHTML = \'<img src="fbloader.gif" />\';
	if (hosting_state)	{
		send_action(tid,62,ssspot);	 }
	}
	
function close_down()
	{
	//alert(client_state);
	sync_state = false;
	send_action(tid,5,current_seat);  
	}
	
function change_lq()
	{
	

		
	if (current_seat == 0 && enable_lq_change )
		{
		enable_lq_change = false;
		document.getElementById("leading_question").innerHTML = \'<input id="change_lq" type="text" size="50" maxlength="50" /><a onclick="do_change_lq();" style="cursor:pointer;" id="change_lq_btn">שנה</a>\';
		document.getElementById("change_lq").disabled = false;
		document.getElementById(\'change_lq\').onkeydown  = function(e) 
			{
			e = e || window.event;
			if (e.keyCode == 13) {
				do_change_lq(); }
			}
		}
	}
	
function do_change_lq()
	{
	if (document.getElementById("change_lq").value != "")
		{
		ChangeElement(\'change_lq_btn\',\'<img src="fbloader.gif" />\');
		document.getElementById("change_lq").disabled = true;
		send_action(tid,60,document.getElementById("change_lq").value);
		}
	}
	
function high_ctrl_btns_all()
	{
	//alert("highlighting ctrl buttons...");
	for (ssspot in seats_state)
		{
		if (seats_state[ssspot] == "TAKEN" && ssspot != 0)
			{
			//alert(ssspot);
			high_ctrl_btns(ssspot); 
			}
		}
	}

function shut_ctrl_btns_all()
	{
	//alert("shutting ctrl buttons...");
	for (ssspot in seats_state)
		{
		if (seats_state[ssspot] == "TAKEN" && ssspot != 0)
			{
			shut_ctrl_btns(ssspot);
			}
		}
	}

function do_change_speaker(ssspot,init)
	{
	//alert("changing speaker to "+ssspot+" current speaker is "+current_speaker);
	if (current_seat == 0)
		{
		high_ctrl_btns(ssspot); 
		ChangeElement(\'cs_btn_\'+current_speaker,live_cs_button(current_speaker)) ;
		ChangeElement(\'cs_btn_\'+ssspot,shut_cs_button(ssspot));	
		}
	ChangeElement(\'spot_mic_\'+ssspot,\'<img src="now_mic.jpg" />\');
	if (document.getElementById(\'spot_border_\'+ssspot))	{
		document.getElementById(\'spot_border_\'+ssspot).style.border = \'4px solid green\'; }
	if (document.getElementById(\'spot_border_\'+current_speaker))	{
		document.getElementById(\'spot_border_\'+current_speaker).style.border = \'4px solid grey\'; }
	ChangeElement(\'spot_mic_\'+current_speaker,"");
	if (init === undefined)	{
		document.getElementById(\'main_chat\').innerHTML += \'<div style="float:right;margin-right:5px;"><div style="float:right;font-family:arial;font-size:13px;font-weight:bold;color:#00457c;width:250px;"><img align="top" src="mic.jpg" /><span style="color:#000000;font-weight:bold;font-size:11px;">המנחה העביר/ה את זכות הדיבור ל</span> \'+document.getElementById(\'spot_name_\'+ssspot).innerHTML+\'</div></div><div style="clear:both;"></div><div style="margin-top:15px;border-top:1px solid #f0f0f0;width:95%;margin-right:5px;margin-bottom:5px;"></div><div style="clear:both;"></div>\'; }
	document.getElementById(\'main_chat\').scrollTop = document.getElementById(\'main_chat\').scrollHeight;
	current_speaker = ssspot;	
	
	}

function high_ctrl_btns(ssspot)
	{
	var cs_btn = "";
	if (current_speaker == ssspot) {
		cs_btn =  shut_cs_button(ssspot); }
	else {
		cs_btn = live_cs_button(ssspot); }
	//alert(ssspot);
	ChangeElement("ctrl_btns_"+ssspot,\'<div id="cs_btn_\'+ssspot+\'" style="float:'.FloatDir().';margin-'.FloatDir().':15px;"> \'+cs_btn+\' </div><br /><div style="float:'.FloatDir().';margin-'.FloatDir().':15px;" id="kick_btn_\'+ssspot+\'"> \'+kick_button(ssspot)+\' </div>\');
	}
		
function shut_ctrl_btns(ssspot)
	{
	ChangeElement("ctrl_btns_"+ssspot,"");
	}
	

function live_cs_button(ssspot)
	{
	return(\'<a onmouseover="javascript:this.style.color=\\\'red\\\';" onmouseout="javascript:this.style.color=\\\'#3b5998\\\';" onclick="change_speaker(\\\'\'+ssspot+\'\\\');" style="cursor:pointer;font-family:arial;font-size:11px;color:#3b5998;"><img align="top" src="mic.jpg" border="0" /> זכות דיבור</a>\');
	}
	
function shut_cs_button(ssspot)
	{
	return(\'\');
	}
	
function free_speaker()
	{
	current_speaker = 0;
	}
function kick_button(ssspot)
	{
	return(\'<a onmouseover="javascript:this.style.color=\\\'red\\\';" onmouseout="javascript:this.style.color=\\\'#3b5998\\\';" onclick="kick_user(\\\'\'+ssspot+\'\\\');" style="cursor:pointer;font-family:arial;font-size:11px;color:#3b5998;"><img align="top" src="block.jpg" border="0" /> חסום</a>\');
	}
	
function ChangeElement(id,html)
	{
	if (document.getElementById(id)) {
		document.getElementById(id).innerHTML = html; }
	}
	
function popup(content,width,height,left,top)
	{
	if (width === undefined) {
		width = 300; }
	if (height === undefined) {
		height = 300; }
	if (left === undefined) {
		left = 200; }
	if (top === undefined) {
		top = 350; }
	ChangeElement(\'popup_cont\',\'<div id="popup" style="background-color:#F5F5F5;position:fixed;left:\'+left+\'px;top:\'+top+\'px;width:\'+width+\'px;height:\'+height+\'px;border:1px solid #777777;z-index:1;text-align:center;"><div style="float:'.FloatDirOp().';cursor:pointer;margin-'.FloatDirOp().':5px;margin-top:5px;" onclick="close_popup();" ><img src="x_close.jpg" border="0" /></div>\'+content+\'</div>\');	
	}
	
function popup_button_line(text,action,width)
	{
	if (width === undefined)	{
		width = 150; }
	return(\'<div style="font-weight:bold;font-family:arial;align:center;margin:auto;text-align:center;cursor:pointer;color:white;background-color:#3B5999;margin-bottom:3px;width:\'+width+\'px;" onmouseover="this.style.color=\\\'red\\\';"  onmouseout="this.style.color=\\\'white\\\';"   onclick="\'+action+\';" >\'+text+\'</div>\');
	}
	
function burst_screen()
	{
	content = \'<div style="padding-top:20px;font-family:arial;text-align:center;"><div style="text-align:center;"><span style="color:red;font-weight:bold;font-family:arial;">'.GetTrans(62).'</span><br />'.GetTrans(67).'<br /><br /><br />\'+popup_button_line(\''.GetTrans(63).'\',\'close_popup();\')+popup_button_line(\''.GetTrans(64).'\',\'do_burst_talk();\')+\'</div></div>\'; 
	popup(content,200,150,200,350);
	}
	
function close_popup()
	{
	ChangeElement(\'popup_cont\',\'\');	
	}
	
function do_burst_talk()
	{
	close_popup();
	send_action(tid,14,encodeURIComponent(document.getElementById(\'main_input\').value)); 
	}

function inviteFriends()
		{
		FB.ui({method: \'apprequests\', exclude_ids: \''.$inv_excludes.'\',display:\'popup\',message: \'בואו להשתתף איתי בשולחן ולהיות משפיעים חברתית\'});
		}

function invite_single(uid)
	{
	if (tid != "")	{
		FB.ui({method: \'apprequests\',to: uid, display:\'popup\',message: \'בואו להשתתף איתי בשולחן ולהיות משפיעים חברתית\',data: tid});}
	else	{
		FB.ui({method: \'apprequests\',to: uid, display:\'popup\',message: \'בואו להשתתף איתי בשולחן ולהיות משפיעים חברתית\'});   }
	}

function FocusClearType(id)
	{
	if (! (input_started[id]))
		{
		document.getElementById(id).focus();	
		document.getElementById(id).value = \'\';	
		document.getElementById(id).style.color = \'#333333\';
		input_started[id] = true;
		}
	}
	
function resetInput(id,string,force)
	{
	if ( (document.getElementById(id).value == "") || force)
		{
		document.getElementById(id).disabled = false;
		document.getElementById(id).value = string;	
		document.getElementById(id).style.color = \'#777777\';	
		input_started[id] = false;
		}
	}
	
function do_user_ban(uid)
	{
	ChangeElement("side_popped_btns",\'<img src="fbloader.gif" />\');
	send_action(tid,63,uid);
	}
	
function side_options_popup(uid,chat_line)
	{
	if (current_seat == 0 && uid != auth_user_id)
		{
		content = \'<div style="padding-top:20px;font-family:arial;"  id="side_popped_btns"><div style="text-align:center;"><span style="color:red;font-weight:bold;font-family:arial;">'.GetTrans(76).'</span><br /></div>\'+popup_button_line(\''.GetTrans(77).'\',\'side_invite(\\\'\'+uid+\'\\\',\\\'\'+chat_line+\'\\\');\')+popup_button_line(\''.GetTrans(78).'\',\'do_user_ban(\\\'\'+uid+\'\\\');\')+popup_button_line(\''.GetTrans(79).'\',\'close_popup();\')+\'</div>\'; popup(content,200,125,200,350);
		}
	}
	
	
function high_side_btns(id)
	{
	if (current_seat == 0)	{
		ChangeElement(id,\'אפשרויות >>\'); 
		}
	}
	
function hide_side_btns(id)
	{
	if (current_seat == 0)	{
		ChangeElement(id,\'.\'); }
	}
	
function side_invite(uid,id)
	{
	if (current_seat == 0)	
		{
		if (invites[uid])	{
			popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>'.GetTrans(69).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75); }
		else	
			{
			ChangeElement(\'side_popped_btns\',\'<img src="fbloader.gif" />\');
			send_action(tid,6,uid); 
		
			}
		}
	}
	
function you_are_banned()
	{
	popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;"><div>'.GetTrans(80).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75,200,300);	
	}
	
function coming_soon()
	{
	popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;font-family:arial"><div>'.GetTrans(72).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75,200,300);	
	}
	
function pop_main_chat()
	{
	document.getElementById(\'main_chat\').style.height = \'295px\';
	document.getElementById(\'main_chat_div\').innerHTML = main_chat_popup;
	document.getElementById(\'main_input\').onkeydown  = function(e) 
		{
		//alert(e.keyCode);
		e = e || window.event;
		if (e.keyCode == 13)
			{
			send_text();
			}
		}
	document.getElementById(\'main_chat\').scrollTop = document.getElementById(\'main_chat\').scrollHeight;	
	}
	
function hide_main_chat()
	{
	document.getElementById(\'main_chat\').style.height = \'350px\';
	document.getElementById(\'main_chat_div\').innerHTML = \'\';
	}
	
function hide_side_chat()
	{
	document.getElementById(\'side_chat\').style.height = \'500px\';
	document.getElementById(\'side_chat_div\').innerHTML = \'\';
	}
	
function pop_side_chat()
	{
	document.getElementById(\'side_chat\').style.height = \'380px\';
	document.getElementById(\'side_chat_div\').innerHTML = side_chat_popup;
	document.getElementById(\'side_input\').onkeydown  = function(e) 
		{
		//alert(e.keyCode);
		e = e || window.event;
		if (e.keyCode == 13)
			{
			send_text();
			}
		}
	document.getElementById(\'side_chat\').scrollTop = document.getElementById(\'side_chat\').scrollHeight;	
	}
	
function get_like_button(sid,uid)
	{
	if (uid == auth_user_id)	{
		return(\'<div style="float:'.FloatDir().';"><img src="like.jpg" border="0" /></div>\'); }
	else	{
		return(\'<div style="float:'.FloatDir().';"><img src="like.jpg" border="0" /></div><div style="margin-'.FloatDir().':2px;float:'.FloatDir().';font-weight:normal;cursor:pointer;font-size:12px;color:#00457C;font-family:arial;" onclick="submit_like(\\\'\'+sid+\'\\\');" onmouseover="this.style.color=\\\'red\\\';" onmouseout="this.style.color=\\\'#00457c\\\';" id="like_\'+sid+\'">'.GetTrans(88).'</div>\'); }
	}
	
function submit_like(sid)
	{
	var like_data = encodeURI(\'{"sid": \'+sid+\',"uid": \'+document.getElementById(\'uid_\'+sid).value+\'}\');
	if (like_state[sid])	{
		send_action(tid,16,like_data); }
	else	{
		send_action(tid,15,like_data); }
	swap_like_state(sid);
	}
	

function swap_like_state(sid)
	{
	if (document.getElementById(\'like_\'+sid).innerHTML == "'.GetTrans(89).'")
		{
		ChangeElement(\'like_\'+sid,"'.GetTrans(88).'");
		like_state[sid] = false;
		}
	else
		{
		ChangeElement(\'like_\'+sid,"'.GetTrans(89).'");
		like_state[sid] = true;
		}
	
	}
function levelup_popup(lvl)
	{
	popup(\'<div style="float:'.FloatDir().';width:120px;height:100px;margin-top:25px;"><img src="level_big_\'+lvl+\'.png" border="0" /></div><div style="float:'.FloatDir().';width:175px;height:100px;margin-top:35px;">כל הכבוד! צברת מספיק נקודות בשביל לקבל אות השפעה! באפשרותך לשתף זאת בפרופיל</div><div style="clear:both;"></div><div style="margin-top:5px;">\'+popup_button_line(\'שתף\',\'levelup_share(\\\'\'+lvl+\'\\\');\')+\'</div><div style="margin-top:5px;">\'+popup_button_line(\'דלג\',\'close_popup();\')+\'</div>\',350,200,150,250);
	}

function levelup_share(lvl)
	{
	FB.ui({method: \'feed\', link: \'http:'.$GLOBALS['fb_path'].'\',display:\'popup\', picture: \'http:'.$GLOBALS['site_path'].'/level_big_1.png\', name: \'עליתי בדרגת השפעה בשולחן עגול\', caption: \'צברתי מספיק נקודות השפעה ועליתי דרגה בשולחן עגול!\', description: \'\'});
	close_popup();
	}

function DecScore(dec)
	{
	points -= dec;
	document.getElementById(\'bar_points\').innerHTML = points;
	}
	
function AddScore(add)
	{
	points += add;
	//alert("cmpr " + points + " with " + level_xp[(current_level + 1)]);
	while (points >= level_xp[(current_level + 1)])
		{
		current_level ++;	
		document.getElementById(\'next_level_points\').innerHTML = level_xp[(current_level + 1)];
		levelup_popup(current_level);
		document.getElementById(\'level_img\').src = \'level_\'+current_level+\'.jpg\';
	//	document.getElementById(\'leveldesc\').innerHTML = \'Level \' + current_level + \'<br />\' + level_desc[current_level];
		}
	bar_progress = points - level_xp[current_level];
	bar_end = level_xp[(current_level + 1)] - level_xp[current_level];
	done_portion = Math.floor(bar_progress/bar_end*bar_width);
	
	document.getElementById(\'bar_points\').innerHTML = points;
	document.getElementById(\'doneportion\').style.width = done_portion + \'px\';
	}
	
function suggest_topic_popup()
	{
	popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;font-size:15px;font-family:arial;"><div>הציעו רעיון לדיון<br /><input type="text" id="suggest_topic" name="suggest_topic" size="35" /><br /></div><div id="suggest_topic_send">\'+popup_button_line(\''.GetTrans(54).'\',\'send_topic_suggest();\')+\'</div>\'+popup_button_line(\''.GetTrans(79).'\',\'close_popup();\')+\'</div>\',300,100,200,300);	
	}
	
function send_topic_suggest()
	{
	var xmlObject = initXmlObject();
	var url = \'suggest.php?topic=\'+encodeURI(document.getElementById(\'suggest_topic\').value);
	document.getElementById(\'suggest_topic_send\').innerHTML = \'<img src="fbloader.gif" />\';
	xmlObject.onreadystatechange=function()
		{
		if(xmlObject.readyState == 4)
			{
			if (xmlObject.responseText.search("OK") != -1)	{
				popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;font-family:arial;"><div>'.GetTrans(85).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75,200,300);	 }
			else	{
				popup(\'<div style="padding-top:10px;text-align:center;font-weight:bold;font-family:arial;"><div>'.GetTrans(86).'</div>\'+popup_button_line(\''.GetTrans(84).'\',\'close_popup();\')+\'</div>\',300,75,200,300);	 }
			}
		}
	xmlObject.open("GET", url, true);
	xmlObject.send();	
	}
	
function inc_crowd_counter()
	{
	document.getElementById(\'crowd_counter\').innerHTML = parseInt(document.getElementById(\'crowd_counter\').innerHTML) + 1;
	}
	
function dec_crowd_counter()
	{
	if (parseInt(document.getElementById(\'crowd_counter\').innerHTML) > 0)	{
		document.getElementById(\'crowd_counter\').innerHTML = parseInt(document.getElementById(\'crowd_counter\').innerHTML) - 1;	}
	}

function calcage(secs, num1, num2) {
  s = ((Math.floor(secs/num1))%num2).toString();
  if (s.length < 2)
    s = "0" + s;
  return "<b>" + s + "</b>";
}

function CountBack(secs,dFormat,TargetId,FinishMessage) 
	{	
	if (secs < 0) {
	document.getElementById(TargetId).innerHTML = FinishMessage;
	return; }
	
	DisplayStr = dFormat.replace(/%%D%%/g, calcage(secs,86400,100000));
	DisplayStr = DisplayStr.replace(/%%H%%/g, calcage(secs,3600,24));
	DisplayStr = DisplayStr.replace(/%%M%%/g, calcage(secs,60,60));
	DisplayStr = DisplayStr.replace(/%%S%%/g, calcage(secs,1,60));
	 document.getElementById(TargetId+"_cntdwn").innerHTML = DisplayStr;
    setTimeout("CountBack(" + (secs+CountStepper) + ",\'"+(dFormat) + "\',\'"+(TargetId) + "\',\'"+(FinishMessage) + "\')", SetTimeOutPeriod);
	}

function putspan(TargetId) {
 document.getElementById(TargetId).innerHTML = ("<span id=\'"+TargetId+"_cntdwn\' style=\'color:#ffb55d;font-weight:bold;\' ></span>");
}

function startTimer(TargetDate,TargetId,FinishMessage)
	{
	dthen = TargetDate;
	DisplayFormat = "%%S%% : %%M%% : %%H%%";
	putspan(TargetId);
	dnow = dnow.valueOf()/1000;
	if(CountStepper>0)
	  ddiff = dnow-dthen;
	else
	  ddiff = dthen-dnow;
	
	gsecs = Math.floor(ddiff);
	CountBack(gsecs,DisplayFormat,TargetId,FinishMessage);
	}
	


</script>	
<div id="runjs"></div>

');


?>