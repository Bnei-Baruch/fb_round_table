<?php



function GetPeopleNotifLine($text)
	{
	$pregs[0] = '/\?id=([0-9]{1,50})/';
	$pregs[1] = '/D=([0-9]{1,50})/';
	preg_match_all($pregs[0],$text,$matches_id);
	preg_match_all($pregs[1],$text,$matches_d);
	$matches = array_unique(array_merge($matches_id[1],$matches_d[1]));
	return($matches);
	}
	
	
function CountPeople($notif,$type)
	{
	$lang = 'EN';
	$pregs[0] = '/.*, .*,.* and ([0-9]{1,6}) '.$notif_exprs[$type][$lang].'/';
	$pregs[1] = '/.*, .* and .*'.$notif_exprs[$type][$lang].'/';
	$pregs[2] = '/.* and .* '.$notif_exprs[$type][$lang].'/';
	$pregs[3] = '/.*'.$notif_exprs[$type][$lang].'/';
	
	foreach ($pregs as $key => $preg)
		{
		if (preg_match($preg,$notif['title_text'],$match))
			{
			if ($key == 0)
				return($match[1] + 2);
			if ($key == 1)
				return(3);
			if ($key == 2)
				return(2);
			if ($key == 3)
				return(1);
			}
		}
	
	return(true);	
	}
	
function NotifGetType($row)
	{
	global $notif_exprs;
	$lang = "EN";
	foreach ($notif_exprs as $key => $exprs)
		{
		if ($exprs[$lang] != '' && preg_match('/'.$exprs[$lang].'/',$row['title_text']))
			return($key);
		}
	return(0);
	}




function GetTimeStr($time = 0)
	{
	if ($time > 0)
		$passed = time() - $time;
	else
		return(' now');
	if ($passed > 0)
		{
		if ($passed >  60*60*48)
			return( 'on '.date('m/d/y',$time));
		if ($passed >  60*60*24)
			return('Yesterday');
		if ($passed >  60*60*2)
			{
			$return = floor( $passed/(60*60) ).' hours ago';
			return($return);
			}
		if ($passed >  60*60)
			return (' about an hour ago ');
		if ($passed > 60*2)
			return (' about '.floor($passed/60) .' minutes ago');
		if ($passed > 60)
			return (' about a minute ago');
		if ($passed < 60)
			return (' about '.$passed.' seconds ago');
		}
	else
		return(false);
	}

function GraphAPIPost($query,$attach)
	{
	global $facebook;
	$page = false;
	$max_retries = 3;
	$retries = 0;
	while ( (!$page) && $retries < $max_retries)
	{
	try 
	{ $page = $facebook->api($query,'POST',$attach); }	
	catch (FacebookApiException $e)
	{
	
	 $error = $e-> getResult();	
	 //print_r($error);
	// echo($error['error_msg']);
	// CreateAlert('facebook api failed '.$error["error_msg"]);
	}
	$retries ++;
	}
	return($page);	
	}
	
	
function GraphAPI($query)
	{
	global $facebook;
	$page = false;
	$max_retries = 3;
	$retries = 0;
	while ( (!$page) && $retries < $max_retries)
	{
	try 
	{ $page = $facebook->api($query); }	
	catch (FacebookApiException $e)
	{
	
	 $error = $e-> getResult();	
	 //print_r($error);
	// echo($error['error_msg']);
	// CreateAlert('facebook api failed '.$error["error_msg"]);
	}
	$retries ++;
	}
	return($page);	
	}
	
function GraphFQL($query)
	{
	global $facebook;
	$page = false;
	$max_retries = 3;
	$retries = 0;
	while ( (!$page) && $retries < $max_retries)
	{
	try 
	{ $page = $facebook->api($query); }	
	catch (FacebookApiException $e)
	{
	
	 $error = $e-> getResult();	
	 //print_r($error);
	// echo($error['error_msg']);
	// CreateAlert('facebook api failed '.$error["error_msg"]);
	}
	$retries ++;
	}
	return($page);	
	}


