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
use validator\OrderValidator;
use validator\OrderproductValidator;

class OrderController extends CommonController {
	function __construct($facade) {
		parent::__construct ( $facade );
	}
	public function index() {
	}
	
	/* for getting order list */
	public function get_all() {
		try {
			$this->_parameter_array ['field_list'] = ListFieldConstant::$order_fields;
			
			/* for validating list fields */
			$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
			$this->check_validator_response ( $validate );
			
			$this->_parameter_array ['group_id'] = 1;
			$response_array = $this->_facade->getAllOrderDetails ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* for get Indiviual order details */
	public function get_order_details($order_number) {
		try {
			$response_array = array ();
			if (UtilityMethods::isNotEmpty ( $order_number )) {
				$this->_parameter_array ['field_list'] = ListFieldConstant::$user_fields;
				$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
				$this->check_validator_response ( $validate );
				$response_data = $this->_facade->getOrderDetails ( $order_number, 1 );
				$this->check_error ( $response_data );
				$response_array ['order_details'] = $response_data;
				$this->dispatch_success ( $response_array );
			} else {
				$this->dispatch_failure ( ApiResponseConstant::RESOURCE_NOT_EXISTS, "order" );
			}
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* create order */
	public function create_order() {
		try {
			$response_array = array ();
			/* for validating input fields */
			$validate = OrderValidator::validate_order ( $this->_parameter_array, CommonConstant::ACTION_TYPE_ADD, $client = false );
			$this->check_validator_response ( $validate );
			
			/* for vaidation order products */
			if (isset ( $this->_parameter_array ['product_details'] )) {
				if (count ( $this->_parameter_array ['product_details'] ) > 0) {
					foreach ( $this->_parameter_array ['product_details'] as $product ) {
						$validate = OrderproductValidator::validate_order_product ( $product, CommonConstant::ACTION_TYPE_ADD, $client = false );
						$this->check_validator_response ( $validate );
					}
				} else {
					$this->dispatch_failure ( ApiResponseConstant::ORDER_PRODUCT_VALIDATION );
				}
			} else {
				$this->dispatch_failure ( ApiResponseConstant::ORDER_PRODUCT_VALIDATION );
			}
			
			$this->_parameter_array ['group_id'] = 1;
			$this->_parameter_array ['user_id'] = 1;
			
			$response_data = $this->_facade->createOrder ( $this->_parameter_array );
			$this->check_error ( $response_data );
			
			$response_array ['order_details'] = $response_data;
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* update order */
	public function update_order($order_number) {
		try {
			
			$this->_parameter_array ['group_id'] = 1;
			$this->_parameter_array ['order_number'] = $order_number;
			
			$validate = OrderValidator::validate_order ( $this->_parameter_array, CommonConstant::ACTION_TYPE_EDIT, $client = false );
			$this->check_validator_response ( $validate );
				
			/* for vaidation order products */
			if (isset ( $this->_parameter_array ['product_details'] )) {
				if (count ( $this->_parameter_array ['product_details'] ) > 0) {
					foreach ( $this->_parameter_array ['product_details'] as $product ) {
						$validate = OrderproductValidator::validate_order_product ( $product, CommonConstant::ACTION_TYPE_EDIT, $client = false );
						$this->check_validator_response ( $validate );
					}
				} else {
					$this->dispatch_failure ( ApiResponseConstant::ORDER_PRODUCT_VALIDATION );
				}
			} else {
				$this->dispatch_failure ( ApiResponseConstant::ORDER_PRODUCT_VALIDATION );
			}
			
			$response_array = $this->_facade->updateOrder ( $this->_parameter_array );
			$this->check_error ( $response_array );
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* Accept order */
	public function accept_order($order_number) {
		try {
			if (UtilityMethods::isNotEmpty ( $order_number )) {
				$response_array = $this->_facade->updateOrderStatus ( $order_number, 1, "confirmed" );
				$this->check_error ( $response_array );
			} else {
				$this->dispatch_failure ( ApiResponseConstant::RESOURCE_NOT_EXISTS, "order" );
			}
			
			$this->dispatch_success ( $response_array );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
	
	/* Reject order */
	public function reject_order($order_number) {
		try {
			if (UtilityMethods::isNotEmpty ( $order_number )) {
				$response_array = $this->_facade->updateOrderStatus ( $order_number, 1, "rejected" );
				$this->check_error ( $response_array );
			} else {
				$this->dispatch_failure ( ApiResponseConstant::RESOURCE_NOT_EXISTS, "order" );
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
}
