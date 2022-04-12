<?php

namespace validator;

use utility\constant\InputFieldLengthConstant;
use utility\constant\ApiResponseConstant;

/**
 * AccesslevelValidator - contains validation methods for access level validations
 */
class AccessLevelValidator {
	
	// Method for validating input parameters for access level
	public static function validate_access_level($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules ( array (
				'access_level_name' => array (
						'add' => 'required',
						'edit' => 'required',
						'common' => 'empty' 
				), 
				'permissions' => array (
						'add' => 'required',
						'common' => 'array|empty'
				),
				'id' => array (
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