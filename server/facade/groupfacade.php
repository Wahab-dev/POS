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

class GroupFacade extends Facade {
	public function __construct() {
		$this->_errorLogger ( __CLASS__ );
	}
	
	/* get group details */
	public function getAllGroupDetails($parameter_array) {
		$response_array = array ();
		try {
			$connection = DbConnector::getConnection ();
			$search_text = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SEARCH_KEYWORD, "" );
			$limit = UtilityMethods::getPageLimit ( $parameter_array, PageSizeConstant::GROUP_PAGE_LIMIT );
			$offset = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_OFFSET, 0 );
			$order_by = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_BY, '' );
			$order_type = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_ORDER, '' );
			$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$group_fields ) );
			
			$result_array = GroupDao::getAllGroupDetails ( $connection, $offset, $limit, $search_text, $fields, $order_by, $order_type );
			$response_array [CommonConstant::QUERY_PARAM_OFFSET] = $offset;
			$response_array ['group_details'] = $result_array;
			$response_array [CommonConstant::QUERY_PARAM_LIMIT] = $limit;
			$response_array [CommonConstant::QUERY_PARAM_COUNT] = GroupDao::getCount ( $connection );
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
	
	/* insert group */
	public function createGroup($parameter_array) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			$connection->beginTransaction ();
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['group_name'] )) {
				$count = GroupDao::check_duplicate_group_name_for_create ( $connection, $parameter_array ['group_name'] );
				if ($count > 0) {
					DbConnector::rollbackTransaction ( $connection );
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "group already exist";
					return $response_array;
				}
			}
			
			$parameter_array ['group_type'] = CommonConstant::GROUP_TYPE_TENANT;
			$parameter_array ['parent_group_id'] = 1;
			
			$group_id = GroupDao::insertGroupDetails ( $connection, $parameter_array );
			
			/* for creating default access level */
			$access_level_array = array ();
			$access_level_array ['access_level_name'] = CommonConstant::DEFAULT_ACCESS_LEVEL_NAME;
			$access_level_array ['group_id'] = $group_id;
			$access_level_array ['permissions'] = array (
					'super_admin' => CommonConstant::NO,
					'manager' => CommonConstant::YES,
					'admin' => CommonConstant::NO,
					'employee' => CommonConstant::NO,
					'customer' => CommonConstant::YES 
			);
			$access_level_id = AccesslevelDao::create_access_level ( $connection, $access_level_array, $group_id, $parameter_array ['group_type'] );
			
			$user_details_array = array ();
			$user_details_array ['group_id'] = $group_id;
			if (isset ( $parameter_array ['user_details'] ) && isset ( $parameter_array ['user_details'] ['first_name'] )) {
				$user_details_array ['first_name'] = $parameter_array ['user_details'] ['first_name'];
			}
			if (isset ( $parameter_array ['user_details'] ) && isset ( $parameter_array ['user_details'] ['last_name'] )) {
				$user_details_array ['last_name'] = $parameter_array ['user_details'] ['last_name'];
			}
			if (isset ( $parameter_array ['user_details'] ) && isset ( $parameter_array ['user_details'] ['user_name'] )) {
				$user_details_array ['user_name'] = $parameter_array ['user_details'] ['user_name'];
			}
			if (isset ( $parameter_array ['user_details'] ) && isset ( $parameter_array ['user_details'] ['password'] )) {
				$user_details_array ['password'] = $parameter_array ['user_details'] ['password'];
			}
			if (isset ( $parameter_array ['user_details'] ) && isset ( $parameter_array ['user_details'] ['address_line_1'] )) {
				$user_details_array ['address_line_1'] = $parameter_array ['user_details'] ['address_line_1'];
			}
			
			$user_details_array ['access_level_id'] = $access_level_id;
			
			/* for phone number */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['user_details']['phone_number'] )) {
				$count = PstnDao::check_duplicate_pstn_for_create ( $connection, $parameter_array ['user_details']['phone_number'], $group_id );
				if ($count > 0) {
					DbConnector::rollbackTransaction ( $connection );
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "phone number already exist";
					return $response_array;
				} else {
					$user_details_array ['phone_number'] = PstnDao::create_pstn ( $connection, $parameter_array ['user_details']['phone_number'], $group_id);
				}
			}
			
			$user_id = UserDao::create_user ( $connection, $user_details_array, $group_id );
			
			if (UtilityMethods::isNotEmpty ( $group_id )) {
				$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$group_fields ) );
				DbConnector::commitTransaction ( $connection );
				return GroupDao::getGroupDetailsById ( $connection, $group_id, $fields );
			}
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
	
	/* update group details */
	public function updateGroup($parameter_array) {
		try {
			$response_array = array ();
			$connection = DbConnector::getConnection ();
			
			/* check group exist */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['id'] )) {
				$count = GroupDao::check_duplicate_group_name_for_create ( $connection, $parameter_array ['id'] );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "group not exist";
					return $response_array;
				}
			}
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['group_name'] )) {
				$count = GroupDao::check_duplicate_group_name_for_edit ( $connection, $parameter_array ['id'], $parameter_array ['group_name'] );
				
				if ($count > 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "group name already exist";
					return $response_array;
				}
			}
			
			$group_info = GroupDao::getGroupDetails ( $connection, $parameter_array ['id'] );
			$primary_key_details = self::build_primarykey ( $group_info );
			$update_details = self::build_updateArray ( $parameter_array );
			
			$response_array = Dao::updateBasedOnGivenKey ( $connection, 'group', $primary_key_details, $update_details );
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
	
	/* get indiviual group details */
	public function getGroupDetails($group_name, $fields = '*') {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			if (UtilityMethods::isNotEmpty ( $group_name )) {
				$count = GroupDao::check_duplicate_group_name_for_create ( $connection, $group_name );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "group not exist";
					return $response_array;
				}
			}
			
			$response_array = GroupDao::getGroupDetails ( $connection, $group_name, $fields );
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
	
	/* for group deletion */
	public function deleteGroup($group_name) {
		try {
			
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			$exist_count = GroupDao::check_duplicate_group_name_for_create ( $connection, $group_name );
			if ($exist_count <= 0) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
				$response_array [CommonConstant::ERROR_MESSAGE] = 'Group';
				return $response_array;
			}
			
			$group_info = GroupDao::getGroupDetails ( $connection, $group_name );
			
			/* check for tenant */
			$tenant_details = TenantDao::get_tenant_mapping_details ( $connection, $group_info ['group_id'] );
			if (! empty ( $tenant_details )) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::UNABLE_TO_DELETE_GROUP_TENANT_EXIST;
				return $response_array;
			}
			
			return GroupDao::deleteGroupDetails ( $connection, $group_info ['group_id'] );
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "get group list failed", $e );
			}
			throw $e;
		}
	}
	
	/* for update */
	public function build_primarykey($parameter_array) {
		$primary_details = array ();
		if (isset ( $parameter_array ['group_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['group_id'] )) {
			$primary_details ['group_id'] = $parameter_array ['group_id'];
		}
		return $primary_details;
	}
	public function build_updateArray($parameter_array) {
		$response_array = array ();
		
		if (isset ( $parameter_array ['group_name'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['group_name'] )) {
			$response_array ['group_name'] = $parameter_array ['group_name'];
		}
		
		if (isset ( $parameter_array ['email_address'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['email_address'] )) {
			$response_array ['group_email'] = $parameter_array ['email_address'];
		}
		if (isset ( $parameter_array ['group_attention'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['group_attention'] )) {
			$response_array ['group_attn'] = $parameter_array ['group_attention'];
		}
		if (isset ( $parameter_array ['address_line_1'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['address_line_1'] )) {
			$response_array ['group_address_line_1'] = $parameter_array ['address_line_1'];
		}
		if (isset ( $parameter_array ['address_line_2'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['address_line_2'] )) {
			$response_array ['group_address_line_2'] = $parameter_array ['address_line_2'];
		}
		return $response_array;
	}
}
