<?php

namespace facade;

use utility\constant\CommonConstant;
use utility\constant\PageSizeConstant;
use dao\DiscountDao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;
use utility\constant\ApiResponseConstant;
use utility\DbConnector;
use dao\Dao;
use dao\ProductDao;

class DiscountFacade extends Facade {
	public function __construct() {
		$this->_errorLogger ( __CLASS__ );
	}
	
	/* for getting Discount list */
	public function getAllDiscountDetails($parameter_array) {
		$response_array = array ();
		try {
			$connection = DbConnector::getConnection ();
			$search_text = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SEARCH_KEYWORD, "" );
			$limit = UtilityMethods::getPageLimit ( $parameter_array, PageSizeConstant::GROUP_PAGE_LIMIT );
			$offset = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_OFFSET, 0 );
			$order_by = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_BY, '' );
			$order_type = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_ORDER, '' );
			$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$discount_fields ) );
			
			$result_array = DiscountDao::getAllDiscountDetails ( $connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $parameter_array ['group_id'] );
			$response_array [CommonConstant::QUERY_PARAM_OFFSET] = $offset;
			$response_array ['discount_details'] = $result_array;
			$response_array [CommonConstant::QUERY_PARAM_LIMIT] = $limit;
			$response_array [CommonConstant::QUERY_PARAM_COUNT] = DiscountDao::getCount ( $connection, $parameter_array ['group_id'] );
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "get group list failed", $e );
			}
			throw $e;
		}
	}
	
	/* get indiviual discount details */
	public function getDiscountDetails($discount_name, $group_id) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			if (UtilityMethods::isNotEmpty ( $discount_name )) {
				$count = DiscountDao::check_duplicate_discount_name_for_create ( $connection, $discount_name, $group_id );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Discount";
					return $response_array;
				}
			}
			
			$fields = implode ( ',', ListFieldConstant::$discount_fields );
			
			$response_array = DiscountDao::getDiscountDetails ( $connection, $discount_name, $group_id, $fields );
			
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create group failed", $e );
			}
			throw $e;
		}
	}
	
	/* insert group */
	public function createDiscount($parameter_array) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			$connection->beginTransaction ();
			if (UtilityMethods::isNotEmpty ( $parameter_array ['discount_name'] )) {
				$count = DiscountDao::check_duplicate_discount_name_for_create ( $connection, $parameter_array ['discount_name'], $parameter_array ['group_id'] );
				if ($count > 0) {
					DbConnector::rollbackTransaction ( $connection );
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Discount";
					return $response_array;
				}
			}
			
			if (isset ( $parameter_array ['provisionable'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['provisionable'] )) {
				$parameter_array ['provisionable'] = ($parameter_array ['provisionable'] == 'yes') ? 1 : 0;
			}
			
			$discount_id = DiscountDao::insertDiscountDetails ( $connection, $parameter_array );
			
			if (UtilityMethods::isNotEmpty ( $discount_id )) {
				$fields = implode ( ',', ListFieldConstant::$discount_fields );
				DbConnector::commitTransaction ( $connection );
				return DiscountDao::getDiscountDetails ( $connection, $parameter_array ['discount_name'], $parameter_array ['group_id'], $fields );
			}
		} catch ( \Exception $e ) {
			DbConnector::rollbackTransaction ( $connection );
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create Discount failed", $e );
			}
			throw $e;
		}
	}
	
	/* update discount details */
	public function updateDiscount($parameter_array) {
		try {
			$response_array = array ();
			$connection = DbConnector::getConnection ();
			$connection->beginTransaction ();
			if (UtilityMethods::isNotEmpty ( $parameter_array ['id'] )) {
				$count = DiscountDao::check_duplicate_discount_name_for_create ( $connection, $parameter_array ['id'], $parameter_array ['group_id'] );
				if ($count <= 0) {
					DbConnector::rollbackTransaction ( $connection );
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "discount not exist";
					return $response_array;
				}
			}
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['id'] )) {
				$count = DiscountDao::check_duplicate_discount_name_for_edit ( $connection, $parameter_array ['id'], $parameter_array ['discount_name'], $parameter_array ['group_id'] );
				if ($count > 0) {
					DbConnector::rollbackTransaction ( $connection );
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "discount name already exist";
					return $response_array;
				}
			}
			
			$discount_info = DiscountDao::getDiscountDetails ( $connection, $parameter_array ['id'], $parameter_array ['group_id'] );
			
			$primary_key_details = self::build_primarykey ( $discount_info );
			$update_details = self::build_updateArray ( $parameter_array );
			
			$response_array = Dao::updateBasedOnGivenKey ( $connection, 'discount', $primary_key_details, $update_details );
			DbConnector::commitTransaction ( $connection );
			return $response_array;
		} catch ( \Exception $e ) {
			DbConnector::rollbackTransaction ( $connection );
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "Update Discount failed", $e );
			}
			throw $e;
		}
	}
	
	/* delete discount */
	public function deleteDiscount($parameter_array) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['discount_name'] )) {
				$count = DiscountDao::check_duplicate_discount_name_for_create ( $connection, $parameter_array ['discount_name'], $parameter_array ['group_id'] );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Discount";
					return $response_array;
				}
			}
			$discount_info = DiscountDao::getDiscountDetails ( $connection, $parameter_array ['discount_name'], $parameter_array ['group_id'], "discount_id" );
			
			if(UtilityMethods::isNotEmpty($discount_info)){
				$dependency = ProductDao::check_discount_dependency($connection, $discount_info ['discount_id'], $parameter_array ['group_id'] );
				if($dependency > 0){
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::UNABLE_TO_DELETE_DISCOUNT_PRODUCT_EXIST;
					return $response_array;
				}
			}
			return DiscountDao::deleteDiscount ( $connection, $discount_info ['discount_id'], $parameter_array ['group_id'] );
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create Discount failed", $e );
			}
			throw $e;
		}
	}
	
	/* for update */
	public function build_primarykey($parameter_array) {
		$primary_details = array ();
		if (isset ( $parameter_array ['discount_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['discount_id'] )) {
			$primary_details ['discount_id'] = $parameter_array ['discount_id'];
		}
		return $primary_details;
	}
	public function build_updateArray($parameter_array) {
		$response_array = array ();
		if (isset ( $parameter_array ['discount_name'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['discount_name'] )) {
			$response_array ['discount_name'] = $parameter_array ['discount_name'];
		}
		
		if (isset ( $parameter_array ['discount_code'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['discount_code'] )) {
			$response_array ['discount_code'] = $parameter_array ['discount_code'];
		}
		if (isset ( $parameter_array ['discount_value'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['discount_value'] )) {
			$response_array ['discount_value'] = $parameter_array ['discount_value'];
		}
		if (isset ( $parameter_array ['provisionable'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['provisionable'] )) {
			$response_array ['is_valid'] = ($parameter_array ['provisionable'] == 'yes') ? 1 : 0;
		}
		if (isset ( $parameter_array ['group_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['group_id'] )) {
			$response_array ['group_id'] = $parameter_array ['group_id'];
		}
		return $response_array;
	}
}
