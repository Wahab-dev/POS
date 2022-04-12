<?php

namespace facade;

use utility\constant\CommonConstant;
use utility\constant\PageSizeConstant;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;
use utility\constant\ApiResponseConstant;
use utility\DbConnector;
use dao\PstnDao;
use dao\AccesslevelDao;
use dao\SupplierDao;
use dao\BankinfoDao;
use dao\SupplierorderDao;

class SupplierFacade extends Facade {
	public function __construct() {
		$this->_errorLogger ( __CLASS__ );
	}
	
	/* get all supplier Details */
	public function getAll($parameter_array) {
		$response_array = array ();
		try {
			$connection = DbConnector::getConnection ();
			$search_text = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SEARCH_KEYWORD, "" );
			$limit = UtilityMethods::getPageLimit ( $parameter_array, PageSizeConstant::SUPPLIER_PAGE_LIMIT );
			$offset = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_OFFSET, 0 );
			$order_by = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_BY, '' );
			$order_type = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_ORDER, '' );
			$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$supplier_fields ) );
			
			$result_array = SupplierDao::getAllSupplierDetails ( $connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $parameter_array ['group_id'] );
			$response_array [CommonConstant::QUERY_PARAM_OFFSET] = $offset;
			$response_array ['supplier_details'] = $result_array;
			$response_array [CommonConstant::QUERY_PARAM_LIMIT] = $limit;
			$response_array [CommonConstant::QUERY_PARAM_COUNT] = SupplierDao::getCount ( $connection, $parameter_array ['group_id'] );
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "get supplier list failed", $e );
			}
			throw $e;
		}
	}
	
	/* insert Supplier */
	public function createSupplier($parameter_array) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['supplier_name'] )) {
				$count = SupplierDao::check_duplicate_supplier_name_for_create ( $connection, $parameter_array ['supplier_name'], $parameter_array ['group_id'] );
				if ($count > 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "supplier already exist";
					return $response_array;
				}
			}
			
			/* for phone number */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['phone_number'] )) {
				$count = PstnDao::check_duplicate_pstn_for_create ( $connection, $parameter_array ['phone_number'], $parameter_array ['group_id'] );
				if ($count > 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "phone number already exist";
					return $response_array;
				} else {
					$parameter_array ['phone_number'] = PstnDao::create_pstn ( $connection, $parameter_array ['phone_number'], $parameter_array ['group_id'] );
				}
			}
			
			/* for alternate number */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['alternate_number'] )) {
				$count = PstnDao::check_duplicate_pstn_for_create ( $connection, $parameter_array ['alternate_number'], $parameter_array ['group_id'] );
				if ($count > 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "alternate number already exist";
					return $response_array;
				} else {
					$parameter_array ['alternate_number'] = PstnDao::create_pstn ( $connection, $parameter_array ['alternate_number'], $parameter_array ['group_id'] );
				}
			}
			
			if (isset ( $parameter_array ['bank_info'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['bank_info'] )) {
				$parameter_array ['bank_info'] = BankinfoDao::create_bank_info ( $connection, $parameter_array ['bank_info'] );
			}
			
			return SupplierDao::insertSupplierDetails ( $connection, $parameter_array );
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create supplier failed", $e );
			}
			throw $e;
		}
	}
	
	/* update supplier details */
	public function updateSupplierDetails($parameter_array) {
		try {
			$response_array = array ();
			$connection = DbConnector::getConnection ();
			
			/* check supplier exist */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['id'] )) {
				$count = SupplierDao::check_duplicate_supplier_name_for_create( $connection, $parameter_array ['id'], $parameter_array ['group_id'] );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "supplier not exist";
					return $response_array;
				}
			}
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['supplier_name'] )) {
				$count = SupplierDao::check_duplicate_supplier_name_for_edit( $connection, $parameter_array ['id'], $parameter_array ['supplier_name'], $parameter_array ['group_id'] );
				if ($count > 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "supplier already exist";
					return $response_array;
				}
			}
			
			$response_array = AccesslevelDao::update_access_level ( $connection, $parameter_array );
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create supplier failed", $e );
			}
			throw $e;
		}
	}
	
	/* get indiviual supplier details */
	public function getSupplierDetails($supplier_name, $group_id) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			if (UtilityMethods::isNotEmpty ( $supplier_name )) {
				$count = SupplierDao::check_duplicate_supplier_name_for_create ( $connection, $supplier_name, $group_id );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Supplier";
					return $response_array;
				}
			}
			
			$fields = ListFieldConstant::$supplier_fields;
			$fields [] = "bank_info";
			$fields [] = "supplier_id";
			$fields = implode ( $fields, "," );
			
			$response_array = SupplierDao::getSupplierDetails ( $connection, $supplier_name, $group_id, $fields );
			if (isset ( $response_array ['bank_info'] ) && UtilityMethods::isNotEmpty ( $response_array ['bank_info'] )) {
				$bank_details = BankinfoDao::get_bank_info_by_id ( $connection, $response_array ['bank_info'], $group_id );
				$response_array ['bank_info'] = $bank_details;
				$count = SupplierorderDao::get_count ( $connection, $group_id, $response_array ['supplier_id'] );
				if ($count > 0) {
					$response_array ['outstanding_balance'] = SupplierDao::get_outstanding_balance ( $connection, $response_array ['supplier_id'], $group_id );
				}
				unset ( $response_array ['supplier_id'] );
			}
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "update group failed", $e );
			}
			throw $e;
		}
	}
	
	/* for supplier deletion */
	public function deleteSupplier($supplier_name, $group_id) {
		try {
			
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			$exist_count = SupplierDao::check_duplicate_supplier_name_for_create ( $connection, $supplier_name, $group_id );
			if ($exist_count <= 0) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
				$response_array [CommonConstant::ERROR_MESSAGE] = 'Supplier';
				return $response_array;
			}
			
			$supplier_id = SupplierDao::getSupplierDetails ( $connection, $supplier_name, $group_id, 'supplier_id' );
			
			/* check for user */
			/*
			 * $dependency_count = UserDao::get_access_level_inuse ( $connection, $access_level_id, $group_id );
			 * if (UtilityMethods::isNotEmpty ( $dependency_count ) && $dependency_count > 0) {
			 * $response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::UNABLE_TO_DELETE_ACCESS_LEVEL_USER_EXIST;
			 * return $response_array;
			 * }
			 */
			
			return SupplierDao::deleteSupplier ( $connection, $supplier_id, $group_id );
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "get group list failed", $e );
			}
			throw $e;
		}
	}
}
