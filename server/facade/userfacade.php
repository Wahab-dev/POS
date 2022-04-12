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
use dao\UserDao;
use dao\AccesslevelDao;

class UserFacade extends Facade {
	public function __construct() {
		$this->_errorLogger ( __CLASS__ );
	}
	
	/* get user details */
	public function getAllUserDetails($parameter_array) {
		$response_array = array ();
		try {
			$connection = DbConnector::getConnection ();
			$search_text = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SEARCH_KEYWORD, "" );
			$limit = UtilityMethods::getPageLimit ( $parameter_array, PageSizeConstant::USER_PAGE_LIMIT );
			$offset = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_OFFSET, 0 );
			$order_by = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_BY, '' );
			$order_type = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_ORDER, '' );
			$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$user_fields ) );
			
			$result_array = UserDao::getAllUserDetails ( $connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $parameter_array ['group_id'] );
			$response_array [CommonConstant::QUERY_PARAM_OFFSET] = $offset;
			$response_array ['user_details'] = $result_array;
			$response_array [CommonConstant::QUERY_PARAM_LIMIT] = $limit;
			$response_array [CommonConstant::QUERY_PARAM_COUNT] = UserDao::get_count ( $connection, $parameter_array ['group_id'] );
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
	
	/* insert user */
	public function createUser($parameter_array) {
		try {
			$connection = DbConnector::getConnection ();
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['user_name'] )) {
				$count = UserDao::check_duplicate_user_name_for_create ( $connection, $parameter_array ['user_name'], $parameter_array ['group_id'] );
				if ($count > 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "user";
					return $response_array;
				}
			}
			
			/* access level validation */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['access_level_name'] )) {
				$count = AccesslevelDao::check_duplicate_acess_level_name_for_create ( $connection, $parameter_array ['access_level_name'], $parameter_array ['group_id'] );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "access level";
					return $response_array;
				} else {
					$access_level_id = AccesslevelDao::get_access_level_details ( $connection, $parameter_array ['access_level_name'], $parameter_array ['group_id'], 'access_level_id' );
					$parameter_array ['access_level_id'] = $access_level_id ['access_level_id'];
					unset ( $parameter_array ['access_level_name'] );
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
			
			$response_data = UserDao::create_user ( $connection, $parameter_array, $parameter_array ['group_id'] );
			
			if (UtilityMethods::isNotEmpty ( $response_data )) {
				$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$user_fields ) );
				return UserDao::getUserDetails ( $connection, $parameter_array ['user_name'], $parameter_array ['group_id'], $fields );
			}
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create group failed", $e );
			}
			throw $e;
		}
	}
	
	/* update user details */
	public function updateUser($parameter_array) {
		try {
			$response_array = array ();
			$connection = DbConnector::getConnection ();
			
			/* check group exist */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['id'] )) {
				$count = UserDao::check_duplicate_user_name_for_create ( $connection, $parameter_array ['id'], $parameter_array ['group_id'] );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "user";
					return $response_array;
				}
			}
			
			$user_info = UserDao::getUserDetails ( $connection, $parameter_array ['id'], $parameter_array ['group_id'] );
			
			if (isset($parameter_array ['user_name']) && UtilityMethods::isNotEmpty ( $parameter_array ['user_name'] )) {
				$count = UserDao::check_duplicate_user_name_for_edit ( $connection, $parameter_array ['id'], $parameter_array ['user_name'], $parameter_array ['group_id'] );
				
				if ($count > 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "user name already exist";
					return $response_array;
				}
			}
			
			if (UtilityMethods::isNotEmpty ( $user_info ['is_active'] ) && $user_info['is_active'] == 2) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::USER_INACTIVE;
				return $response_array;
			}
			
			if (UtilityMethods::isNotEmpty ( $user_info ['is_active'] ) && $user_info['is_active'] == 3) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::USER_DELETED;
					return $response_array;
			}
			
			/* access level validation */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['access_level_name'] )) {
				$count = AccesslevelDao::check_duplicate_acess_level_name_for_create ( $connection, $parameter_array ['access_level_name'], $parameter_array ['group_id'] );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "access level";
					return $response_array;
				} else {
					$access_level_id = AccesslevelDao::get_access_level_details ( $connection, $parameter_array ['access_level_name'], $parameter_array ['group_id'], 'access_level_id' );
					$parameter_array ['access_level_id'] = $access_level_id ['access_level_id'];
					unset ( $parameter_array ['access_level_name'] );
				}
			}
						
			$primary_key_details = self::build_primarykey ( $user_info );
			$update_details = self::build_updateArray ( $parameter_array );
			
			$response_array = Dao::updateBasedOnGivenKey ( $connection, 'user', $primary_key_details, $update_details );
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
	
	/* get indiviual user details */
	public function getUserDetails($user_name, $group_id) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			if (UtilityMethods::isNotEmpty ( $user_name )) {
				$count = UserDao::check_duplicate_user_name_for_create ( $connection, $user_name, $group_id );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "user";
					return $response_array;
				}
			}
			
			$fields = implode ( ',', ListFieldConstant::$user_fields );
			
			$response_array = UserDao::getUserDetails ( $connection, $user_name, $group_id, $fields );
			
			if (UtilityMethods::isNotEmpty ( $response_array ['is_active'] ) && $user_info['is_active'] == 2) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::USER_INACTIVE;
				return $response_array;
			}
				
			if (UtilityMethods::isNotEmpty ( $response_array ['is_active'] ) && $user_info['is_active'] == 3) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::USER_DELETED;
				return $response_array;
			}
			
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "fetch user details failed", $e );
			}
			throw $e;
		}
	}
	
	/* for user deletion */
	public function deleteUser($user_name, $group_id) {
		try {
			
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			$exist_count = UserDao::check_duplicate_user_name_for_create ( $connection, $user_name, $group_id );
			
			if ($exist_count <= 0) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
				$response_array [CommonConstant::ERROR_MESSAGE] = 'User';
				return $response_array;
			}
			
			$response_array = UserDao::getUserDetails($connection, $user_name, $group_id);
			
			if (UtilityMethods::isNotEmpty ( $response_array ['is_active'] ) && $user_info['is_active'] == 2) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::USER_INACTIVE;
				return $response_array;
			}
			
			if (UtilityMethods::isNotEmpty ( $response_array ['is_active'] ) && $user_info['is_active'] == 3) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::USER_DELETED;
				return $response_array;
			}
			
			return UserDao::deleteUserDetails ( $connection, $user_name, $group_id );
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "delete user failed", $e );
			}
			throw $e;
		}
	}
	
	/* for update */
	public function build_primarykey($parameter_array) {
		$primary_details = array ();
		if (isset ( $parameter_array ['user_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['user_id'] )) {
			$primary_details ['user_id'] = $parameter_array ['user_id'];
		}
		
		if (isset ( $parameter_array ['group_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['group_id'] )) {
			$primary_details ['group_id'] = $parameter_array ['group_id'];
		}
		return $primary_details;
	}
	public function build_updateArray($parameter_array) {
		$response_array = array ();
		
		if (isset ( $parameter_array ['user_name'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['user_name'] )) {
			$response_array ['user_name'] = $parameter_array ['user_name'];
		}
		
		if (isset ( $parameter_array ['first_name'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['first_name'] )) {
			$response_array ['first_name'] = $parameter_array ['first_name'];
		}
		
		if (isset ( $parameter_array ['last_name'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['last_name'] )) {
			$response_array ['last_name'] = $parameter_array ['last_name'];
		}
		
		if (isset ( $parameter_array ['phone_number'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['phone_number'] )) {
			$response_array ['user_phno'] = $parameter_array ['phone_number'];
		}
		
		if (isset ( $parameter_array ['alternate_number'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['alternate_number'] )) {
			$response_array ['user_alt_no'] = $parameter_array ['alternate_number'];
		}
		
		if (isset ( $parameter_array ['password'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['password'] )) {
			$response_array ['password'] = $parameter_array ['password'];
		}
		if (isset ( $parameter_array ['access_level_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['access_level_id'] )) {
			$response_array ['access_level_id'] = $parameter_array ['access_level_id'];
		}
		if (isset ( $parameter_array ['address_line_1'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['address_line_1'] )) {
			$response_array ['user_address_line_1'] = $parameter_array ['address_line_1'];
		}
		if (isset ( $parameter_array ['address_line_2'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['address_line_2'] )) {
			$response_array ['user_address_line_2'] = $parameter_array ['address_line_2'];
		}
		
		if (isset ( $parameter_array ['email_address'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['email_address'] )) {
			$response_array ['user_email'] = $parameter_array ['email_address'];
		}
		return $response_array;
	}
	
	/* authenticate user */
	public function authenticate($user_name, $password, $group_name) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			$exist_count = GroupDao::check_duplicate_group_name_for_create($connection, $group_name);
			if($exist_count <= 0){
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
				$response_array [CommonConstant::ERROR_MESSAGE] = 'Group';
				return $response_array;
			}
			
			$group_id = GroupDao::getGroupDetails($connection, $group_name, 'group_id');
			$group_id = $group_id['group_id'];
			
			$exist_count = UserDao::check_duplicate_user_name_for_create ( $connection, $user_name, $group_id );
			if ($exist_count <= 0) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
				$response_array [CommonConstant::ERROR_MESSAGE] = 'User';
				return $response_array;
			}
			
			$count = UserDao::authenticate_user($connection, $user_name, $password, $group_id);
			if($count <= 0){
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::AUTHENTICATION_FAILED;
				return $response_array;
			}
			
			$group_details = GroupDao::getGroupDetailsById($connection, $group_id);
			$user_details = UserDao::getUserDetails($connection, $user_name, $group_id, '*');
			
			if (UtilityMethods::isNotEmpty ( $user_details ['is_active'] ) && $user_details['is_active'] == 2) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::USER_INACTIVE;
				return $response_array;
			}
				
			if (UtilityMethods::isNotEmpty ( $user_details ['is_active'] ) && $user_details['is_active'] == 3) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::USER_DELETED;
				return $response_array;
			}
			
			$response_array = self::_setSessionDetails($group_details, $user_details);
			
			return $response_array;
		}catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "user authentication failed", $e );
			}
			throw $e;
		}
	}
}
