<?php

namespace validator;

use utility\constant\InputFieldLengthConstant;
use utility\constant\ApiResponseConstant;

/**
 * bankinfoValidator - contains validation methods for bank info validations
 */
class BankinfoValidator {
	
	// Method for validating input parameters for bank info
	public static function validate_bankinfo($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules ( array (
				'bank_name' => array (
						'add' => 'required',
						'common' => 'empty' 
				),
				'bank_branch' => array (
						'add' => 'required',
						'common' => 'empty'
				),
				'bank_code' => array (
						'add' => 'required',
						'common' => 'empty' 
				),
				'account_number' => array (
						'add' => 'required',
						'edit' => 'required',
						'common' => 'empty' 
				),
				'account_type' => array (
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