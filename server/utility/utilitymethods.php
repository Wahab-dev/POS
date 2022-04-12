<?php

namespace utility;

use utility\constant\CommonConstant;

class UtilityMethods {
	
	// method to isNotEqual with $value1 and $value2 as parameters
	public static function isStringNotEqual($value1, $value2, $case_insensitive = false) {
		return ! UtilityMethods::isStringEqual ( $value1, $value2, $case_insensitive );
	}
	
	// method to isNotEqual with $value1 and $value2 as parameters
	public static function isNotEqual($value1, $value2, $case_insensitive = false) {
		return ! UtilityMethods::isEqual ( $value1, $value2, $case_insensitive );
	}
	
	// method to isEmpty with $value1 as parameter
	public static function isEmpty($value1) {
		return (! isset ( $value1 ) || empty ( $value1 ));
	}
	public static function isEmptyString($value1) {
		if (! isset ( $value1 )) {
			return true;
		}
		$value1 = trim ( $value1 );
		return empty ( $value1 );
	}
	public static function isValueSet($value) {
		return (isset ( $value ));
	}
	
	// method to isEmpty with $value1 as parameter
	public static function isNotEmpty($value1) {
		return ! UtilityMethods::isEmpty ( $value1 );
	}
	
	// method to convertIfNull with $value and $default_value as parameters
	public static function convertIfNull($value, $default_value) {
		return UtilityMethods::isEmpty ( $value ) ? $default_value : $value;
	}
	public static function isStringEqual($value1, $value2, $case_insensitive = false) {
		if ($case_insensitive) {
			return (strcasecmp ( $value1, $value2 ) == 0);
		} else {
			return (strcmp ( $value1, $value2 ) == 0);
		}
	}
	
	// method to isEqual with $value1 and $value2 as parameters
	public static function isEqual($value1, $value2, $case_insensitive = false) {
		if (is_string ( $value1 ) && is_string ( $value2 )) {
			return self::isStringEqual ( $value1, $value2, $case_insensitive );
		}
		return $value1 === $value2;
	}
	
	// Method to get the filename without extension
	public static function get_file_without_extension($file_name) {
		$info = pathinfo ( $file_name );
		$file_name = basename ( $file_name, '.' . $info ['extension'] );
		
		return $file_name;
	}
	public static function get_file_extension($file_name) {
		$info = pathinfo ( $file_name );
		return $info ['extension'];
	}
	
	// Method to get module name from class name
	public static function check_error_exist($response) {
		if (is_array ( $response ) && isset ( $response [CommonConstant::ERROR_CODE] )) {
			return true;
		} else {
			return false;
		}
	}
		
	// Method to check request device is mobile or not
	public static function is_mobile_device() {
		return preg_match ( "/(android|webos|avantgo|iphone|ipad|ipod|blackberry|iemobile|bolt|
							bo‌​ost|cricket|docomo|fone|hiptop|mini|opera mini|kitkat|mobi|palm|
							phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER ["HTTP_USER_AGENT"] );
	}
	
	public static function getValueFromArray($array,$key,$default_value = NULL){
		if(array_key_exists($key, $array)){
			return $array[$key];
		}
		return $default_value;
	}
	
	public static function getPageLimit($parameter_array,$maxlimit){
		if(!array_key_exists(CommonConstant::QUERY_PARAM_LIMIT, $parameter_array) || $parameter_array[CommonConstant::QUERY_PARAM_LIMIT] > $maxlimit ){
			return $maxlimit;
		}
		return $parameter_array[CommonConstant::QUERY_PARAM_LIMIT];
	}
	
	//method to format order numbers
	public static function format_order_number($number, $group_id) {
		$format = '%1$04d-%2$04d';
		return sprintf($format, $number, $group_id);
	}
}
