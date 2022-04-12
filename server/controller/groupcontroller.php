<?php

namespace controller;

use controller\CommonController;
use utility\constant\ListFieldConstant;
use utility\UtilityMethods;
use utility\constant\CommonConstant;
use utility\constant\ApiResponseConstant;
use validator\ListValidator;
use validator\GroupValidator;

class GroupController extends CommonController {
	function __construct($facade) {
		parent::__construct ( $facade );
	}
	public function index() {
	}
	
	/* for getting group list */
	public function get_all() {
		try {
			$this->_parameter_array ['field_list'] = ListFieldConstant::$group_fields;
			
			/* for validating list fields */
			$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
			$this->check_validator_response ( $validate );
			$response_array = $this->_facade->getAllGroupDetails ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$response_data = array ();
			$response_data ['group_details'] = $response_array ['group_details'];
			$response_data ['count'] = $response_array ['count'];
			$response_data ['limit'] = $response_array ['limit'];
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* for get Indiviual group details */
	public function get_group_details($group_name) {
		try {
			$response_array = array();
			if (UtilityMethods::isNotEmpty ( $group_name )) {
				$this->_parameter_array ['field_list'] = ListFieldConstant::$group_fields;
				$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
				$this->check_validator_response ( $validate );
				$response_data = $this->_facade->getGroupDetails ( $group_name, implode ( ',', ListFieldConstant::$group_fields ) );
				$this->check_error ( $response_data );
				
				$response_array['group_details'] = $response_data;
				$this->dispatch_success ( $response_array );
			}else {
				$this->dispatch_failure(ApiResponseConstant::RESOURCE_NOT_EXISTS,"group");
			}
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* Insert group */
	public function create_group() {
		try {
			/* for validating list fields */
			$validate = GroupValidator::validate_group ( $this->_parameter_array, CommonConstant::ACTION_TYPE_ADD, $client = false );
			$this->check_validator_response ( $validate );
			
			if(UtilityMethods::isEmpty($this->_parameter_array['user_details']) && UtilityMethods::isEmpty($this->_parameter_array['user_details']['user_name'])
					&& UtilityMethods::isEmpty($this->_parameter_array['user_details']['first_name']) && UtilityMethods::isEmpty($this->_parameter_array['user_details']['phone_number']) && 
					UtilityMethods::isEmpty($this->_parameter_array['user_details']['address_line_1']) && UtilityMethods::isEmpty($this->_parameter_array['user_details']['password'])){
				$this->dispatch_failure(ApiResponseConstant::USER_DETAILS_REQUIRED);
			}
			
			$response_array = $this->_facade->createGroup ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$response_data = array ();
			$response_data ['group_details'] = $response_array;
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* update group */
	public function update_group($group_name) {
		try {
			
			if (UtilityMethods::isNotEmpty ( $group_name )) {
				$this->_parameter_array ['id'] = $group_name;
			}
			
			$validate = GroupValidator::validate_group ( $this->_parameter_array, CommonConstant::ACTION_TYPE_EDIT, $client = false );
			
			$this->check_validator_response ( $validate );
			
			$response_array = $this->_facade->updateGroup ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$response_data = array ();
			
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* delete group */
	public function delete_group($group_name) {
		try {
			
			$response_array = $this->_facade->deleteGroup ( $group_name );
			$this->check_error ( $response_array );
			
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
}
