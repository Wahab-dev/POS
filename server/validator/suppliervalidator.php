<?php

namespace validator;

use utility\constant\InputFieldLengthConstant;
use utility\constant\ApiResponseConstant;

/**
 * supplierValidator - contains validation methods for supplier validations
 */
class SupplierValidator {
	
	// Method for validating input parameters for supplier
	public static function validate_supplier($parameter_array, $action, $client = FALSE) {
		$validator = new Validator ();
		$validator->set_validation_rules ( array (
				'supplier_name' => array (
						'add' => 'required',
						'common' => 'empty' 
				),
				'supplier_attention' => array (
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
		), $action );
		
		$validator->set_error_codes ( array (
				'empty' => array (
						'*' => ApiResponseConstant::MISSING_REQUIRED_PARAMETERS 
				)
		) );
		return $validator->run ( $parameter_array, FALSE );
	}
}