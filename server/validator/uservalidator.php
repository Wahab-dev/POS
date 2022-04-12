<?php

namespace validator;

use utility\constant\InputFieldLengthConstant;
use utility\constant\ApiResponseConstant;

/**
 * UserValidator - contains validation methods for user validations
 */
class UserValidator {
	
	// Method for validating input parameters for user
	public static function validate_user($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules(array(
				'first_name' => array (
						'add' => 'required',
						'common' => 'max_len,'.InputFieldLengthConstant::USER_FIRST_NAME_LENGTH.'|empty' 
				), 
				'last_name' => array (
						'common' => 'max_len,'.InputFieldLengthConstant::USER_LAST_NAME_LENGTH.'|empty'
				),
				'user_name' => array (
						'add' => 'required',
						'common' => 'max_len,'.InputFieldLengthConstant::USER_LAST_NAME_LENGTH.'|empty'
				),
				'password' => array (
						'add' => 'required',
						'common' => 'empty'
				),
				'phone_number' => array (
						'add' => 'required',
						'common' => 'max_len,'.InputFieldLengthConstant::PHONE_NUMBER_LENGTH.'|empty'
				),
				'alternate_number' => array (
						'add' => 'required',
						'common' => 'max_len,'.InputFieldLengthConstant::PHONE_NUMBER_LENGTH.'|empty'
				),
				'email_address' => array (
						'add' => 'required',
						'common' => 'empty'
				),
				'access_level_name' => array (
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
				'id' => array (
						'edit' => 'required',
						'common' => 'empty'
				),
		), $action);
		
		$validator->set_error_codes ( array (
				'empty' => array (
						'*' => ApiResponseConstant::MISSING_REQUIRED_PARAMETERS 
				),
				'max_len'=>array(
						'*' => ApiResponseConstant::MAX_LENGTH_EXCEEDED
				)
		) );
		return $validator->run ( $parameter_array, FALSE );
	}
}