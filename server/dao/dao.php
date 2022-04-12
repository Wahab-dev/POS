<?php
namespace dao;
/**
 * dao - base dao to all other dao 
 **/
abstract class Dao
{

	public static $errorLog=NULL;
	
	// method to get logger instance
	public static function getLoggerInstance($className){
		self::$errorLog = new ErrorLogger($className);
	}
	
	// method create model with $modelName as parameter
	public static function createModel($modelName){
		$modelName .= 'Model';
		return new $modelName();
	}
	
	// method to executeDMLQuery with $sql_query and $data as parameter
	public static function executeDMLQuery($connection,$sql_query,$data,$return_rowcount = FALSE){
		if (!$sql_query){
			throw new Exception("No SQL query!");
		}
		if (!$data){
			throw new Exception("No data for query!");
		}
		$sth = $connection->prepare($sql_query);
		if($return_rowcount === FALSE) {
			return $sth->execute($data);
		} else {
			$status = $sth->execute($data);
			if($status) {
				return $sth->rowCount();
			} else {
				return FALSE;
			}
		}
	}
	
	// method to get all values with parameter $sql_query and parameter $data is set as null
	public static function getAll($connection,$sql_query,$data = null){
		if (!$sql_query){
			throw new Exception("No SQL query!");
		}
		$sth = $connection->prepare($sql_query);
		$sth->execute($data);
		return $sth->fetchAll();
	}
	
	// method to get row of records with parameter $sql_query and parameter $data is set as null
	public static function getRow($connection,$sql_query,$data = null){
		if (!$sql_query){
			throw new Exception("No SQL query!");
		}
		$sth = $connection->prepare($sql_query);
		$sth->execute($data);
		return $sth->fetch();
	}
	
	// method to get single column values as one dimensional array
	public static function getSingleColumnAsArray($connection,$sql_query,$data = null) {
		if (!$sql_query){
			throw new Exception("No SQL query!");
		}
		$sth = $connection->prepare($sql_query);
		$sth->execute($data);
		$rows = $sth->fetchAll();
		$column_array = array_map('current', $rows);
		return $column_array;
	}
	
        // method to get row of records with parameter $sql_query and parameter $data is set as null
	public static function fetchColumn($connection,$sql_query,$data = null){
		if (!$sql_query){
			throw new Exception("No SQL query!");
		}
		$sth = $connection->prepare($sql_query);
		$sth->execute($data);
		return $sth->fetchColumn();
	}
	
	// method to get row of records with parameter $statement and parameter $data is set as null
	public static function fetchColumnByStatement($statement,$data = null){
		if (!$statement){
			throw new Exception("No Statement!");
		}
		$statement->execute($data);
		return $statement->fetchColumn();
	}
        
	// method to get statement with $sql_query as parameter
	public static function getStatement($connection,$sql_query){
		if (!$sql_query){
			throw new Exception("No SQL query!");
		}
		$sth = $connection->prepare($sql_query);
		return $sth;
	}
	
	/**
	 * method to update table based on parameters
	 * @param $connection
	 * @param $table_name to be updated
	 * @param $primary_key_details - key as primary column name and thier respective values 
	 * @param $parameter_details - key as column name and thier respective values 
	 */
	public static function updateBasedOnGivenKey($connection, $table_name, $primary_key_details, $parameter_details){
		$param_array = array();
		$sql = "UPDATE `{$table_name}` SET last_updated_stamp=current_timestamp ";
		if(count($parameter_details) > 0){
			foreach($parameter_details as $key=>$value){
				$sql .= ", `{$key}` = ?";
				$param_array[] = $value;
			}
		}
		if(count($primary_key_details) > 0){
			$sql .= " WHERE 1=1 ";
			foreach($primary_key_details as $key=>$value){
				$sql .= " AND {$key} = ?";
				$param_array[] = $value;
			}
		}
		self::executeDMLQuery($connection,$sql, $param_array);
	}

