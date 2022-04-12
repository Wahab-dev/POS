<?php

namespace controller;

use controller\CommonController;
use utility\constant\ListFieldConstant;
use utility\UtilityMethods;
use utility\constant\CommonConstant;
use utility\constant\ApiResponseConstant;
use validator\ListValidator;
use validator\ProductValidator;

class ProductController extends CommonController {
	function __construct($facade) {
		parent::__construct ( $facade );
	}
	public function index() {
	}
	
	/* get product list */
	public function get_all() {
		try {
			$this->_parameter_array ['field_list'] = ListFieldConstant::$product_fields;
			
			/* for validating list fields */
			$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
			$this->check_validator_response ( $validate );
			$this->_parameter_array['group_id']=1; //hard coding value 
			$response_array = $this->_facade->getAllProductDetails ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$response_data = array ();
			$response_data ['product_details'] = $response_array ['product_details'];
			$response_data ['count'] = $response_array ['count'];
			$response_data ['limit'] = $response_array ['limit'];
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* for get Indiviual price details */
	public function get_product_details($product_name) {
		try {
			if (UtilityMethods::isNotEmpty ($product_name )) {
				$this->_parameter_array ['field_list'] = ListFieldConstant::$product_fields;
				$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
				$this->check_validator_response ( $validate ); 
				$group_id =1;
	 			$response_array = $this->_facade->getProductDetails ( $product_name, $group_id, implode ( ',', ListFieldConstant::$product_fields ) );
				$this->check_error ( $response_array );
				
				$response_data = array ();
				$response_data ['product_details'] = $response_array;
				$this->dispatch_success ( $response_data );
			}else {
				$this->dispatch_failure(ApiResponseConstant::RESOURCE_NOT_EXISTS,"product");
			}
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* for get Indiviual price details */
	public function get_price_details($product_name) {
		try {
			if (UtilityMethods::isNotEmpty ($product_name )) {
				$this->_parameter_array ['field_list'] = ListFieldConstant::$price_fields;
				$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
				$this->check_validator_response ( $validate );
				$group_id=1;
				$response_array = $this->_facade->getPriceDetails ( $product_name, $group_id, implode ( ',', ListFieldConstant::$price_fields ) );
				$this->check_error ( $response_array );
	
				$response_data = array ();
				$response_data ['price_details'] = $response_array;
				$this->dispatch_success ( $response_data );
			}else {
				$this->dispatch_failure(ApiResponseConstant::RESOURCE_NOT_EXISTS,"product");
			}
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* Insert Product  */
	public function create_product() {
		try {
		
			/* for validating list fields */
			$validate = ProductValidator::validate_product ( $this->_parameter_array, CommonConstant::ACTION_TYPE_ADD, $client = false );
			
			$this->check_validator_response ( $validate );
			$this->_parameter_array['group_id'] = 1;
			
 			$response_array = $this->_facade->createProduct ( $this->_parameter_array );
 			$this->check_error ( $response_array );
			
			$response_data = array ();
			$response_data ['product_details'] = $response_array;
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	
	/* update group */
	public function update_product($product_name) {
		try {
			
			if (UtilityMethods::isNotEmpty ( $product_name )) {
				$this->_parameter_array ['id'] = $product_name;
			}
			$validate = ProductValidator::validate_product( $this->_parameter_array, CommonConstant::ACTION_TYPE_EDIT, $client = false );
			
			$this->check_validator_response ( $validate );
			$this->_parameter_array['group_id'] = 1; //hard coding group_id
			$response_array = $this->_facade->updateProduct ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$response_data = array ();
			
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	

	/* hold product  */
	public function hold($product_name) {
		try {
			if (UtilityMethods::isNotEmpty ( $product_name )) {
				$this->_parameter_array ['id'] = $product_name;
			}
			$validate = ProductValidator::validate_product( $this->_parameter_array, CommonConstant::ACTION_TYPE_EDIT, $client = false );
			$this->check_validator_response ( $validate );
			$this->_parameter_array['group_id'] = 1; //hard coding group_id
			$this->_parameter_array['provisionable']='no'; 
			$response_array = $this->_facade->updateProduct ( $this->_parameter_array );
			$this->check_error ( $response_array );
				
			$response_data = array ();
				
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* unhold product  */
	public function unhold($product_name) {
		try {
	
			if (UtilityMethods::isNotEmpty ( $product_name )) {
				$this->_parameter_array ['id'] = $product_name;
			}
			$validate = ProductValidator::validate_product( $this->_parameter_array, CommonConstant::ACTION_TYPE_EDIT, $client = false );
			$this->check_validator_response ( $validate );
			$this->_parameter_array['group_id'] = 1;
			$this->_parameter_array['provisionable']='yes' ; 
			$response_array = $this->_facade->updateProduct ( $this->_parameter_array );
			$this->check_error ( $response_array );
	
			$response_data = array ();
	
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	//delete product
	public function delete_product($product_name) {
		try {
			if (UtilityMethods::isNotEmpty ( $product_name )) {
				$this->_parameter_array['group_id'] = 1;
				$this->_parameter_array['product_name'] = $product_name;
				$this->_parameter_array['hidden'] = true;
				$response_array = $this->_facade->deleteProduct ( $this->_parameter_array );
				$this->check_error ( $response_array );
	
				$this->dispatch_success ( $response_array );
			}else {
				$this->dispatch_failure(ApiResponseConstant::RESOURCE_NOT_EXISTS,"product");
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
