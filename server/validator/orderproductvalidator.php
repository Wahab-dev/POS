<?php

namespace validator;

use utility\constant\InputFieldLengthConstant;
use utility\constant\ApiResponseConstant;

/**
 * orderproductValidator - contains validation methods for order product validations
 */
class OrderproductValidator {
	
	// Method for validating input parameters for order product
	public static function validate_order_product($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules ( array (
				'product_name' => array (
						'add' => 'required',
						'edit' => 'required',
						'common' => 'empty' 
				), 
				'quantity' => array (
						'add' => 'required',
						'edit' => 'required',
						'common' => 'empty'
				)
		), $action );
		
		$validator->set_error_codes ( array (
				'empty' => array (
						'*' => ApiResponseConstant::MISSING_REQUIRED_PARAMETERS 
				)
		) );
		return $validator->run ( $parameter_array, FALSE );
	}
}