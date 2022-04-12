<?php

namespace controller;

use controller\Controller;
use utility\constant\ResponseConstant;
use utility\constant\CommonConstant;
use utility\constant\ApiResponseConstant;
use utility\UtilityMethods;

abstract class CommonController extends Controller {
	public $_content_type = "application/json";
	public $_parameter_array = array ();
	private $_code = ResponseConstant::HTTP_OK;
	public static $locale = DEFAULT_LOCALE;
	
	// A constructor method with $viewFolderName, $action, $facadeBaseName as Parameters
	public function __construct($facadeBaseName) {
		$this->inputs ();
		parent::__construct ( $facadeBaseName );
	}
	
	/*
	 * // method to download file
	 * function download_file($file, $name, $mime_type = '') {
	 * $file = json_decode ( base64_decode ( $file ) );
	 * $file = DOWNLOAD_BASE_PATH . $file;
	 * $name = json_decode ( base64_decode ( $name ) );
	 * if (! is_readable ( $file )) {
	 * die ( "File not found" );
	 * }
	 * $size = filesize ( $file );
	 * $name = rawurldecode ( $name );
	 *
	 * $known_mime_types = array (
	 * "pdf" => "application/pdf",
	 * "txt" => "text/plain",
	 * "html" => "text/html",
	 * "htm" => "text/html",
	 * "exe" => "application/octet-stream",
	 * "zip" => "application/zip",
	 * "doc" => "application/msword",
	 * "xls" => "application/vnd.ms-excel",
	 * "xlsx" => "application/vnd.ms-excel",
	 * "ppt" => "application/vnd.ms-powerpoint",
	 * "gif" => "image/gif",
	 * "png" => "image/png",
	 * "jpeg" => "image/jpg",
	 * "jpg" => "image/jpg",
	 * "php" => "text/plain"
	 * );
	 *
	 * if ($mime_type == '') {
	 * $file_extension = strtolower ( substr ( strrchr ( $file, "." ), 1 ) );
	 * if (array_key_exists ( $file_extension, $known_mime_types )) {
	 * $mime_type = $known_mime_types [$file_extension];
	 * } else {
	 * $mime_type = "application/force-download";
	 * }
	 * ;
	 * }
	 * ;
	 *
	 * header ( 'Content-Type: ' . $mime_type );
	 * header ( 'Content-Disposition: attachment; filename="' . $name . '"' );
	 * header ( "Content-Transfer-Encoding: binary" );
	 * header ( 'Accept-Ranges: bytes' );
	 * header ( "Cache-control: private" );
	 * header ( 'Pragma: private' );
	 * readfile ( $file );
	 * }
	 */
	
