<?php

namespace validator;

use utility\constant\InputFieldLengthConstant;
use utility\constant\ApiResponseConstant;

/**
 * groupvalidator - contains validation methods for group validations
 */
class GroupValidator {
	
	// Method for validating input parameters for add group
	public static function validate_group($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules ( array (
				'group_name' => array (
						'add' => 'required',
						'edit' => 'required',
						'common' => 'empty' 
				),
				'id' => array (
						'edit' => 'required',
						'common' => 'empty'
				),
				'group_attention' => array (
						'add' => 'required',
						'common' => 'empty' 
				),
				'address_line_1' => array (
						'add' => 'required',
						'common' => 'empty' 
				),
				'address_line_2' => array (
						'common' => 'empty' 
				),
				'email_address' => array (
						'add' => 'required',
						'common' => 'empty' 
				) 
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