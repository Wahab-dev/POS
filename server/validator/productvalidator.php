<?php

namespace validator;

use utility\constant\InputFieldLengthConstant;
use utility\constant\ApiResponseConstant;

/**
 * groupvalidator - contains validation methods for group validations
 */
class ProductValidator {
	
	// Method for validating input parameters for add group
	public static function validate_product($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules ( array (
				'product_name' => array (
						'add' => 'required',
						'edit' => 'required',
						'common' => 'empty' 
				),
				'supplier_name' => array (
						'add' => 'required',
						'common' => 'empty'
				),
				'product_type' => array (
						'add' => 'required',
						'common' => 'empty'
				),
				'provisionable' => array (
						'add' => 'required',
						'common' => 'empty' 
				),
				'seasonable' => array (
						'add' => 'required',
						'common' => 'empty'
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