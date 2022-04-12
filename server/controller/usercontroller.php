<?php

namespace controller;

use controller\CommonController;
use utility\constant\ListFieldConstant;
use utility\UtilityMethods;
use utility\constant\CommonConstant;
use utility\constant\ApiResponseConstant;
use validator\ListValidator;
use validator\GroupValidator;
use validator\AccessLevelValidator;
use validator\UserValidator;
use utility\SessionHelper;

class UserController extends CommonController {
	function __construct($facade) {
		parent::__construct ( $facade );
	}
	public function index() {
	}
	
	/* for getting user list */
	public function get_all() {
		try {
			$this->_parameter_array ['field_list'] = ListFieldConstant::$user_fields;
			
			/* for validating list fields */
			$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
			$this->check_validator_response ( $validate );
			
			$this->_parameter_array['group_id'] = 1;
			$response_array = $this->_facade->getAllUserDetails ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* for get Indiviual user details */
	public function get_user_details($user_name) {
		try {
			$response_array = array();
			if (UtilityMethods::isNotEmpty ( $user_name )) {
				$this->_parameter_array ['field_list'] = ListFieldConstant::$user_fields;
				$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
				$this->check_validator_response ( $validate );
				$response_data = $this->_facade->getUserDetails ( $user_name, 1 , $this->_parameter_array ['field_list']);
				$this->check_error ( $response_data );
				$response_array['user_details'] = $response_data;
				$this->dispatch_success ( $response_array );
			}else {
				$this->dispatch_failure(ApiResponseConstant::RESOURCE_NOT_EXISTS,"user");
			}
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* Insert user */
	public function create_user() {
		try {
			$response_array = array();
			/* for validating input fields */
			$validate = UserValidator::validate_user ( $this->_parameter_array, CommonConstant::ACTION_TYPE_ADD, $client = false );
			$this->check_validator_response ( $validate );
			
			$this->_parameter_array['group_id'] = 1;
			
			$response_data = $this->_facade->createUser ( $this->_parameter_array );
			$this->check_error ( $response_data );

			$response_array['user_details'] = $response_data;
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* update user */
	public function update_user($user_name) {
		try {
			
			if (UtilityMethods::isNotEmpty ( $user_name )) {
				$this->_parameter_array ['id'] = $user_name;
			}
			
			$this->_parameter_array['group_id'] = 1;
			$this->_parameter_array['group_type'] = CommonConstant::GROUP_TYPE_TENANT;
			
			$validate = UserValidator::validate_user( $this->_parameter_array, CommonConstant::ACTION_TYPE_EDIT, $client = false );
			
			$this->check_validator_response ( $validate );
			
			$response_array = $this->_facade->updateUser ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* delete group */
	public function delete_user($user_name) {
		try {
			if (UtilityMethods::isNotEmpty ( $user_name )) {
				$response_array = $this->_facade->deleteUser ( $user_name , 1 );
				$this->check_error ( $response_array );
			}else{
				$this->dispatch_failure ( ApiResponseConstant::RESOURCE_NOT_EXISTS, "user" );
			}
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	public function check_error($data) {
		if (isset ( $data [CommonConstant::ERROR_CODE] )) {
			if (isset ( $data [CommonConstant::ERROR_MESSAGE] )) {
				$this->dispatch_failure ( $data [CommonConstant::ERROR_CODE], $data [CommonConstant::ERROR_MESSAGE] );
			}
			$this->dispatch_failure ( $data [CommonConstant::ERROR_CODE] );
		}
	}
	
	/* authentication */
	public function authenticate(){
		try {
			if (UtilityMethods::isNotEmpty ( $this->_parameter_array['user_name'] ) 
					&& UtilityMethods::isNotEmpty($this->_parameter_array['password'])
					&& UtilityMethods::isNotEmpty($this->_parameter_array['group_name'])) {
				$response_array['login_details'] = $this->_facade->authenticate ( $this->_parameter_array['user_name'] , $this->_parameter_array['password'], $this->_parameter_array['group_name'] );
				$this->check_error ( $response_array );
			}else{
				$this->dispatch_failure ( ApiResponseConstant::RESOURCE_NOT_EXISTS, "user" );
			}
				
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* authentication */
	public function logout(){
		try {
			SessionHelper::session_destroy();
			$this->dispatch_success(array());
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
}
