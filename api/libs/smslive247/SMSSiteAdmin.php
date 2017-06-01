<?php

	require_once("nusoap/nusoap.php");
	
	class SMSSiteAdmin {
		
		var $client;
		var	$nameSpace = "http://smslive247.com/bulksms/service";
		var	$smsLiveWSDL = "http://smslive247.com/bulksms/webservice/SMSSiteAdmin.asmx?wsdl";
		
		//constructor::SMSSiteAdmin
		function SMSSiteAdmin() {
			if (strlen(ProxyHost) > 0) {
				$this->client = new soapclient($this->smsLiveWSDL, 'wsdl',
				ProxyHost, ProxyPort, ProxyUsername, ProxyPassword);
			}
			else {
				$this->client = new soapclient($this->smsLiveWSDL, 'wsdl');
			}
			if ($this->client->getError()) {
				die($this->client->getError());
			}
		}

		function NewSiteUser($site_token, $User) {
			$params = array('site_token'=>$site_token,
							'User'=>$User);
			$ResponseInfo = $this->client->call("NewSiteUser",array($params));
			return $ResponseInfo;
		}

		function UpdateSiteUser($site_token, $User) {
			$params = array('site_token'=>$site_token,
							'User'=>$User);
			$ResponseInfo = $this->client->call("UpdateSiteUser",array($params));
			return $ResponseInfo;
		}
		   
		function DeleteSiteUser($site_token, $Username) {
			$params = array('site_token'=>$site_token,
							'Username'=>$Username);
			$ResponseInfo = $this->client->call("DeleteSiteUser",array($params));
			return $ResponseInfo;
		}
		   
		function GetAllSiteUsers($site_token) {
			$params = array('site_token'=>$site_token);
			$SMSSiteUserInfo = $this->client->call("GetAllSiteUsers",array($params));
			return $SMSSiteUserInfo;
		}
		   
		function FlashSiteUser($site_token, $RecieverName, $SMS_Count) {
			$params = array('site_token'=>$site_token,
							'RecieverName'=>$RecieverName,
							'SMS_Count'=>$SMS_Count);
			$ResponseInfo = $this->client->call("FlashSiteUser",array($params));
			return $ResponseInfo;
		}
		   
		function Login($site_id, $Password) {
			$params = array('site_id'=>$site_id,
							'Password'=>$Password);
			$ResponseInfo = $this->client->call("Login",array($params));
			return $ResponseInfo;
		}
		   
		function ChangePwd($site_token, $OldPassword, $NewPassword) {
			$params = array('site_token'=>$site_token,
							'OldPassword'=>$OldPassword,
							'NewPassword'=>$NewPassword);
			$ResponseInfo = $this->client->call("ChangePwd",array($params));
			return $ResponseInfo;
		}
		   
		function Recharge($site_token, $str16DigitCode) {
			$params = array('site_token'=>$site_token,
							'str16DigitCode'=>$str16DigitCode);
			$ResponseInfo = $this->client->call("Recharge",array($params));
			return $ResponseInfo;
		}
		   
		function UpdateAccountInfo($site_token, $NewInfo) {
			$params = array('site_token'=>$site_token,
							'NewInfo'=>$NewInfo);
			$ResponseInfo = $this->client->call("UpdateAccountInfo",array($params));
			return $ResponseInfo;
		}
		   
		function GetAccountInfo($site_token) {
			$params = array('site_token'=>$site_token);
			$SMSSiteInfo = $this->client->call("GetAccountInfo",array($params));
			return $SMSSiteInfo;
		}
		   
		function SendSMS($site_token, $NewSMS) {
			$params = array('site_token'=>$site_token,
							'NewSMS'=>$NewSMS);
			$ResponseInfo = $this->client->call("SendSMS",array($params));
			return $ResponseInfo;
		}
		   
		function GetMessages($site_token, $from_date, $to_date) {
			$params = array('site_token'=>$site_token,
							'from_date'=>$from_date,
							'to_date'=>$to_date);
			$SentMessageInfo = $this->client->call("GetMessages",array($params));
			return $SentMessageInfo;
		}
		   
		function DeleteMessage($site_token, $MessageID) {
			$params = array('site_token'=>$site_token,
							'MessageID'=>$MessageID);
			$ResponseInfo = $this->client->call("DeleteMessage",array($params));
			return $ResponseInfo;
		}
		   
	}

?>