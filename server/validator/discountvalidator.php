<?php

namespace validator;

use utility\constant\ApiResponseConstant;
use utility\constant\CommonConstant;

/**
 * groupvalidator - contains validation methods for group validations
 */
class DiscountValidator {
	
	// Method for validating input parameters for add group
	public static function validate_discount($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules ( array (
				'discount_name' => array (
						'add' => 'required',
						'edit' => 'required',
						'common' => 'empty' 
				),
				'discount_value' => array (
						'add' => 'required',
						'common' => 'empty'
				),
				'provisionable' => array (
						'add' => 'required',
						'common' => 'containsList,'.  CommonConstant::YES.';'.CommonConstant::NO.'|empty'
				),
				'id' => array (
						'edit' => 'required',
						'common' => 'empty' 
				),
		), $action );
		
		$validator->set_error_codes ( array (
				'empty' => array (
						'*' => ApiResponseConstant::MISSING_REQUIRED_PARAMETERS 
				),
				'max_len' => array (
						'*' => ApiResponseConstant::MAX_LENGTH_EXCEEDED 
				) 
		) );
		return $validator->run ( $parameter_array, FALSE );
	}
}