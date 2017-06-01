<?php

/*This class handles SMS sending*/
class smsHandler {

	// constructor
	function __construct() {     
        // include config
        require_once '../config.php';

        //webservice classes needed
		require_once("../libs/smslive247/SMSSiteUser.php");

	    /*  
			YOUR SMSLIVE247.COM ACCOUNT INFORMATION
	        Your Reseller Email is the email you registered with on SMSLive247.com. You should
	        create a sub-account under "SMS Hosting" and assign the sub-account a password. See
	        the Readme.doc on how this is done or call support on (234)08057076520.
	    */
		define('SMSLiveResellerEmail', "yemgab@yahoo.com", true);
		define('SMSLiveSubAccountName', "TRENOVA", true);
		define('SMSLiveSubAccountPassword', "trenova1720", true);

	     /*  
			YOUR PROXY SERVER SETTINGS
	        Set ProxyHost to your proxy IP or Hostname *ONLY* if your webserver connects to 
	        the Internet thru a proxy server. This is usually the case if you are running
	        the webserver from your local computer. Please Set ProxyHost = "" if you host
	        on a paid hosting company.
	    */
		define('ProxyHost', "", true);
		define('ProxyPort', "", true);
		define('ProxyUsername', "", true);
		define('ProxyPassword', "", true);
    }

    // send an sms
    /* ==================================================
	SUBMIT A REQUEST TO SEND THE SMS VIA THE HTTP API
	=====================================================*/
	function SendSMS ($message, $sender, $sendto, $msgType) {
		
		$UserObj = new SMSSiteUser; //create an SMS_User object

		//call the login method
		$res = $UserObj->Login(SMSLiveResellerEmail.":".SMSLiveSubAccountName, SMS_SUBACCT, SMS_SUBACCTPWD);

		$auth_token = $res['LoginResult']['ExtraMessage'];

		//create the SMS object
		$newSMS = array('Message'=>$message,
			'MessageType'=>'TEXT',			//TEXT or FLASH
			'MessageID'=>0,					//0 = new message
			'MessageFolder'=>'SENT_FOLDER',	//required
			'DeliveryEmail'=>'',			//optional
			'Destination'=>array('string'=>$sendto), // recipient
			'CallBack'=>$sender);			//SenderID: max 11 chars

		//call the SendSMS method
		$res = $UserObj->SendSMS($auth_token, $newSMS);

		return $res['SendSMSResult'];
	}

}