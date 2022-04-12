<?php

namespace utility;

use utility\constant\SessionConstant;

class SessionUtility {
	public static function set_login_details($user_details) {
		foreach ( $user_details as $key => $value ) {
			SessionHelper::set ( $key, $value );
		}
	}
	public static function get_login_user_id() {
		return SessionHelper::get ( SessionConstant::LOGGIN_USER_ID, NULL );
	}
	public static function set_session_expiry_time() {
		return SessionHelper::set ( SessionConstant::SESSION_EXPIRY_TIME, time () + (60 * USER_SESSION_TIMEOUT_MINUTES) );
	}
	public static function get_session_expiry_time() {
		return SessionHelper::get ( SessionConstant::SESSION_EXPIRY_TIME, NULL );
	}
}
