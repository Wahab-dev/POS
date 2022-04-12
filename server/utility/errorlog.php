<?php

namespace utility;

use utility\constant\SessionConstant;

/*
 * This class acts as an interface between logger and application
 */
class Errorlog {
	private $log;
	private $name;
	private $level;
	private static $error_level = array (
			"FATAL",
			"ERROR",
			"WARN",
			"INFO",
			"DEBUG",
			"TRACE" 
	);
	const FATAL = "FATAL";
	const ERROR = "ERROR";
	const WARN = "WARN";
	const INFO = "INFO";
	const DEBUG = "DEBUG";
	const TRACE = "TRACE";
	const ALL = "ALL";
	const NONE = "NONE";
	
	public function __construct($loggerName) {
		$this->name = $loggerName;
		$this->level = PRINTED_LOG_LEVEL;
	}

	private function _error_level_hierarchy() {
		$error_level = self::$error_level;
		$level_order = array_search ( $this->level, $error_level );
		if (UtilityMethods::isEqual ( $this->level, self::ALL, true )) {
			$level_order = count ( $error_level ) - 1;
		} else if (UtilityMethods::isEqual ( $this->level, self::NONE, true )) {
			return;
		}
		$levels = array ();
		for($max_order = 0; $max_order <= $level_order; $max_order ++) {
			$levels [$error_level [$max_order]] = TRUE;
		}
		return $levels;
	}
	public function error($message, $throwable = null) {
		if ($this->isErrorEnabled ()) {
			$this->log ( self::ERROR, $message, $throwable );
		}
	}
	public function fatal($message, $throwable = null) {
		if ($this->isFatalEnabled ()) {
			$this->log ( self::FATAL, $message, $throwable );
		}
	}
	public function trace($message, $throwable = null) {
		if ($this->isTraceEnabled ()) {
			$this->log ( self::TRACE, $message, $throwable );
		}
	}
	public function debug($message, $throwable = null) {
		if ($this->isDebugEnabled ()) {
			$this->log ( self::DEBUG, $message, $throwable );
		}
	}
	public function info($message, $throwable = null) {
		if ($this->isInfoEnabled ()) {
			$this->log ( self::INFO, $message, $throwable );
		}
	}
	public function warn($message, $throwable = null) {
		if ($this->isWarnEnabled ()) {
			$this->log ( self::WARN, $message, $throwable );
		}
	}
	public function isFatalEnabled() {
		$error = $this->_error_level_hierarchy ();
		if (isset ( $error [self::FATAL] ) && $error [self::FATAL]) {
			return $error [self::FATAL];
		}
	}
	public function isDebugEnabled() {
		$error = $this->_error_level_hierarchy ();
		if (isset ( $error [self::DEBUG] ) && $error [self::DEBUG]) {
			return $error [self::DEBUG];
		}
	}
	public function isInfoEnabled() {
		$error = $this->_error_level_hierarchy ();
		if (isset ( $error [self::INFO] ) && $error [self::INFO]) {
			return $error [self::INFO];
		}
	}
	public function isWarnEnabled() {
		$error = $this->_error_level_hierarchy ();
		if (isset ( $error [self::WARN] ) && $error [self::WARN]) {
			return $error [self::WARN];
		}
	}
	public function isErrorEnabled() {
		$error = $this->_error_level_hierarchy ();
		if (isset ( $error [self::ERROR] ) && $error [self::ERROR]) {
			return $error [self::ERROR];
		}
	}
	public function isTraceEnabled() {
		$error = $this->_error_level_hierarchy ();
		if (isset ( $error [self::TRACE] ) && $error [self::TRACE]) {
			return $error [self::TRACE];
		}
	}
	public function log($tag, $message, $throwable = null) {
		if (isset ( $message )) {
			$passthru_user = "";
			$logged_in_username = CommonHelper::getLoggedInValue ( "user_login_name" );
			$group_identifier = CommonHelper::getLoggedInValue ( "group_identifier" );
			$log_data = $tag . " " . $this->name . " [" . $logged_in_username . "@" . $group_identifier . "] => " . $message . " " . $throwable;			
			error_log ( $log_data );
		}
	}
	public function printLog($tag, $controllers, $message, $logged_users, $throwable = null) {
		if (isset ( $message )) {
			$log_data = $tag . " " . $controllers . " [" . $logged_users . "] => " . $message . " " . $throwable;
			error_log ( $log_data );
		}
	}
	public function get_var_dump($var) {
		ob_start ();
		var_dump ( $var );
		$dump = ob_get_clean ();
		return "INPUT(S): " . $dump . "\tMESSAGE: ";
	}
}

?>
