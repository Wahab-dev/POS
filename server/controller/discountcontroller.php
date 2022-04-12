<?php

namespace controller;

use controller\CommonController;
use utility\constant\ListFieldConstant;
use utility\UtilityMethods;
use utility\constant\CommonConstant;
use utility\constant\ApiResponseConstant;
use validator\ListValidator;
use validator\DiscountValidator;

class DiscountController extends CommonController {
	function __construct($facade) {
		parent::__construct ( $facade );
	}
	public function index() {
	}
	
	/* for getting Discount list */
	public function get_all() {
		try {
			$this->_parameter_array ['field_list'] = ListFieldConstant::$discount_fields;
			
			/* for validating list fields */
			$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
			$this->check_validator_response ( $validate );
			$this->_parameter_array['group_id']=1; //hard coding value 
			$response_array = $this->_facade->getAllDiscountDetails ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$response_data = array ();
			$response_data ['discount_details'] = $response_array ['discount_details'];
			$response_data ['count'] = $response_array ['count'];
			$response_data ['limit'] = $response_array ['limit'];
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* for get Indiviual discount details */
	public function get_discount_details($discount_name) {
		try {
			if (UtilityMethods::isNotEmpty ( $discount_name )) {
				$this->_parameter_array ['field_list'] = ListFieldConstant::$discount_fields;
				$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
				$this->check_validator_response ( $validate ); 
				$response_array = $this->_facade->getDiscountDetails ( $discount_name, 1, implode ( ',', ListFieldConstant::$discount_fields ) );
				$this->check_error ( $response_array );
				
				$response_data = array ();
				$response_data ['discount_details'] = $response_array;
				$this->dispatch_success ( $response_data );
			}else {
				$this->dispatch_failure(ApiResponseConstant::RESOURCE_NOT_EXISTS,"discount");
			}
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* Insert discount  */
	public function create_discount() {
		try {
			/* for validating list fields */
			$validate = DiscountValidator::validate_discount ( $this->_parameter_array, CommonConstant::ACTION_TYPE_ADD, $client = false );
			$this->check_validator_response ( $validate );
			
			$this->_parameter_array['group_id'] = 1;

 			$response_array = $this->_facade->createDiscount ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$response_data = array ();
			$response_data ['discount_details'] = $response_array;
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* update group */
	public function update_discount($discount_name) {
		try {
			
			if (UtilityMethods::isNotEmpty ( $discount_name )) {
				$this->_parameter_array ['id'] = $discount_name;
			}
			$validate = DiscountValidator::validate_discount ( $this->_parameter_array, CommonConstant::ACTION_TYPE_EDIT, $client = false );
			
			$this->check_validator_response ( $validate );
			$this->_parameter_array['group_id'] = 1; //hard coding group_id
			$response_array = $this->_facade->updateDiscount ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$response_data = array ();
			
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	//delete discount 
	public function delete_discount($discount_name) {
		try {
			if (UtilityMethods::isNotEmpty ( $discount_name )) {		
				$this->_parameter_array['group_id'] = 1;
				$this->_parameter_array['discount_name'] = $discount_name;
				$response_array = $this->_facade->deleteDiscount ( $this->_parameter_array );
				$this->check_error ( $response_array );
				
				$this->dispatch_success ( $response_array );
			}else {
				$this->dispatch_failure(ApiResponseConstant::RESOURCE_NOT_EXISTS,"discount");
			}
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