function VerifySex($sex_array)
	{
	global $trans;
	
	foreach ($sex_array as $sex)
		{
	//	echo 'gonna compare '.$sex.' it '.GetTrans(3).' and '.GetTrans(4).'<br />';
		if (stristr($sex,GetTrans(3)) or stristr($sex,GetTrans(4)) or stristr($sex,'male') or stristr($sex,'female'))
			return(true);
		}
	return(false);
	}
	

function retryFQL($fql,$retries = 3,$stats = false,$trans = false)
	{
	global $debug,$facebook;
	
	$start = time();
	$retried = -1;
	//echo $fql.'<br />';
	while (!is_array($return) && $retried < $retries)
		{
		$retried ++;
		try	{
			$return = $facebook->api_client-> fql_query($fql); }		
		catch(Exception $e){
			$exception = $e->getMessage();}
		//echo 'retrying '.$fql.' '.$exception.' '.$return.'<br />';
		}
	$length =  time() - $start;
	if ($stats)
		{
		if (is_array($return))
			$success = 1;
		else
			$success = 0;
		
		$query = "insert into stats values ('".$trans."','".$length."','".time()."','".$_SESSION['user_id']."','".$retried."','".$success."')";
		mysql_query_alert($query);
		
		}
	if ($debug)
		echo $exception;

	return($return);
	}




function firstWord($word)
	{
	return(substr($word,0,strpos($word,' ')));	
	}

function ConvertQs($str)
	{
	return(str_replace('\'','&#039;',$str));
	}

function ConvertPYS($str)
	{
	return(str_replace('\'','¨',$str));
	}



function InsertBFFResults($uid,$results)
	{
	$query = "insert into results values ('".$uid."','".SetOrZero($results[0])."','".SetOrZero($results[1])."','".SetOrZero($results[2])."','".SetOrZero($results[3])."','".SetOrZero($results[4])."','".SetOrZero($results[5])."','".SetOrZero($results[6])."','".SetOrZero($results[7])."','".SetOrZero($results[8])."','".SetOrZero($results[9])."')";
	mysql_query($query);
	return(true);
	}

function SetOrZero($num)
	{
	if (isset($num) && $num != '')
		return($num);
	else
		return(0);
	}
function SplitSteps($main,$steps)
	{
	$num = mt_rand(0,9);
	$i = 0;
	$results = array();
	foreach($steps as $step)
		{
		$results[$i] = $step;
		$i ++;
		}
	while ($i < 10)
		{
		$results[$i] = $main;
		$i ++;
		}
	CompleteStep($results[$num]);
	return($results[$num]);
	}
	

function mysql_query_alert($query,$ignore_errors = array())
	{
	$return = mysql_query($query);
	if (!$return && !in_array(mysql_errno(),$ignore_errors) )
		{
		CreateAlert('query failed '.$query .' '.mysql_error(),7);
		$return = false;
		}
	return($return);
	}

function my_strip_tags($str) {
    $strs=explode('<',$str);
    $res=$strs[0];
    for($i=1;$i<count($strs);$i++)
    {
        if(!strpos($strs[$i],'>'))
            $res = $res.'&lt;'.$strs[$i];
        else
            $res = $res.'<'.$strs[$i];
    }
    return strip_tags($res);   
}

function DropHyphens($url)
	{
	$replace = '/-/';
	return(preg_replace($replace,' ',$url));
	}

function CreateMysqlAlert($query)
	{
	CreateAlert($query. ' ' . mysql_error(),7);
	}
function AddHypens($url)
	{
	$replace = '/ /';
	return(preg_replace($replace,'-',$url));
	}

function ChopPostId($post_id)
	{
	return(substr($post_id,(strpos($post_id,'_') + 1),( strlen($post_id) - strpos($post_id,'_'))));
	}

function session_check()
	{
	if (!(isset($_SESSION['user_id'])) or $_SESSION['user_id'] == '')
		{
		$return = FALSE;
		}
	else
		{
		$return = TRUE;
		}
	return ($return);
	}
	
