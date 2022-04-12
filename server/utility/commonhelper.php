<?php
namespace utility;
use utility\constant\CommonConstant;
/**
 * commonhelper - helps for bootstrap requests
 **/
class CommonHelper{
	/**
	 *
     * @var declared as protected
	 */
	protected static $_prefix = "portal_";
	
	// method to check status
	public static function _check_status(){
		if(session_status() == PHP_SESSION_NONE){
			session_start();
		}
	}
	
	// method to get with $key and $default as parameters
	public static function getParam($key, $default = null){
		if (isset($GLOBALS[self::$_prefix.$key])){
			return $GLOBALS[self::$_prefix.$key];
		}elseif (isset($_SESSION[self::$_prefix.$key])){
			self::_check_status();
			return $_SESSION[self::$_prefix.$key];
		}elseif (isset($_REQUEST[self::$_prefix.$key])){
			return $_REQUEST[self::$_prefix.$key];
		}else{
			return $default;
		}
	}
	
	// method to set with $key and $default as parameters
	public static function setParam($key, $value, $type=NULL){
		if(UtilityMethods::isEqual($type, CommonConstant::STORAGE_TYPE_SESSION)){
			self::_check_status();
			$_SESSION[self::$_prefix.$key] = $value;
		}else{
			$GLOBALS[self::$_prefix.$key] = $value;
		}
	}
	
	// method to erase with $key as parameters
	public static function erase($key, $type=NULL){
		if(UtilityMethods::isEqual($type, CommonConstant::STORAGE_TYPE_SESSION)){
			self::_check_status();
			unset($_SESSION[self::$_prefix.$key]);
		}else{
			unset($GLOBALS[self::$_prefix.$key]);
		}
	}
	
	// method to destroy session
	public static function session_destroy(){
		self::_check_status();
		session_destroy();
	}
	
	// method to erase with $key as parameters
	public static function build_header_param($original_headers){
		$headers = array();
		foreach($original_headers as $header_key => $header_value) {
			switch($header_key) {
				case 'X-User-ID':
					$headers['user_id'] = $header_value;
					break;
				case 'X-Group-ID':
					$headers['group_id'] = $header_value;
					break;
				case 'X-User-Token':
					$headers['user_token'] = $header_value;
					break;
				case 'X-Nonce-Token':
					$headers['nonce_token'] = $header_value;
					break;
				case 'X-Password':
					$headers['password'] = $header_value;
					break;
				case 'X-Customer-Group-ID':
					$headers['context_group_id'] = $header_value;
					break;
				case 'X-Hmac':
					$headers['hmac'] = $header_value;
					break;
				default:
					break;
			}
		}
		return $headers;
	}
	
	//method to get presentation group
	public static function getPresentationInfo(){
		//return self::getParam(CommonConstant::PRESENTATION_USER_DETAILS);
	}

	//method to get logged in group
	public static function getLoggedInInfo(){
		//return self::getParam(CommonConstant::LOGGED_USER_DETAILS);
	}
	
	//method to get Context group
	public static function getContextInfo(){
		//return self::getParam(CommonConstant::CONTEXT_INFO_DETAILS);
	}
	
	//method to get presentation user Value
	public static function getPresentationValue($key){
		$details = self::getPresentationInfo();
		if(!array_key_exists($key, $details)){
			UtilityMethods::printTrace("$key is not in getPresentationValue()");
			return;
		}
		return $details[$key];
	}
	
	//method to get logged in user value
	public static function getLoggedInValue($key){
		$details = self::getLoggedInInfo();
		if(!is_array($details)){
			return;
		}
		if(!array_key_exists($key, $details)){
			UtilityMethods::printTrace("$key is not in getLoggedInValue()");
			return;
		}
		return $details[$key];
	}
	
	
	//method to get Context group values based on key
	public static function getContextValue($key){
		$details = self::getContextInfo();
		if(!array_key_exists($key, $details)){
			UtilityMethods::printTrace("$key is not in getContextValue()");
			return;
		}
		return $details[$key];
	}
	
	//method to get requested API version
	public static function getRequestAPIVersion(){
		return self::getParam(CommonConstant::REQUESTED_API_VERSION);
	}
}