	//Method to get sequence number
	public static function get_sequence_number($connection, $sequence_for,$update_sequence_count) {
		$sql = " select sequence_value from sequence_number_config where sequence_key = ?";
		$current_sequence_value = self::fetchColumn($connection, $sql, array($sequence_for));
		$next_sequence_starts = bcadd($update_sequence_count,$current_sequence_value,0);
		self::set_sequence_number($connection, $sequence_for,$next_sequence_starts);
		return $current_sequence_value;
	}
	
	//Method to set sequence number
	public static function set_sequence_number($connection, $sequence_for,$next_sequence_starts) {
		$sql = "update sequence_number_config set sequence_value=? where sequence_key=?";
		self::executeDMLQuery($connection, $sql, array($next_sequence_starts,$sequence_for));
		return;
	}
	
	// Method to get country list
	public static function get_country_details($connection, $fields="country_name", $country_name=null){
		$sql = "select {$fields} from `country`";
		if(UtilityMethods::isNotEmpty($country_name)){
			$sql .= "where country_name = '".$country_name."'";
			return self::fetchColumn($connection, $sql);
		}
		else{
			return self::getAll($connection, $sql);
		}
	}
	
	// Method to get country list
	public static function get_country_list_drop_down($connection){
		$sql = "select country_name from `country`";
		return self::getSingleColumnAsArray($connection, $sql);
	}
	
	//Method to check is Error Level Is enabled.
	public static function _isErrorEnabled(){
		return self::$errorLog->isErrorEnabled();
	}
	//Method to check is Trace Level Is enabled.
	public static function _isTraceEnabled(){
		return self::$errorLog->isTraceEnabled();
	}
	//Method to check is Fatal  Level Is enabled.
	public static function _isFatalEnabled(){
		return self::$errorLog->isFatalEnabled();
	}
	//Method to check is warn  Level Is enabled.
	public static function _isWarnEnabled(){
		return self::$errorLog->isWarnEnabled();
	}
	//Method to check is Debug  Level Is enabled.
	public static function _isDebugEnabled(){
		return self::$errorLog->isDebugEnabled();
	}
	//Method to check is Error Level Is enabled.
	public static function _isInfoEnabled(){
		return self::$errorLog->isInfoEnabled();
	}
	//Method to catch Error
	public static function _error($message,$throwable=NULL){
		return self::$errorLog->error($message,$throwable);
	}
	//Method to catch Trace
	public static function _trace($message,$throwable=NULL){
		return self::$errorLog->trace($message,$throwable);
	}
	//Method to catch Fatal Error
	public static function _fatal($message,$throwable=NULL){
		return self::$errorLog->fatal($message,$throwable);
	}
	//Method to catch Warning
	public static function _warn($message,$throwable=NULL){
		return self::$errorLog->warn($message,$throwable);
	}
	//Method to catch Debug
	public static function _debug($message,$throwable=NULL){
		return self::$errorLog->debug($message,$throwable);
	}
	// Method to catch Info
	public static function _info($message,$throwable=NULL){
		return self::$errorLog->info($message,$throwable);
	}
	// Get the variable Dump
	public static function _getVarDump($var){
		return self::$errorLog->get_var_dump($var);
	}
	
	/**
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from
	 * $params are are in the same order as specified in $query
	 *
	 * @param string $query The sql query with parameter placeholders
	 * @param array $params The array of substitution parameters
	 * @return string The interpolated query
	 */
	public static function interpolateQuery($query, $params) {
		$keys = array();
		$values = $params;
	
		# build a regular expression for each parameter
		foreach ($params as $key => $value) {
			if (is_string($key)) {
				$keys[] = '/'.$key.'/';
			} else {
				$keys[] = '/[?]/';
			}
	
			if (is_array($value))
				$values[$key] = implode(',', $value);
	
			if (is_null($value))
				$values[$key] = 'NULL';
		}
		// Walk the array to see if we can add single-quotes to strings
		array_walk($values, create_function('&$v, $k', 'if (!is_numeric($v) && $v!="NULL") $v = "\'".$v."\'";'));
	
		$query = preg_replace($keys, $values, $query, 1, $count);
	
		return $query;
	}
}