function LoginFlagJS()
	{
	if (session_check())
		$return =  '<script type="text/javascript">Login_Flag = \'true\';</script>';
	else
		$return =  '<script type="text/javascript">Login_Flag = \'false\';</script>';
	return($return);
	}
function IE6Flag()
	{
	if (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 6'))
		$return =  '<script type="text/javascript">IE6_Flag = \'true\';</script>';
	else
		$return =  '<script type="text/javascript">IE6_Flag = \'false\';</script>';
	return($return);
	}
function Logout()
{
		setcookie('username','',time() - 60*60*24*365*50) or CreateAlert('Failed setting cookie for '.$_SESSION['uid'],7);
		setcookie('password','',time() - 60*60*24*365*50) or CreateAlert('Failed setting cookie for '.$_SESSION['uid'],7);
		$_COOKIE['username'] = '';
		$_COOKIE['password'] = '';
		unset($_SESSION['username']);
		unset($_SESSION['uid']);
		session_unset();
		session_destroy();
		unset($_POST['username']);
		unset($_POST['password']); 
		//print_r($_POST);
}

function Authenticate()
{
global $facebook;
$user_id = $facebook->require_login();

RegisterUser($facebook,$user_id);

$_SESSION['user_id'] = $user_id;
return (true);
}

function GetUrl()
	{
	return (htmlentities($_SERVER['PHP_SELF']) . '?' . htmlentities($_SERVER['QUERY_STRING']));
	}
function detect_ie()
	{
    if (isset($_SERVER['HTTP_USER_AGENT']) &&  (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
        return true;
    else
        return false;
	}

function ZeroPad($string,$digits)
	{
	$adds = $digits - mb_strlen($string);
	while ($adds > 0)
		{
		$string = 0 . $string;
		$adds --;
		}
	return($string);
	}

function thumbnail($i,$nw,$p,$nn,$ext)
	 { 
    if (stristr($ext,"jpg") or stristr($ext,"jpeg"))
		$img=imagecreatefromjpeg("$i");
	if (stristr($ext,"png"))
		$img=imagecreatefrompng("$i");
	if (stristr($ext,"gif"))
		$img=imagecreatefromgif("$i");
	$ow=imagesx($img); 
    $oh=imagesy($img);
	 $scale=$nw/$ow;
    $nh=ceil($oh*$scale);
    $newimg=imagecreatetruecolor($nw,$nh); 
    imagecopyresized($newimg,$img,0,0,0,0,$nw,$nh,$ow,$oh);
    if (stristr($ext,"jpg") or stristr($ext,"jpeg"))
		imagejpeg($newimg, $p.$nn); 
	 if (stristr($ext,"png"))
		imagepng($newimg, $p.$nn); 
	 if (stristr($ext,"gif"))
		imagegif($newimg, $p.$nn); 
    return true; 
	}
function newThumbnail($imgSrc,$thumbSize,$newImg,$ext)
 	{
	//echo ('got to here?!<br />');
	$return = true;
	 //getting the image dimensions 
 	//saving the image into memory (for manipulation with GD Library)  
	if (stristr($ext,"jpg") or stristr($ext,"jpeg"))
		{
		if (!imagecreatefromjpeg("$imgSrc"))
			{
			$return = false;
			}
		$myImage = imagecreatefromjpeg("$imgSrc") or $return = false;
		}
	if (stristr($ext,"png"))
		{
		if (!imagecreatefrompng("$imgSrc"))
			{
			$return = false;
			}
		$myImage = imagecreatefrompng("$imgSrc") or $return = false;
		}
	if (stristr($ext,"gif"))
		{
		if (!imagecreatefromgif("$imgSrc"))
			{
			$return = false;
			}
		$myImage = imagecreatefromgif("$imgSrc") ;
		}
	if ($return)
		{ 
		
 		list($width, $height) = getimagesize($imgSrc);   
		 //setting the crop size  
		 if($width < $height)  
			$SmallestSide = $width;   
		else  
			$SmallestSide = $height;     
		$cropPercent = 1;   
		$cropWidth   = $SmallestSide*$cropPercent;   
		$cropHeight  = $SmallestSide*$cropPercent;   
		$c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/2);  
		// Creating the thumbnail    
		 $thumb = imagecreatetruecolor($thumbSize, $thumbSize);   
		
		// echo('need to see if those are defined<br>');
		// echo( $thumbSize . ' | '. $thumbSize. ' | '.  $cropWidth. ' | '.  $cropHeight);
		 imagecopyresampled($thumb, $myImage, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);   
		  $white = imagecolorallocate($thumb, 999, 999, 999);
		 
		 imagejpeg($thumb,$newImg) or $return = false;
		}
	 return($return);
		}

function CatchExt($string)
{
$start = mb_strrpos($string,".");
$len = mb_strlen($string);
$ext = substr($string,$start+1,$len-$start);
return ($ext);
}
function ChompExt($string)
 {
	$len = mb_strrpos($string,".");
	$ext = substr($string,0,$len);
	return ($ext);
 }
 
function SimpleCutString($string,$maxlen)
	{
	if (mb_strlen($string) > $maxlen)
		$return = mb_substr($string,'0',$maxlen);
	else
		$return = $string;
	return($return);
	} 
function CutString($string,$maxlen,$maxbr,$more = '',$dotsflag = false)
	{
	$moreflag = false;
	if (mb_strlen(html_entity_decode($string)) > $maxlen)
		{
		if ($dotsflag)
			$string = mb_substr($string,'0',$maxlen-3) . "...";
		else
			$string = mb_substr($string,'0',$maxlen);
		$moreflag = true;
		}
	preg_match_all('/<br/i',$string,$brs,PREG_OFFSET_CAPTURE  );
	if (sizeof($brs[0]) > $maxbr)
		{
		$string = mb_substr($string,'0',$brs[0][$maxbr - 1][1]);
		$moreflag = true;
		}
	
	if ($more != '' && $moreflag)
		$string = $string  . $more;
	return($string);
	}

//Rebuilds a query string, by adding keys or changing existing ones.
//if $originalQuery is an array or a query string, it will be rebuilt. otherwise the result is based on $_GET
//if $keysToRemove is a value or an array, these keys will be dropped from the query
function RebuildQS($keysToAdd, $originalQuery = NULL, $keysToRemove = NULL)
{
	if(is_array($originalQuery))
		$getClone = $originalQuery;
	else if(is_string($originalQuery))
		parse_str($originalQuery, $getClone);
	else
		$getClone = $_GET;

	if(!is_null($keysToRemove))
	{
		if (is_array($keysToRemove))
		{				
			foreach($keysToRemove as $key)
				unset($getClone[$key]);
		}
		else
			unset($getClone[$keysToRemove]);
	}	

	if(is_array($keysToAdd))
		foreach($keysToAdd as $key => $value)
			$getClone[$key] = $value;
	
	return htmlspecialchars(http_build_query($getClone));
}


function timeStr($time)
	{
 
	return  (date('l dS \of F Y h:i:s A',$time));
	}
	

function getnow()
	{
	$now =  date('l dS \of F Y h:i:s A',time());
	return $now;
	}
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function ParseTag($input,$tag)
	{
	$preg = '@<'.$tag.'>(.*)</'.$tag.'>@i';
	preg_match($preg,$input,$output );
	return($output[1]);
	}
	
function Nothing()
	{
	return(true);
	}
function CreateAlert($text,$sev = 7,$man_id = false)
	{
	$text = addslashes(htmlentities($text));
	if ($man_id)
		$alertid = $man_id;
	else
		$alertid = GenerateCguid();
	$text .= ' generating page <a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'</a>';
	$query = "insert into alerts values ('$alertid','$text','$sev','".time()."','0','0','0','0')";
	return(mysql_query($query));
	}
function StripUserInput($input)
	{
	//echo ('strip started with '.$input.'<br />');
	$quotes = array('/’/','/‘/');
	$input = preg_replace($quotes,'\'',$input);
	$quotes = array('/”/','/“/');
$output  = preg_replace($quotes,'"',$input);
	//$output = htmlentities($input);
	// make sure to addslashes only if addslashes wasn't already performed
	if (! ( stristr($output,'\\\'') or stristr($output,'\\"') ) )
		$output = addslashes($output);
	//	echo ('strip will return '.$output.'<br />');
	return($output);
	}
function CareSlashes($input)
	{
	if (! ( stristr($input,'\\\'') or stristr($input,'\\"') ) )
		$output = addslashes($input);
	return($output);
	}
function ReplaceWithBR($input)
	{
	return(str_replace("\n","<br />",$input));
	}
function StripBR($input)
	{
	return(str_replace("<br />","",$input));
	}
	
function GenerateCguid()
	{
	$len = 25;
	$i = 0;
	$cguid = "";
	while ($i < $len)
		{
		$digit = chr(mt_rand(97,122));
		$cguid .= $digit;
		$i ++;
		}
	return($cguid);
	}
function GenerateUid()
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
	
function IsGuid($guid)
{
	return preg_match('/[a-z]{25}/', $guid) === 1;
}

function IsUid($uid)
{
	if(is_numeric($uid))
		return $uid < 1000000000 && $uid > 0;
	else
		return preg_match('/[0-9]{9}/', $uid) === 1;
}
	

function ClearAlert($id)
	{
	if(!IsGuid($id))
	{
		CreateAlert("Not a valid guid. $id", 6);
		return;
	}

	$query = "delete from alerts where alertID = '".$id."'";
	return(mysql_query($query));
	}


function AddError($num)
	{
	global $query,$error,$error_code;
	if ($num == 10)
		CreateAlert("Query error, query was ".$query." error was ".mysql_error()."<br />",7);
	$error = true;
	$error_code[$num] = true;
	return(true);
	}
function FormatError($msgs)
	{
//	if (isset($msgs[0]) && $msgs[0] != '')
//		{
		foreach ($msgs as $msg)
			{
			if (isset($msg) && $msg != '')
				$return .= '<span class="SubmitError">* '.$msg.'</span>';		
			}
//		}
	return ($return);
	}

function SearchString($tableName, $field, $value, $fields = array('*'), $additionalWhere = '', $orderBy = false)
{
	$query = 'SELECT '.join(',', $fields)." FROM $tableName WHERE $field LIKE '%$value%' $additionalWhere";
	if($orderBy)
		$query.= " ORDER BY $orderBy";
		
	$result = mysql_query($query) or CreateAlert("failed selecting for search ".$query." ".mysql_error(),7);

	$matches = array();
	while($data = mysql_fetch_array($result))
		$matches[] = $data;
		
	return $matches;
}

function ChopToScript($script)
	{
	$len =  (strlen($script) - strrpos($script,'/'));
	
	return(substr($script,1 - $len,$len));
	}

function ttdbc()
	{
	$server= '127.0.0.1';
	$username="root";
	$password="mslorensub13!#";
	$database="trendingtopics";
	mysql_connect($server,$username,$password) or die("Cannot connect to database");
	mysql_query('SET NAMES utf8');
	@mysql_select_db($database) or die( "Unable to select database");
	unset($username,$password);
	}

function hldbc()
	{
	$server= '127.0.0.1';
	$username="root";
	$password="mslorensub13!#";
	$database="hotlinks";
	mysql_connect($server,$username,$password) or die("Cannot connect to database");
	mysql_query('SET NAMES utf8');
	@mysql_select_db($database) or die( "Unable to select database");
	unset($username,$password);
	}
	
function CrunchTopics()
{
	return(true);
	global $facebook,$excludes,$commons;
	mysql_close();
	ttdbc();
	$query = "select timestamp from engine where user_id = '".$_SESSION['user_id']."'";
	$result = mysql_query($query);
	if (mysql_affected_rows() == 0)
		{
		$query = "insert into engine values ('".$_SESSION['user_id']."',".time().")";
		mysql_query($query) or CreateAlert(mysql_error());
		}
	else
		{
		while ($vresult = mysql_fetch_array($result))
			{
			if ($vresult['timestamp'] < (time() - 86400))
				{
				$query = "update engine set timestamp = '".time()."' where user_id = '".$_SESSION['user_id']."'";
				mysql_query($query) or  CreateAlert(mysql_error());
				}
			else
				return(true);
			}
		}
	
$fql = "SELECT message,status_id FROM status WHERE uid IN(select target_id from connection where source_id = '".$_SESSION['user_id']."') AND time > ".time()." - 86400 limit 50";
$feed =   $facebook->api_client-> fql_query($fql);
while (!is_array($feed) && $retries < 2)
		{
		$feed =   $facebook->api_client-> fql_query($fql);
		$retries ++;
		}
//echo $fql;
$words = array();
$i = 0;
foreach ($feed as $row)
	{
	$query = "insert into status values ('".$row['status_id']."','".$_SESSION['user_id']."','".time()."')";
	mysql_query($query);
    if (stristr(mysql_error(),"Duplicate entry"))
		continue;
	$i  ++;
	$message_words  = array();
	$message_words = explode(' ',$row['message']);
	//$words = array_merge($words,$message_words);
	$remember = false;
	foreach ($message_words as $word) 
		{
		$word = CleanWord($word);
		$all[$word] ++;
		if ($all[$word] == 2)
			{
			$remember = $word;
			}
		else
			$remember = false;
		if ($remember)
			{
			
			}
			
		if (strlen($word) < 4)
			continue;
		$word = strtolower($word);
		if (!(isEnglish($word)))
			continue;
		if (in_array($word,$excludes))
			continue;
		if (in_array($word,$commons ))
			continue;
		$scores[$word] ++;
		 
		if ($scores[$word] == 2)
			{
			
			}
		}
	
	}

$i = 0;
                       
foreach($scores as $word => $score)
	{
	//echo $word.' has '.$score.'<br />';
	$query = "insert into trends values ('".$word."','".$score."','".time()."');";
	mysql_query($query);	
	}
}

function CrunchLinks()
{
	
	global $facebook;
	mysql_close();
	hldbc();
	$fql =  "SELECT url,share_count FROM link_stat WHERE url IN(select url from link where owner in (select uid2 from friend where uid1 = '".$_SESSION['user_id']."' limit 50)  AND created_time > ".time()." - 86400*7 limit 100) AND share_count > 100";
$feed =   $facebook->api_client-> fql_query($fql);
while (!is_array($feed) && $retries < 2)
		{
		$feed =   $facebook->api_client-> fql_query($fql);
		$retries ++;
		}
		
foreach ($feed as $row)
	{
//	echo $row['url'].' '.$row['share_count'].' '.$row['like_count'].' '.$row['comment_count'].' '.$row['click_count'].' '.$row['normalized_url'].'<br />';
//	$shares[$row['url']] = $row['share_count'];
	if (isURL($row['url']))
		{
		$row['url'] = StreamLineURL($row['url']);
		$query = "insert into link values ('".$row['url']."','".time()."','".$row['share_count']."',0,'".time()."')";
		mysql_query($query);
		//echo ('got to here with '.mysql_error().' on link '.$row['url'].'<br />');
		if (stristr(mysql_error(),'Duplicate'))
			{
			$query = "select share_count,updated from link where url = '".$row['url']."'";
			$result = mysql_query($query);
			if (mysql_affected_rows() > 0)
				{
				while ($vresult = mysql_fetch_array($result))
					{
				//	echo "comparing ".$vresult['updated'].' with '.(time() - 15*60).' , gap makes out '.($vresult['updated'] - (time() - 15*60)).'<br />';
					if (time() - 15*60 > $vresult['updated'])
						{
						$gap = (time() - $vresult['updated']);
				//		echo 'got to here with 1)'.$vresult['updated'].' 2)'.$row['share_count'] .' 3)'.$vresult['share_count'].' 4)'.$gap.'<br />';
						$gap_shares = $row['share_count'] - $vresult['share_count'];
						$sph = floor($gap_shares/$gap*60*60);
						$query = "update link set SPH = '".$sph."',updated = '".time()."' where url = '".$row['url']."'";
						mysql_query($query);
						}
					}
				}
			}
		}
	}
}

function isURL($url)
	{
	if (strlen($url) < 15)
		return(false);
	return(preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i",$url));
	}
	
function StreamLineURL($url)
	{
	$url = chop($url,'/');
	return($url);
	}
	
function ParseYTID($url)
	{
	$qs = (parse_url($url));
	parse_str($qs['query'], $return);
	return($return['v']);
	}
	
function commaNumber($num)
	{
	if (is_numeric($num))
		{
		if ($num > 999999)
			{
			$num = substr_replace($num, ',', -3, 0);
			return(substr_replace($num, ',', -7, 0));
			}
		if ($num > 999)
			return ($num = substr_replace($num, ',', -3, 0));
		return ($num);
		}
	else
		return($num);
	}
	
function SubmitFlag()
	{
	global $flag_report;
	if ($_GET['flag_options'] != '' && isset($_GET['flag_options']) && $_GET['flag_options'] != 'null' && isset($_GET['flag_guid']) && $_GET['flag_guid'] != '')
		{
		$guid = StripUserInput($_GET['flag_guid']);
		$more = StripUserInput($_GET['flag_more']);
		$options = StripUserInput($_GET['flag_options']);
		$type = StripUserInput($_GET['flag_type']);
		$offender = StripUserInput($_GET['flag_offender']);
		$query = "select guid from flags where guid = '".$guid."'";
		mysql_query_alert($query);
		if (mysql_affected_rows() > 0)
			{
			$query = "update flags set option1 = option1 + 1 where guid = '".$guid."'";
			mysql_query_alert($query);
			}
		else
			{
			$query = "insert into flags values ('".$guid."','".$options."','".$more."','".time()."','".$_SESSION['user_id']."','".$type."','".$offender."','1','1','0','0')";
			mysql_query_alert($query);
			
			}
		
		//$query = "update users set warning = warning + 1 where user_id = '".$offender."'";
	//	mysql_query($query) or CreateAlert("Failed updating user warning level ".$query." ".mysql_error(),7);
		echo('<script type="text/javascript">alert("Your flag report has been submitted");</script>');
		}
	}
	
	
function ScrapePYS()
	{
	$feed = GraphAPI('/me/feed');	
	$hasIDs = '';
	$return = '';
	if (is_array($feed))
		{
		foreach ($feed['data'] as $post)
			{
			if ( $post['type'] == 'photo')
				{
				$hasIDs .= $post['object_id'].',';
				}		
			}
		$hasIDs = chop($hasIDs,',');	
		
		$objects = GraphAPI('/?ids='.$hasIDs.'&fields=width,height,id');
		}
//	echo '1)'.$hasIDs.'<br />';
	if (is_array($objects))
		{
		$hasIDs = '';
		foreach ($objects as $id => $object)
			{
			if ($object['width'] > 250 or $object['height'] > 250)
				{
				$hasIDs .= $id.',';
				}
			}
		$hasIDs = chop($hasIDs,',');	
		}	
//	echo '2)'.$hasIDs.'<br />';
	$engagement = GraphAPI('/?ids='.$hasIDs.'&fields=comments,likes&offset=1&limit=1');
	if (is_array($engagement))
		{
		foreach ($engagement as $id => $row)
			{
		//	echo '3)'.$id.'<br />';
			if (sizeof($row['likes']['data']) > 0 or sizeof($row['comments']['data']) > 0)
				$return .= $id.',';		
			}
			$return = chop($return,',');	
		}
	//echo '4)'.$return.'<br />';
	return($return);
	}
	
?>