<?php
define ( 'DS', DIRECTORY_SEPARATOR );
define ( 'BASE_PATH', dirname ( __FILE__ ) );
define ( 'CONTEXT_PATH', dirname ( __FILE__ ) . DS );
define ( 'SERVER_PATH', CONTEXT_PATH . 'server' . DS );
define ( 'UTILITY_PATH', SERVER_PATH . 'utility' . DS );
define ( 'CONFIG_PATH', CONTEXT_PATH . 'config' . DS );
define ( 'ERROR_MSG_PATH', UTILITY_PATH . 'error' . DS );

$error_log = "/var/log/httpd/pos_log_" . date ( "Y-m-d" ) . ".log";
error_reporting ( E_ALL | E_STRICT );
ini_set ( 'display_errors', "1" );
ini_set ( "log_errors", "1" );
ini_set ( "error_log", $error_log );

define ( "CACHE_VERSION", "99" );
require_once CONFIG_PATH . 'config.php';
function __autoload($class) {
	$class = SERVER_PATH . strtolower ( str_replace ( '\\', '/', $class ) . '.php' );
	if (file_exists ( $class )) {
		require_once $class;
	} else {
		debug_print_backtrace ();
		die ( 'File not found : ' . $class );
	}
}