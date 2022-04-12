<?php

namespace utility;

class SessionHelper {
	/**
	 *
	 * @var declared as protected
	 */
	protected static $_prefix = "portal_";
	
	// method to check status
	public static function _check_status() {
		if (session_id () == '' || ! isset ( $_SESSION )) {
			session_start ();
		}
	}
	public static function is_key_exists($key) {
		self::_check_status ();
		return array_key_exists ( self::$_prefix . $key, $_SESSION );
	}
	
	// method to get with $key and $default as parameters
	public static function get($key, $default = null) {
		self::_check_status ();
		if (isset ( $_SESSION [self::$_prefix . $key] )) {
			return $_SESSION [self::$_prefix . $key];
		}
		
		return $default;
	}
	
	// method to set with $key and $default as parameters
	public static function set($key, $value) {
		self::_check_status ();
		$_SESSION [self::$_prefix . $key] = $value;
	}
	
	// method to erase with $key as parameters
	public static function erase($key) {
		self::_check_status ();
		unset ( $_SESSION [self::$_prefix . $key] );
	}
	
	// method to destroy session
	public static function session_destroy() {
		self::_check_status ();
		session_destroy ();
	}
}
