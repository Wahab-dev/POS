<?php

namespace facade;

use utility\constant\CommonConstant;
use utility\constant\PageSizeConstant;
use dao\GroupDao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;
use utility\constant\ApiResponseConstant;
use utility\DbConnector;
use dao\PstnDao;
use dao\Dao;
use dao\TenantDao;
use dao\AccesslevelDao;
use dao\UserDao;

class AccesslevelFacade extends Facade {
	public function __construct() {
		$this->_errorLogger ( __CLASS__ );
	}
	
	/* get Access level Details */
	public function getAllAccessLevelDetails($parameter_array) {
		$response_array = array ();
		try {
			$connection = DbConnector::getConnection ();
			$search_text = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SEARCH_KEYWORD, "" );
			$limit = UtilityMethods::getPageLimit ( $parameter_array, PageSizeConstant::ACCESSLEVEL_PAGE_LIMIT );
			$offset = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_OFFSET, 0 );
			$order_by = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_BY, '' );
			$order_type = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_ORDER, '' );
			$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$access_level_fields ) );
			
			$result_array = AccesslevelDao::get_all_access_level( $connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $parameter_array['group_id']);
			$response_array [CommonConstant::QUERY_PARAM_OFFSET] = $offset;
			$response_array ['access_level_details'] = $result_array;
			$response_array [CommonConstant::QUERY_PARAM_LIMIT] = $limit;
			$response_array [CommonConstant::QUERY_PARAM_COUNT] = AccesslevelDao::getCount ( $connection, $parameter_array['group_id'] );
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "get access level list failed", $e );
			}
			throw $e;
		}
	}
	
	/* insert Access level */
	public function createAccessLevel($parameter_array) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			$connection->beginTransaction ();
			if (UtilityMethods::isNotEmpty ( $parameter_array ['access_level_name'] )) {
				$count = AccesslevelDao::check_duplicate_acess_level_name_for_create( $connection, $parameter_array ['access_level_name'], $parameter_array['group_id']);
				if ($count > 0) {
					DbConnector::rollbackTransaction ( $connection );
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "access level already exist";
					return $response_array;
				}
			}
			
			$parameter_array['permissions'] = self::build_permission_parameter($parameter_array['permissions'], $parameter_array['group_type']);
		
			$access_level_id = AccesslevelDao::create_access_level($connection, $parameter_array, $parameter_array['group_id'], $parameter_array['group_type']);
			
			if (UtilityMethods::isNotEmpty ( $access_level_id )) {
				DbConnector::commitTransaction ( $connection );
				$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$access_level_fields ) );
				return AccesslevelDao::get_access_level_details( $connection, $parameter_array['access_level_name'], $parameter_array['group_id'], $fields );
			}
		} catch ( \Exception $e ) {
			DbConnector::rollbackTransaction ( $connection );
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create access level failed", $e );
			}
			throw $e;
		}
	}
	
	/* update access level details */
	public function updateAccesslevel($parameter_array) {
		try {
			$response_array = array ();
			$connection = DbConnector::getConnection ();
			$connection->beginTransaction ();
			/* check access level exist */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['id'] ) ) {
				$count = AccesslevelDao::check_duplicate_acess_level_name_for_create( $connection, $parameter_array ['id'], $parameter_array['group_id'] );
				if ($count <= 0) {
					DbConnector::rollbackTransaction ( $connection );
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "access level not exist";
					return $response_array;
				}
			}	
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['access_level_name'] )) {
				$count = AccesslevelDao::check_duplicate_acess_level_name_for_edit( $connection, $parameter_array ['id'], $parameter_array ['access_level_name'], $parameter_array['group_id'] );
				if ($count > 0) {
					DbConnector::rollbackTransaction ( $connection );
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "access level already exist";
					return $response_array;
				}
			}
			
			$parameter_array['permissions'] = self::build_permission_parameter($parameter_array['permissions'], $parameter_array['group_type']);
			
			$access_level_id = AccesslevelDao::get_access_level_details( $connection, $parameter_array ['id'], $parameter_array['group_id'], 'access_level_id');
			$parameter_array['access_level_id'] = $access_level_id['access_level_id']; 
			
			$response_array = AccesslevelDao::update_access_level( $connection, $parameter_array);
			DbConnector::commitTransaction ( $connection );
			return $response_array;
		} catch ( \Exception $e ) {
			DbConnector::rollbackTransaction ( $connection );
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create group failed", $e );
			}
			throw $e;
		}
	}
	
	/* get indiviual access level details */
	public function getAccessLevelDetails($access_level_name, $group_id ) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			if (UtilityMethods::isNotEmpty ( $access_level_name ) ) {
				$count = AccesslevelDao::check_duplicate_acess_level_name_for_create( $connection, $access_level_name, $group_id);
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Access level";
					return $response_array;
				}
			}
			
			$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$access_level_fields ) );
			$response_array = AccesslevelDao::get_access_level_details( $connection, $access_level_name, $group_id, $fields);
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
	
	/* for access level deletion */
	public function deleteAccessLevel($access_level_name, $group_id) {
		try {
			
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			$exist_count = AccesslevelDao::check_duplicate_acess_level_name_for_create ( $connection, $access_level_name, $group_id );
			if ($exist_count <= 0) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
				$response_array [CommonConstant::ERROR_MESSAGE] = 'Access level';
				return $response_array;
			}
			
			$access_level_id = AccesslevelDao::get_access_level_details($connection, $access_level_name, $group_id, 'access_level_id');
			
			/* check for user */
			$dependency_count = UserDao::get_access_level_inuse($connection, $access_level_id, $group_id);
			if (UtilityMethods::isNotEmpty($dependency_count) && $dependency_count > 0) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::UNABLE_TO_DELETE_ACCESS_LEVEL_USER_EXIST;
				return $response_array;
			}
			
			return AccesslevelDao::deleteAccessLevel($connection,$access_level_name,$group_id);
			
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "get group list failed", $e );
			}
			throw $e;
		}
	}
	
	/* build permission array */
	// method to check permission level
	private function build_permission_parameter($permissions,$group_type){
		$connection = DbConnector::getConnection();
		$permissions_array=array();
		
		$permissions_array['super_admin'] = in_array('super_admin', $permissions) ? CommonConstant::YES : CommonConstant::NO;
		$permissions_array['admin'] = in_array('admin', $permissions) ? CommonConstant::YES : CommonConstant::NO;
		$permissions_array['manager'] = in_array('manager', $permissions) ? CommonConstant::YES : CommonConstant::NO;
		$permissions_array['employee'] = in_array('employee', $permissions) ? CommonConstant::YES : CommonConstant::NO;
		$permissions_array['customer'] = in_array('customer', $permissions) ? CommonConstant::YES : CommonConstant::NO;
		
		
		return $permissions_array;
	}
	
}
