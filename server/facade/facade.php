<?php

namespace facade;

use utility\constant\CommonConstant;
use dao\Dao;
use utility\Errorlog;
use utility\UtilityMethods;
use utility\SessionHelper;
use utility\constant\SessionConstant;

class Facade {
	protected $errorLog;
	protected function _build_error_response($error_code, $message = null) {
		return array (
				CommonConstant::ERROR_CODE => $error_code,
				CommonConstant::ERROR_MESSAGE => $message 
		);
	}
	
	// Instantiate error logger
	protected function _errorLogger($className) {
		$this->errorLog = new Errorlog ( $className );
	}
	// Method to check is Error Level Is enabled.
	protected function _isErrorEnabled() {
		return $this->errorLog->isErrorEnabled ();
	}
	// Method to check is Trace Level Is enabled.
	protected function _isTraceEnabled() {
		return $this->errorLog->isTraceEnabled ();
	}
	// Method to check is Fatal Level Is enabled.
	protected function _isFatalEnabled() {
		return $this->errorLog->isFatalEnabled ();
	}
	// Method to check is warn Level Is enabled.
	protected function _isWarnEnabled() {
		return $this->errorLog->isWarnEnabled ();
	}
	// Method to check is Debug Level Is enabled.
	protected function _isDebugEnabled() {
		return $this->errorLog->isDebugEnabled ();
	}
	// Method to check is Error Level Is enabled.
	protected function _isInfoEnabled() {
		return $this->errorLog->isInfoEnabled ();
	}
	// Method to catch Error
	protected function _error($message, $throwable = NULL) {
		return $this->errorLog->error ( $message, $throwable );
	}
	// Method to catch Trace
	protected function _trace($message, $throwable = NULL) {
		return $this->errorLog->trace ( $message, $throwable );
	}
	// Method to catch Fatal Error
	protected function _fatal($message, $throwable = NULL) {
		return $this->errorLog->fatal ( $message, $throwable );
	}
	// Method to catch Warning
	protected function _warn($message, $throwable = NULL) {
		return $this->errorLog->warn ( $message, $throwable );
	}
	// Method to catch Debug
	protected function _debug($message, $throwable = NULL) {
		return $this->errorLog->debug ( $message, $throwable );
	}
	// Method to catch Info
	protected function _info($message, $throwable = NULL) {
		return $this->errorLog->info ( $message, $throwable );
	}
	// Get the variable Dump
	protected function _getVarDump($var) {
		return $this->errorLog->get_var_dump ( $var );
	}
	protected function _setSessionDetails($group_details, $user_details) {
		$session_array = array ();
		
		$start_time = time();
		SessionHelper::set(SessionConstant::SESSION_START_TIME, $start_time);
		SessionHelper::set(SessionConstant::SESSION_EXPIRE_TIME, ($start_time + (USER_SESSION_TIMEOUT_MINUTES * 60))); // ending a session in configured minutes from the starting time
		
		
		if (isset ( $group_details ['group_id'] ) && UtilityMethods::isNotEmpty ( $group_details ['group_id'] )) {
			$session_array [SessionConstant::GROUP_ID] = $group_details ['group_id'];
		}
		
		if (isset ( $group_details ['group_name'] ) && UtilityMethods::isNotEmpty ( $group_details ['group_name'] )) {
			$session_array [SessionConstant::GROUP_NAME] = $group_details ['group_name'];
		}
		
		if (isset ( $group_details ['group_type'] ) && UtilityMethods::isNotEmpty ( $group_details ['group_type'] )) {
			$session_array [SessionConstant::GROUP_TYPE] = $group_details ['group_type'];
		}
		
		if (isset ( $group_details ['group_attention'] ) && UtilityMethods::isNotEmpty ( $group_details ['group_attention'] )) {
			$session_array [SessionConstant::GROUP_ATTENTION] = $group_details ['group_attention'];
		}
		
		if (isset ( $group_details ['parent_group_id'] ) && UtilityMethods::isNotEmpty ( $group_details ['parent_group_id'] )) {
			$session_array [SessionConstant::PARENT_GROUP_ID] = $group_details ['parent_group_id'];
		}
		
		if (isset ( $user_details ['user_id'] ) && UtilityMethods::isNotEmpty ( $user_details ['user_id'] )) {
			$session_array [SessionConstant::USER_ID] = $user_details ['user_id'];
		}
		
		if (isset ( $user_details ['user_name'] ) && UtilityMethods::isNotEmpty ( $user_details ['user_name'] )) {
			$session_array [SessionConstant::USER_NAME] = $user_details ['user_name'];
		}
		
		if (isset ( $user_details ['first_name'] ) && UtilityMethods::isNotEmpty ( $user_details ['first_name'] )) {
			$session_array [SessionConstant::FIRST_NAME] = $user_details ['first_name'];
		}
		
		if (isset ( $user_details ['last_name'] ) && UtilityMethods::isNotEmpty ( $user_details ['last_name'] )) {
			$session_array [SessionConstant::LAST_NAME] = $user_details ['last_name'];
		}
		
		if (isset ( $user_details ['email_address'] ) && UtilityMethods::isNotEmpty ( $user_details ['email_address'] )) {
			$session_array [SessionConstant::EMAIL_ADDRESS] = $user_details ['email_address'];
		}
		
		if (isset ( $user_details ['phone_number'] ) && UtilityMethods::isNotEmpty ( $user_details ['phone_number'] )) {
			$session_array [SessionConstant::PHONE_NUMBER] = $user_details ['phone_number'];
		}
		
		if (isset ( $user_details ['access_permissions'] ) && UtilityMethods::isNotEmpty ( $user_details ['access_permissions'] )) {
			$session_array [SessionConstant::ACCESS_PREMISSION] = $user_details ['access_permissions'];
		}
		
		SessionHelper::set(SessionConstant::COMMON_PARAMETERS, $session_array);
		return $session_array;
	}
	
}
