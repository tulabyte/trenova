<?php
	
	//webservice classes needed
	require_once("SMSSiteUser.php");
	require_once("SMSSiteAdmin.php");

    /*  
		YOUR SMSLIVE247.COM ACCOUNT INFORMATION
        Your Reseller Email is the email you registered with on SMSLive247.com. You should
        create a sub-account under "SMS Hosting" and assign the sub-account a password. See
        the Readme.doc on how this is done or call support on (234)08057076520.
    */
	define(SMSLiveResellerEmail, "yemgab@yahoo.com", true);
	define(SMSLiveSubAccountName, "TRENOVA", true);
	define(SMSLiveSubAccountPassword, "trenova1720", true);

     /*  
		YOUR PROXY SERVER SETTINGS
        Set ProxyHost to your proxy IP or Hostname *ONLY* if your webserver connects to 
        the Internet thru a proxy server. This is usually the case if you are running
        the webserver from your local computer. Please Set ProxyHost = "" if you host
        on a paid hosting company.
    */
	define(ProxyHost, "", true);
	define(ProxyPort, "", true);
	define(ProxyUsername, "", true);
	define(ProxyPassword, "", true);
	
?>