<?php

namespace utility;

use controller\UserController;
use utility\constant\SessionConstant;

$action = "index";
$param1 = "";
$param2 = "";
$param3 = "";
$param4 = "";
$param5 = "";

if (isset ( $_GET ['pos'] )) {
	
	$params = array ();
	$params = explode ( "/", $_GET ['pos'] );
	$message = '';
} else {
	die ( 'Invalid URL. Please check the URL' );
}

session_start ();
$controller = $params [0];

if (! empty ( $params [1] )) {
	$action = $params [1];
}

if (! empty ( $params [2] )) {
	$param1 = $params [2];
}

if (! empty ( $params [3] )) {
	$param2 = $params [3];
}

if (! empty ( $params [4] )) {
	$param3 = $params [4];
}

if (! empty ( $params [5] )) {
	$param4 = $params [5];
}

if (! empty ( $params [6] )) {
	$param5 = $params [6];
}

if (isset ( $params [0] ) && isset ( $params [1] )) {
	if (UtilityMethods::isEqual($params[0], 'user') && UtilityMethods::isEqual($params[1], 'authenticate')) {
		$user_controller = new UserController('user');
		$user_controller->authenticate();
	}

	if (UtilityMethods::isEqual($params[0], 'user') && UtilityMethods::isEqual($params[0], 'logout')) {
		$user_controller = new UserController('user');
		$user_controller->logout();
	}
}

if (! SessionHelper::is_key_exists ( SessionConstant::COMMON_PARAMETERS )) {
	SessionHelper::session_destroy ();
	$response = array (
			'SESSION_ERROR' => 'UNAUTHORIZED_ACCESS'
	);
	echo json_encode ( $response );
	exit ();
} else {
	// Check for Session Timeout
	$now = time (); // checking the time now when bootstrap loads
	if ($now > SessionHelper::get ( SessionConstant::SESSION_EXPIRE_TIME )) {
		// Reset has_logged_in falg to false.
		SessionHelper::session_destroy ();
		$response = array (
				'SESSION_ERROR' => 'SESSION_TIMED_OUT'
		);
		echo json_encode ( $response );
		exit ();
	} else {
		// update ending a session in configured minutes from the current time
		SessionHelper::set ( SessionConstant::SESSION_EXPIRE_TIME, (time () + (USER_SESSION_TIMEOUT_MINUTES * 60)) );

		$details = SessionHelper::get ( SessionConstant::COMMON_PARAMETERS );
		// Url based validation
		$unauthorized_access = false;
	}
}

$class = '\controller\\' . ucfirst ( $controller ) . "Controller";
$load = new $class ( $controller );

if (method_exists ( $load, $action )) {
	$load->{$action} ( $param1, $param2, $param3, $param4, $param5 );
} else {
	die ( 'Invalid method. Please check the URL. ' . $controller . ' - ' . $action );
}