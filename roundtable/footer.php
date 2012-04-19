<?php

echo (' 
	  <script type="text/javascript"> 
	 window.fbAsyncInit = function() {
		FB.init({ appId:  "'.$GLOBALS['app_id'].'",frictionlessRequests : true, status: true, cookie: true, xfbml: true, oauth  : true,channelUrl:"//staging.soulbounds.com/channel.html"  });
		FB.Canvas.setAutoGrow();
		runPageFunctions();
	};
	(function() {
		var e = document.createElement("script");
		e.type = "text/javascript";
		e.async = true;
		e.src = document.location.protocol + "//connect.facebook.net/en_US/all.js";
		document.getElementById("fb-root").appendChild(e);
		
		}());</script></div></body></html>');	

mysql_close();
?>