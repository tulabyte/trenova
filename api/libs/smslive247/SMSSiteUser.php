<?php

	require_once("nusoap/nusoap.php");
	
	class SMSSiteUser {
		
		var $client;
		var	$nameSpace = "http://smslive247.com/bulksms/service";
		var	$smsLiveWSDL = "http://smslive247.com/bulksms/webservice/SMSSiteUser.asmx?wsdl";
		
		//constructor::SMSSiteUser
		function SMSSiteUser() {
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
		   		   
		function FlashUser($user_token, $RecieverName, $SMS_Count) {
			$params = array('user_token'=>$user_token,
							'RecieverName'=>$RecieverName,
							'SMS_Count'=>$SMS_Count);
			$ResponseInfo = $this->client->call("FlashUser",array($params));
			return $ResponseInfo;
		}
		   
		function Login($site_id, $UserName, $Password) {
			$params = array('site_id'=>$site_id,
							'UserName'=>$UserName,
							'Password'=>$Password);
			$ResponseInfo = $this->client->call("Login",array($params));
			return $ResponseInfo;
		}
		   
		function ChangePwd($user_token, $OldPassword, $NewPassword) {
			$params = array('user_token'=>$user_token,
							'OldPassword'=>$OldPassword,
							'NewPassword'=>$NewPassword);
			$ResponseInfo = $this->client->call("ChangePwd",array($params));
			return $ResponseInfo;
		}
		   
		function Recharge($user_token, $str16DigitCode) {
			$params = array('user_token'=>$user_token,
							'str16DigitCode'=>$str16DigitCode);
			$ResponseInfo = $this->client->call("Recharge",array($params));
			return $ResponseInfo;
		}
		   
		function UpdateUserInfo($user_token, $NewInfo) {
			$params = array('user_token'=>$user_token,
							'NewInfo'=>$NewInfo);
			$ResponseInfo = $this->client->call("UpdateUserInfo",array($params));
			return $ResponseInfo;
		}
		   
		function GetUserInfo($user_token) {
			$params = array('user_token'=>$user_token);
			$SMSSiteUserInfo = $this->client->call("GetUserInfo",array($params));
			return $SMSSiteUserInfo;
		}
		   
		function SendSMS($user_token, $NewSMS) {
			$params = array('user_token'=>$user_token,
							'NewSMS'=>$NewSMS);
			$ResponseInfo = $this->client->call("SendSMS",array($params));
			//echo "<pre><xmp>".$this->client->request."</xmp><pre>";
			return $ResponseInfo;
		}
		   
		function GetMessages($user_token, $from_date, $to_date) {
			$params = array('user_token'=>$user_token,
							'from_date'=>$from_date,
							'to_date'=>$to_date);
			$SentMessageInfo = $this->client->call("GetMessages",array($params));
			return $SentMessageInfo;
		}
		   
		function DeleteMessage($user_token, $MessageID) {
			$params = array('user_token'=>$user_token,
							'MessageID'=>$MessageID);
			$ResponseInfo = $this->client->call("DeleteMessage",array($params));
			return $ResponseInfo;
		}
		   
	}

?>