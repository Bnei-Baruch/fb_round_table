<?php 

require('dbc.php');
require('functions.php');
$scope_perms = "email,create_event,publish_actions";
require('init.php');
require('top.php');


//$attach = array("name" => "my api created event","description" => "my api created event description","start_time" => "1334275200","end_time" => "1334289600","location" => "location","privacy" => "OPEN");
//$facebook->api('me/events','POST',$attach); 

$attach = array("discussion" => "http://staging.soulbounds.com/roundtable/table.php?tid=284835428");
$fu =  $facebook->api('me/apctestenvo:join','POST',$attach); 
print_r($fu);

require('footer.php');




//echo 'fu';

?>

