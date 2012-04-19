<?php 
require('dbc.php');
require('functions.php');
require('init.php');
MetaHeader($GlobalKeywords . ' '.stripslashes($GlobalTitle),stripslashes($GlobalTitle) . " ". stripslashes($GlobalDesc),' '.stripslashes($GlobalTitle));

require('top.php');

echo GetPrivacyPolicyRT();

mysql_close();
?>

