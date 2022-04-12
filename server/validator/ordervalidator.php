<?php

namespace validator;

use utility\constant\InputFieldLengthConstant;
use utility\constant\ApiResponseConstant;

/**
 * orderValidator - contains validation methods for order validations
 */
class OrderValidator {
	
	// Method for validating input parameters for order
	public static function validate_order($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules ( array (
				'order_number' => array (
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