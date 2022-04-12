<?php
use utility\constant\ResponseConstant;
use utility\constant\ApiResponseConstant;
use utility\constant\CommonConstant;
/**
 * Error Messages in English
 **/
$error_msg = array(
		// Error messages for HTTP Response codes		
		ResponseConstant::HTTP_UNAUTHORIZED => 'Unauthorized access',
		ResponseConstant::HTTP_FORBIDDEN => 'Unauthorized access',
		ResponseConstant::HTTP_NOT_IMPLEMENTED => 'Invalid request',
		ResponseConstant::HTTP_NOT_FOUND => 'Not found',
		ResponseConstant::HTTP_METHOD_NOT_ALLOWED => 'Method not allowed',
		
		// Error messages related to authorization                           
		ApiResponseConstant::VALIDATION_FAILED => 'Validation error',                 
		ApiResponseConstant::CREATED_FAILED => 'Creation error',
		ApiResponseConstant::UPDATED_FAILED => 'Update error',
		ApiResponseConstant::DELETED_FAILED => 'Delete error',
		ApiResponseConstant::INVALID_JSON_REQUEST => 'Unable to parse json request',
		ApiResponseConstant::CONTEXT_NOT_AUTHORIZED => 'Unauthorized access',
		ApiResponseConstant::ROLE_NOT_AUTHORIZED => 'Unauthorized access',
		ApiResponseConstant::UNAUTHORIZED_ACCESS => 'Unauthorized access',
		ApiResponseConstant::ACCOUNT_LOCKED => 'User account locked',
		ApiResponseConstant::PASSWORD_EXPIRED => 'User password expired',

		
		// Error messages related to common error codes
		ApiResponseConstant::RESOURCE_NOT_EXISTS => CommonConstant::API_SCREEN_NAME_PLACE_HOLDER.' not found',
		ApiResponseConstant::RESOURCE_IN_USE => CommonConstant::API_SCREEN_NAME_PLACE_HOLDER.' already in use',
		ApiResponseConstant::RESOURCE_ALREADY_EXIST => CommonConstant::API_SCREEN_NAME_PLACE_HOLDER.' already exists',
		ApiResponseConstant::UNABLE_TO_REMOVE_RESOURCE => 'Unable to remove '.CommonConstant::API_SCREEN_NAME_PLACE_HOLDER . '. '. CommonConstant::CONTACT_SUPPORT_MSG,
		ApiResponseConstant::UNABLE_TO_SAVE_RESOURCE => 'Unable to save '.CommonConstant::API_SCREEN_NAME_PLACE_HOLDER . '. '. CommonConstant::CONTACT_SUPPORT_MSG,
		ApiResponseConstant::UNABLE_TO_UPDATE_RESOURCE => 'Unable to update '.CommonConstant::API_SCREEN_NAME_PLACE_HOLDER . '. '. CommonConstant::CONTACT_SUPPORT_MSG,
		ApiResponseConstant::FAILURE_ON_DOMAIN_RESOURCE_UPDATE => 'Failure on domain resource update.',
		ApiResponseConstant::UNABLE_TO_CONNECT_PROVISIONING_SYSTEM => 'Unable to perform operation.' . ' ' . CommonConstant::CONTACT_SUPPORT_MSG,
		ApiResponseConstant::BATCH_FILE_VERSION_NOT_EXISTS => "There is an updated version of the worksheet. Please use the latest version.",
		
		ApiResponseConstant::MISSING_REQUIRED_PARAMETERS => 'Required parameter(s) missing.',
		ApiResponseConstant::MAX_LENGTH_EXCEEDED => 'Parameter(s) maximum characters length exceeded.',
		ApiResponseConstant::MIN_LENGTH_NOT_REACHED => 'Parameter(s) minimum characters length required.',
		ApiResponseConstant::INVALID_VALUES_IN_PARAMETERS =>'Invalid parameter value.',
		ApiResponseConstant::UNKNOWN_ERROR_OCCURRED =>'Unable to process the request.' . ' ' . CommonConstant::CONTACT_SUPPORT_MSG,
		ApiResponseConstant::MODIFICATION_NOT_ALLOWED => 'Modification not allowed.',
		ApiResponseConstant::REPEATED_UNIQUE_KEYS => 'Invalid request.',
		ApiResponseConstant::PROV_NO_RESPONSE => 'Internal communication error.' . ' ' . CommonConstant::CONTACT_SUPPORT_MSG,
		ApiResponseConstant::USER_DETAILS_REQUIRED => 'User details like username, password, firstname, phone number, address are required',
		
		//for Group Error message
		ApiResponseConstant::UNABLE_TO_DELETE_GROUP_TENANT_EXIST => 'Group cannot deleted, due to tenant exist.',
		
		// for access level error message
		ApiResponseConstant::UNABLE_TO_DELETE_ACCESS_LEVEL_USER_EXIST => 'Access level cannot deleted, due to mapped to user exist.',
		
		// for order error message
		ApiResponseConstant::PRODUCT_NON_PROVISIONABLE => 'The product'.CommonConstant::API_SCREEN_NAME_PLACE_HOLDER.'is non provisionable product',
		ApiResponseConstant::DISCOUNT_NON_PROVISIONABLE => 'The discount'.CommonConstant::API_SCREEN_NAME_PLACE_HOLDER.'is non provisionable product',
		ApiResponseConstant::ORDER_EXPIRED => 'Order has been expired, Please try with a new one',
		ApiResponseConstant::ORDER_PRODUCT_VALIDATION => 'Your must contain atleast one product',
		ApiResponseConstant::ORDER_PRODUCT_CONFIRMED => 'Already your order has been confirmed',
		ApiResponseConstant::ORDER_PRODUCT_REJECTED => 'Already your order has been rejected',
		ApiResponseConstant::ORDER_PRODUCT_RETURNED => 'Already your order has been returned',
		
		// for user error message
		ApiResponseConstant::AUTHENTICATION_FAILED => 'User name or password is incorrect',
		ApiResponseConstant::USER_DELETED => 'User has been deleted',
		ApiResponseConstant::USER_INACTIVE => 'User is in inactive state, please contact admin',
		
		// for discount error message
		ApiResponseConstant::UNABLE_TO_DELETE_DISCOUNT_PRODUCT_EXIST => 'Unable to delete discount as product exist',
);