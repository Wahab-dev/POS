<?php
namespace controller;

use controller\CommonController;
use utility\constant\ListFieldConstant;
use utility\UtilityMethods;
use utility\constant\CommonConstant;
use utility\constant\ApiResponseConstant;
use validator\ListValidator;
use validator\GroupValidator;

class SpecialdiscountController extends CommonController {
	function __construct($facade) {
		parent::__construct ( $facade );
	}
	public function index() {
	}

	/* for getting group list */
	public function get_all() {
		try {
			$this->_parameter_array ['field_list'] = ListFieldConstant::$specialdiscount_fields;
				
			/* for validating list fields */
			$validate = ListValidator::validate_list ( $this->_parameter_array, CommonConstant::ACTION_TYPE_GET_LIST, $client = false );
			$this->check_validator_response ( $validate );
			$response_array = $this->_facade->getAllGroupDetails ( $this->_parameter_array );
			$this->check_error ( $response_array );
				
			$response_data = array ();
			$response_data ['group_details'] = $response_array ['group_details'];
			$response_data ['count'] = $response_array ['count'];
			$response_data ['limit'] = $response_array ['limit'];
			$this->dispatch_success ( $response_data );
		} catch ( Exception $e ) {
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
	}
}