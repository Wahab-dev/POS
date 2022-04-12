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

class AccesslevelController extends CommonController {
	function __construct($facade) {
		parent::__construct ( $facade );
	}
	public function index() {
	}
	
	/* for getting access level list */
	public function get_all() {
		try {
			$this->_parameter_array ['field_list'] = ListFieldConstant::$access_level_fields;
			
			/* for validating list fields */
			$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
			$this->check_validator_response ( $validate );
			
			$this->_parameter_array['group_id'] = 1;
			$response_array = $this->_facade->getAllAccessLevelDetails ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* for get Indiviual access level details */
	public function get_access_level_details($access_level_name) {
		try {
			$response_array = array();
			if (UtilityMethods::isNotEmpty ( $access_level_name )) {
				$this->_parameter_array ['field_list'] = ListFieldConstant::$access_level_fields;
				$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
				$this->check_validator_response ( $validate );
				$response_data = $this->_facade->getAccessLevelDetails ( $access_level_name, 1 );
				$this->check_error ( $response_data );
				$response_array['access_level_details'] = $response_data;
				$this->dispatch_success ( $response_array );
			}else {
				$this->dispatch_failure(ApiResponseConstant::RESOURCE_NOT_EXISTS,"access level");
			}
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* Insert access level */
	public function create_access_level() {
		try {
			$response_array = array();
			/* for validating input fields */
			$validate = AccessLevelValidator::validate_access_level ( $this->_parameter_array, CommonConstant::ACTION_TYPE_ADD, $client = false );
			$this->check_validator_response ( $validate );
			$this->_parameter_array['group_id'] = 1;
			$this->_parameter_array['group_type'] = CommonConstant::GROUP_TYPE_TENANT;
			$response_data = $this->_facade->createAccessLevel ( $this->_parameter_array );
			$this->check_error ( $response_data );

			$response_array['access_level_details'] = $response_data;
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* update access level */
	public function update_access_level($access_level_name) {
		try {
			
			if (UtilityMethods::isNotEmpty ( $access_level_name )) {
				$this->_parameter_array ['id'] = $access_level_name;
			}
			
			$this->_parameter_array['group_id'] = 1;
			$this->_parameter_array['group_type'] = CommonConstant::GROUP_TYPE_TENANT;
			
			$validate = AccessLevelValidator::validate_access_level ( $this->_parameter_array, CommonConstant::ACTION_TYPE_EDIT, $client = false );
			
			$this->check_validator_response ( $validate );
			
			$response_array = $this->_facade->updateAccesslevel ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* delete group */
	public function delete_access_level($access_level_name) {
		try {
			
			$response_array = $this->_facade->deleteAccessLevel ( $access_level_name , 1 );
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
