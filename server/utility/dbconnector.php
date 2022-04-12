<?php
namespace utility;

/**
 * dbconnector
 **/
class DbConnector {
	/**
	 *
	 * @var declared as protected
	 */
	private static $connection;
	
	// method to initialise
	private static function _init() {
		if (! self::$connection) {
			try {
				$dsn = 'mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_INSTANCE_NAME . ';charset=utf8';
				self::$connection = new \PDO( $dsn, DATABASE_USER_NAME, DATABASE_USER_PASSWORD );
				self::$connection->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
				self::$connection->setAttribute ( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
			} catch ( PDOException $e ) {
				die ( 'Connection error: ' . $e->getMessage () );
			}
		}
		return self::$connection;
	}
	// method to get connection
	public static function getConnection() {
		return self::_init ();
	}
	
	// method to commit transaction with parameter $connectionObj as parameter
	public static function commitTransaction(\PDO $connectionObj) {
		if (isset ( $connectionObj )) {
			$connectionObj->commit ();
		}
	}
	
	// method to rollback transaction with $connectionObj as parameter
	public static function rollbackTransaction(\PDO $connectionObj) {
		if (isset ( $connectionObj )) {
			$connectionObj->rollBack ();
		}
	}
	
	// method to get connection for proxy
	public static function getProxyConnection() {
		try {
			$dsn = 'mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_INSTANCE_NAME . ';charset=utf8';
			$proxyConnection = new \PDO ( $dsn, DATABASE_USER_NAME, DATABASE_USER_PASSWORD );
			$proxyConnection->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$proxyConnection->setAttribute ( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
			return $proxyConnection;
		} catch ( PDOException $e ) {
			die ( 'Connection error: ' . $e->getMessage () );
		}
	}
	
	// method to close connection
	public static function closeConnection() {
		self::$connection = null;
	}
}
