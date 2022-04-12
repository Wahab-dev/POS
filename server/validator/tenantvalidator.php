<?php

namespace validator;

use utility\constant\InputFieldLengthConstant;

/**
 * tenantvalidator - contains validation methods for tenant validations
 */
class TenantValidator {
	
	// Method for validating input parameters for add group
	public static function validate_tenant($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules ( array (
				'tenant_code' => array (
						'rename' => 'required',
						'common' => 'empty'
				),
				'tenant_name' => array (
						'add' => 'required',
						'rename' => 'required',
						'common' => 'empty' 
				),
				'phone_number' => array (
						'add' => 'required',
						'common' => 'empty' . '|max_len,' . InputFieldLengthConstant::PHONE_NUMBER_LENGTH 
				),
				'alternate_number' => array (
						'add' => 'required',
						'common' => 'empty' . '|max_len,' . InputFieldLengthConstant::PHONE_NUMBER_LENGTH 
				),
				'tenant_attention' => array (
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