	// method to check if REQUEST METHOD
	public function get_request_method() {
		return $_SERVER ['REQUEST_METHOD'];
	}
	public function get_request_content_type() {
		$content_type = "";
		$headers = apache_request_headers ();
		foreach ( $headers as $key => $value ) {
			if (UtilityMethods::isStringEqual ( "$key", "Content-Type", true )) {
				$content_type = $value;
			}
		}
		// UtilityMethods::printDebugMessage("Content Type:".$content_type);
		return $content_type;
	}
	private function inputs() {
		$method = $this->get_request_method ();
		$content_type = $this->get_request_content_type ();
		if (preg_match ( "/application\/json/i", $content_type )) {
			if (UtilityMethods::isEqual ( $method, "GET", true )) {
				$this->_parameter_array = $this->cleanInputs ( $_GET );
			} else {
				if (! array_key_exists ( "_{$method}_DATA_FILLED", $GLOBALS )) {
					$input_content = file_get_contents ( "php://input" );
					$input_array = json_decode ( $input_content, true );
					$GLOBALS ["RAW_INPUT_CONTENT"] = $input_content;
					$GLOBALS ["_{$method}"] = $input_array;
					$GLOBALS ["_{$method}_DATA_FILLED"] = "YES";
					// Add these request vars into _REQUEST, mimicing default behavior, PUT/DELETE will override existing COOKIE/GET vars
					$_REQUEST = $this->_parameter_array + $_REQUEST;
					
					// add debug statements for SAP debugging
					/*
					 * error_log('GLOBALS '.print_r($GLOBALS,TRUE));
					 * error_log('input_array'.print_r($input_array,TRUE));
					 * error_log('input_content'.print_r($input_content,TRUE));
					 * error_log('_REQUEST'.print_r($_REQUEST,TRUE));
					 */
				}
				$this->_parameter_array = $GLOBALS ["_{$method}"];
				// error_log('parameter_array'.print_r($this->_parameter_array,TRUE));
			}
		} else {
			switch ($method) {
				case "POST" :
					$this->_parameter_array = $this->cleanInputs ( $_POST );
					break;
				case "GET" :
					$this->_parameter_array = $this->cleanInputs ( $_GET );
					break;
				case "PUT" :
				case "DELETE" :
					if (! array_key_exists ( "_{$method}", $GLOBALS )) {
						$input_content = file_get_contents ( "php://input" );
						parse_str ( $input_content, $this->_parameter_array );
						$GLOBALS ["_{$method}"] = $this->_parameter_array;
						// Add these request vars into _REQUEST, mimicing default behavior, PUT/DELETE will override existing COOKIE/GET vars
						$_REQUEST = $this->_parameter_array + $_REQUEST;
					}
					$this->_parameter_array = $this->cleanInputs ( $GLOBALS ["_{$method}"] );
					break;
				default :
					$this->response ( '', ResponseConstant::HTTP_FORBIDDEN );
					break;
			}
		}
	}
	private function parse_raw_http_request(array &$a_data) {
		// http://www.chlab.ch/blog/archives/php/manually-parse-raw-http-data-php
		
		// read incoming data
		$input = file_get_contents ( 'php://input' );
		
		// grab multipart boundary from content type header
		preg_match ( '/boundary=(.*)$/', $_SERVER ['CONTENT_TYPE'], $matches );
		$boundary = $matches [1];
		
		// split content by boundary and get rid of last -- element
		$a_blocks = preg_split ( "/-+$boundary/", $input );
		array_pop ( $a_blocks );
		
		// loop data blocks
		foreach ( $a_blocks as $id => $block ) {
			if (empty ( $block ))
				continue;
				
				// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
				
			// parse uploaded files
			if (strpos ( $block, 'application/octet-stream' ) !== FALSE) {
				// match "name", then everything after "stream" (optional) except for prepending newlines
				preg_match ( "/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches );
			}  // parse all other fields
else {
				// match "name" and optional value in between newline sequences
				preg_match ( '/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches );
			}
			$a_data [$matches [1]] = $matches [2];
		}
	}
	private function cleanInputs($data) {
		$clean_input = array ();
		if (is_array ( $data )) {
			foreach ( $data as $k => $v ) {
				$clean_input [$k] = $this->cleanInputs ( $v );
			}
		} else {
			if (get_magic_quotes_gpc ()) {
				$data = trim ( stripslashes ( $data ) );
			}
			$data = strip_tags ( $data );
			$clean_input = trim ( $data );
		}
		return $clean_input;
	}
	
	// method to set the status Message
	public function get_status_message() {
		$status = array (
				100 => 'Continue',
				101 => 'Switching Protocols',
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found',
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				306 => '(Unused)',
				307 => 'Temporary Redirect',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported' 
		);
		return ($status [$this->_code]) ? $status [$this->_code] : $status [ResponseConstant::HTTP_INTERNAL_SERVER_ERROR];
	}
	
	// method to append the header and return the response
	public function response($data, $status) {
		$this->_code = ($status) ? $status : ResponseConstant::HTTP_OK;
		header ( "HTTP/1.1 " . $this->_code . " " . $this->get_status_message () );
		header ( "Content-Type:" . $this->_content_type );
		echo $data;
		exit ();
	}
	
	// method to convert response --> json format --> Base64 encode
	public function wrapResponse($data) {
		if (is_array ( $data )) {
			return $this->wrapResponseAsJSON ( $data );
		}
	}
	
	// method to convert Response to JSON
	public function wrapResponseAsJSON($data) {
		$response = json_encode ( $data );
		if (json_last_error ()) {
			if ($this->_isDebugEnabled ()) {
				$this->_debug ( "JSON encode failed : " . json_last_error_msg () . " data : " . var_export ( $data, true ) );
			}
			$this->dispatch_failure ( ApiResponseConstant::UNKNOWN_ERROR_OCCURRED );
		}
		return $response;
	}
	
	// Method to dispatch success message
	public function dispatch_success($response_array) {
		if (isset ( $response_array ['response_status'] )) {
			unset ( $response_array ['response_status'] );
		}
		$success_array = array (
				'transaction_code' => CommonConstant::RESPONSE_SUCCESS_CODE,
				'transaction_message' => CommonConstant::RESPONSE_SUCCESS_MESSAGE 
		);
		if (is_array ( $response_array )) {
			$success_array = array_merge ( $success_array, $response_array );
		}
		$this->response ( $this->wrapResponse ( $success_array ), ResponseConstant::HTTP_OK );
	}
	
	// Method to dispatch failure message
	public function dispatch_failure($error_code, $error_details = null, $detailed_array = null, $http_response_code = ResponseConstant::HTTP_OK) {
		include ERROR_MSG_PATH . 'error_messages_' . self::$locale . '.php';
		if ($http_response_code == ResponseConstant::HTTP_OK) {
			if (is_array ( $error_code )) {
				$response_array = array (
						'transaction_code' => $error_code ['code'],
						'transaction_message' => $error_code ['message'] 
				);
				
				$this->response ( $this->wrapResponse ( $response_array ), $http_response_code );
			} else {
				$error_message = "";
				if (UtilityMethods::isEqual ( $error_code, ApiResponseConstant::TRANSLATED_ERROR_MESSAGE )) {
					$error_message = $error_details;
				} else {
					$error_message = str_replace ( '##FIELDNAME##', $error_details, $error_msg [$error_code] );
					$replace = (empty ( $error_details )) ? ucfirst ( str_replace ( "-", " ", $this->_screen_name ) ) : $error_details;
					if (preg_match ( "/" . CommonConstant::API_SCREEN_NAME_PLACE_HOLDER . "/i", $error_message )) {
						$error_message = str_replace ( CommonConstant::API_SCREEN_NAME_PLACE_HOLDER, $replace, $error_message );
					}
				}
				
				$response_array = array (
						'transaction_code' => $error_code,
						'transaction_message' => $error_message 
				);
			}
			// $response_array['transaction_message']=$response_array['transaction_message']." (". $response_array['transaction_code'].")";
			if (is_array ( $detailed_array )) {
				$response_array = array_merge ( $response_array, $detailed_array );
			}
			$this->response ( $this->wrapResponse ( $response_array ), $http_response_code );
		} else {
			$this->response ( $error_msg [$http_response_code], $http_response_code );
		}
	}
	
	// Method to dispatch warning message
	public function dispatch_warning($response_array, $warning_code) {
		unset ( $response_array ['response_status'] );
		include_once CLIENT_ERROR_MSG_PATH . 'error_messages_' . self::$locale . '.php';
		$warning_message = $error_msg [$warning_code];
		
		$response_additional_array = array (
				'transaction_status' => CommonConstant::RESPONSE_TYPE_SUCCESS,
				'transaction_code' => $warning_code,
				'transaction_message' => $warning_message 
		);
		$response_array = array_merge ( $response_additional_array, $response_array );
		$this->response ( $this->wrapResponse ( $response_array ), ResponseConstant::HTTP_OK );
	}
	
	// Method to dispatch custom failure message
	public function dispatch_custom_failure($error_message) {
		$response_array = array (
				'transaction_message' => $error_message 
		);
		$this->response ( $this->wrapResponse ( $response_array ), ResponseConstant::HTTP_OK );
	}
	
	// Method to authorize context and respond with error message if not authorized
	protected function authorize_context($allowed_contexts) {
	}
	
	// Method to authorize role and respond with error message if not authorized
	protected function authorize_role($allowed_roles) {
	}
	
	/* for setting session details */
	protected function _set_session_details() {
	}
	
	/*
	 * // method convert excel to csv and save csv file
	 * public function process_upload_file($support_excel_sheet = array("Sheet1","Example")) {
	 * $response = array ();
	 * $file = RequestHelper::file ( "file" );
	 * $upload_error_status = Uploadhelper::check_upload_error ( $file );
	 * if (UtilityMethods::isNotEmpty ( $upload_error_status )) {
	 * $response ["upload_error_status"] = $upload_error_status;
	 * return $response;
	 * }
	 *
	 * // validating file using its name and extension
	 * $file_name = basename ( $file ['name'] );
	 * $extn_pos = strpos ( $file_name, '.' );
	 * $extension = substr ( $file_name, $extn_pos );
	 *
	 * if (! (UtilityMethods::isEqual ( $extension, ".csv" ) || UtilityMethods::isEqual ( $extension, ".xls" ) || UtilityMethods::isEqual ( $extension, ".xlsx" ))) {
	 * $response ["upload_error_status"] = "Please upload csv or excel file";
	 * return $response;
	 * }
	 * if (UtilityMethods::isEqual ( $extension, ".csv" )) {
	 * $csv_file_name = basename ( $file_name, $extension );
	 * $file_name = UtilityMethods::get_unique_file_name ( BULK_FILE_UPLOAD_FOLDER, $csv_file_name . ".csv", "csv" );
	 * $target_file_path = BULK_FILE_UPLOAD_FOLDER . $file_name;
	 * $move_file_status = move_uploaded_file ( $file ['tmp_name'], $target_file_path );
	 * if (! $move_file_status) {
	 * $response ["upload_error_status"] = "There was an error uploading the file";
	 * return $response;
	 * }
	 * } else {
	 * $conversion_response_array = UtilityMethods::excel_to_csv ( $file ['tmp_name'], $support_excel_sheet );
	 * if (UtilityMethods::isEqual ( $conversion_response_array ["STATUS"], "ERROR" )) {
	 * if ($this->_isErrorEnabled ()) {
	 * $this->_error ( "Converting Excel to CSV Failed:" . $conversion_response_array ["MESSAGE"] );
	 * }
	 * $response ["upload_error_status"] = "Converting Excel to CSV Failed.";
	 * return $response;
	 * } else {
	 * if ($this->_isDebugEnabled ()) {
	 * $this->_debug ( "Excel file is valid and Converting it to CSV." );
	 * }
	 * // Check if file with same name already exists and rename new file
	 * $csv_file_name = basename ( $file_name, $extension );
	 * $file_name = UtilityMethods::get_unique_file_name ( BULK_FILE_UPLOAD_FOLDER, $csv_file_name . ".csv", "csv" );
	 * $target_file_path = BULK_FILE_UPLOAD_FOLDER . $file_name;
	 *
	 * $out = fopen ( $target_file_path, 'w' );
	 * foreach ( $conversion_response_array ["CSV_RESULT"] as $row_data ) {
	 * fputcsv ( $out, $row_data );
	 * }
	 * fclose ( $out );
	 * $convert_data = mb_convert_encoding ( file_get_contents ( $target_file_path ), "HTML-ENTITIES", "UTF-8" );
	 * file_put_contents ( $target_file_path, $convert_data );
	 * } // end of else-if(error)
	 * }
	 *
	 * return $target_file_path;
	 * }
	 */
	
	public function check_validator_response($validate) {
		if (isset ( $validate ['validation_errors'] ) && count ( $validate ['validation_errors'] ) > 0) {
			$error_param = array_keys ( $validate ['validation_errors'] );
			$error_code = array_values ( $validate ['validation_errors'] );
			$this->dispatch_failure ( $error_code [0], $error_param [0] );
		}
	}
}	
