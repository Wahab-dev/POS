<?php

namespace validator;

use utility\constant\ApiResponseConstant;
class Validator extends GUMP {
	private $error_codes = array ();
	private $param_map_rules = array ();
	public function set_error_codes(array $error_code_list = array()) {
		$this->error_codes = $error_code_list;
	}
	public function set_param_map_rules(array $param_map_rules = array()) {
		$this->param_map_rules = $param_map_rules;
	}
	public function set_field_map(array $field_map = array()) {
		if (count ( $field_map ) > 0) {
			foreach ( $field_map as $old_field => $new_field ) {
				self::set_field_name ( $old_field, $new_field );
			}
		}
	}
	public function clear_field_map() {
		self::$fields = array ();
	}
	
	/**
	 * Getter/Setter for the validation rules
	 *
	 * @param array $rules        	
	 * @param string $action_type
	 *        	(can contain one for the following: add and edit)
	 * @return array
	 */
	public function set_validation_rules(array $rules = array(), $action_type) {
		if (empty ( $rules )) {
			return $this->validation_rules;
		}
		if (isset ( $action_type )) {
			if (count ( $rules ) > 0) {
				$consolidated_rules = array ();
				foreach ( $rules as $field => $rule_list ) {
					if (is_array ( $rule_list )) {
						$rules_array = array ();
						if (isset ( $rule_list [$action_type] )) {
							array_push ( $rules_array, $rule_list [$action_type] );
						}
						if (isset ( $rule_list ['common'] )) {
							array_push ( $rules_array, $rule_list ['common'] );
						}
						if (count ( $rules_array ) > 0) {
							$consolidated_rules [$field] = implode ( '|', $rules_array );
						}
					} else {
						$consolidated_rules [$field] = $rules [$field];
					}
				}
				$rules = $consolidated_rules;
			}
		}
		$this->validation_rules = $rules;
	}
	
	/**
	 * Run the filtering and validation after each other.
	 * Overriding parent method to return error details,
	 * rather than boolean false on validation failure,
	 *
	 * @param array $data        	
	 * @param bool $check_fields        	
	 * @return array
	 * @throws Exception
	 */
	public function run(array $data, $check_fields = false) {
		$data = $this->filter ( $data, $this->filter_rules () );
		
		$validated = $this->do_validate ( $data, $this->validation_rules, $check_fields );
		if ($validated !== true) {
			return array (
					'validation_errors' => $validated 
			);
		}
		$data = $this->do_param_map ( $data, $this->param_map_rules );
		return $data;
	}
	
	/**
	 * Perform data validation against the provided ruleset.
	 * Overriding the parent method to include feature for
	 * returning on first error and return error details as code or message.
	 *
	 * @access public
	 * @param mixed $input        	
	 * @param array $ruleset        	
	 * @param bool $return_on_first_error        	
	 * @return mixed
	 * @throws Exception
	 */
	public function do_validate(array $input, array $ruleset, $return_on_first_error) {
		$this->errors = array ();
		$input_array = $input;
		foreach ( $ruleset as $field => $rules ) {
			$input = $input_array;
			$rules = explode ( '|', $rules );
			if (count ( $fields = explode ( '|', $field ) ) > 1) {
				$field = $fields [1];
				if (isset ( $input_array [$fields [0]] ) && is_array ( $input_array [$fields [0]] )) {
					$input = $input_array [$fields [0]];
				} else {
					$input = array ();
				}
			}
			if (in_array ( "required", $rules ) || (isset ( $input [$field] )) || empty ( $input [$field] ) && array_key_exists ( $field, $input )) {
				foreach ( $rules as $rule ) {
					$method = NULL;
					$param = NULL;
					
					if (strstr ( $rule, ',' ) !== FALSE) { // has params
						$rule = explode ( ',', $rule );
						$method = 'validate_' . $rule [0];
						$param = $rule [1];
						$rule = $rule [0];
					} else {
						$method = 'validate_' . $rule;
					}
					
					if (is_callable ( array (
							$this,
							$method 
					) )) {
						$result = $this->$method ( $field, $input, $param );
						if (is_array ( $result )) { // Validation Failed
							$this->errors [] = $result;
							if ($return_on_first_error === TRUE) {
								break;
							}
						}
					} else if (isset ( self::$validation_methods [$rule] )) {
						if (isset ( $input [$field] )) {
							$result = call_user_func ( self::$validation_methods [$rule], $field, $input, $param );
							$result = $this->$method ( $field, $input, $param );
							if (is_array ( $result )) { // Validation Failed
								$this->errors [] = $result;
								if ($return_on_first_error === TRUE) {
									break;
								}
							}
						}
					} else {
						throw new \Exception( "Validator method '$method' does not exist." );
					}
				}
			}
			if (count ( $this->errors ) > 0) {
				if ($return_on_first_error === TRUE) {
					break;
				}
			}
		}
		if (count ( $this->errors ) > 0) {
			return $this->get_errors_as_code ();
		} else {
			return TRUE;
		}
	}
	
	// Method to map parameter names to new names
	private function do_param_map($data, array $param_map_rules) {
		if (count ( $param_map_rules ) > 0) {
			foreach ( $param_map_rules as $old_param => $new_param ) {
				if (isset ( $data [$old_param] )) {
					$data [$new_param] = $data [$old_param];
					unset ( $data [$old_param] );
				}
			}
		}
		return $data;
	}
	
	/**
	 * Process the validation errors and return an array of errors as code with field names as keys
	 *
	 * @return array
	 */
	public function get_errors_as_code() {
		if (empty ( $this->errors )) {
			return array ();
		}
		
		$resp = array ();
		
		foreach ( $this->errors as $e ) {
			
			// $field = ucwords(str_replace(array('_','-'), chr(32), $e['field']));
			$field = $e ['field'];
			$param = $e ['param'];
			
			// Let's fetch explicit field names if they exist
			if (array_key_exists ( $e ['field'], self::$fields )) {
				$field = self::$fields [$e ['field']];
			}
			
			$error_set = false;
			if (count ( $this->error_codes ) > 0) {
				$short_validator_name = str_replace ( 'validate_', '', $e ['rule'] );
				if (isset ( $this->error_codes [$short_validator_name] [$field] )) {
					$resp [$field] = $this->error_codes [$short_validator_name] [$field];
					$error_set = true;
				} else if (isset ( $this->error_codes [$short_validator_name] ['*'] )) {
					$resp [$field] = $this->error_codes [$short_validator_name] ['*'];
					$error_set = true;
				}
			}
			
			if (! $error_set) {
				switch ($e ['rule']) {
					case 'validate_required' :
						$resp [$field] = ApiResponseConstant::MISSING_REQUIRED_PARAMETERS;
						break;
					case 'validate_max_len' :
						$resp [$field] = ApiResponseConstant::MAX_LENGTH_EXCEEDED;
						break;
					case 'validate_min_len' :
						$resp [$field] = ApiResponseConstant::MIN_LENGTH_NOT_REACHED;
						break;
					case 'validate_empty' :
						if (UtilityMethods::isAPIVersionGreaterThan3_27 ()) {
							$resp [$field] = ApiResponseConstant::MISSING_REQUIRED_PARAMETERS;
							break;
						}
					case 'validate_unique' :
						$resp [$field] = ApiResponseConstant::REPEATED_UNIQUE_KEYS;
						break;
					case 'validate_valid_email' :
					case 'validate_exact_len' :
					case 'validate_alpha' :
					case 'validate_alpha_numeric' :
					case 'validate_alpha_numeric_or_numeric' :
					case 'validate_alpha_numeric_with_extn_wav' :
					case 'validate_alpha_dash' :
					case 'validate_alpha_underscore_space' :
					case 'validate_alpha_dash_space' :
					case 'validate_numeric' :
					case 'validate_integer' :
					case 'validate_boolean' :
					case 'validate_float' :
					case 'validate_valid_url' :
					case 'validate_valid_ip' :
					case 'validate_valid_cc' :
					case 'validate_contains' :
					case 'validate_date' :
					case 'validate_min_numeric' :
					case 'validate_max_numeric' :
					case 'validate_containsList' :
					case 'validate_empty' :
					case 'validate_array' :
					case 'validate_currency' :
					case 'validate_alpha_dot_digit' :
					case 'validate_sub_domain' :
					case 'validate_root_domain' :
					case 'validate_server_address' :
					case 'validate_smtp_host' :
					case 'validate_alpha_hyphen' :
					case 'validate_alpha_hyphen_dash' :
					case 'validate_smtp_port' :
					case 'validate_characters_except_hypen_underscore_dot' :
					case 'validate_numeric_comma_at_dot_underscore_plus' :
					case 'validate_numeric_comma_at_dot_underscore' :
					case 'validate_xml_element' :
					case 'validate_alpha_numeric_comma_at_underscore' :
					case 'validate_alpha_numeric_underscore_hypen_dot' :
					case 'validate_alpha_numeric_except_comma' :
					case 'validate_numeric_integer' :
					case 'validate_mac_address' :
					case 'validate_alpha_character' :
					case 'validate_alpha_numeric_comma_at' :
					case 'validate_fieldList' :
					case 'validate_pstndid' :
					case 'validate_numeric_array' :
					case 'validate_enabled_disabled' :
					case 'validate_time' :
					case 'validate_ip_address' :
					case 'validate_subnet_mask' :
					case 'validate_alpha_star' :
					case 'validate_host_or_ip' :
					case 'validate_valid_url_char' :
					case 'validate_valid_fqdn' :
					case 'validate_valid_domain' :
					case 'validate_user_alias' :
					case 'validate_callrouting_alias' :
					case 'validate_subscriber_password' :
					case 'validate_subsciber_userid' :
					case 'validate_array_of_array' :
						$resp [$field] = ApiResponseConstant::INVALID_VALUES_IN_PARAMETERS;
						break;
					default :
						throw new Exception ( 'Error thrown from undefined validation method - ' . $e ['rule'] );
				}
			}
		}
		
		return $resp;
	}
	
	/**
	 * Determine if the provided value contains only alpha characters with dashed and underscores and space
	 *
	 * Usage: '<index>' => 'alpha_dash_space'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_alpha_dash_space($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! preg_match ( "/^([a-z0-9_\-\s])+$/i", $input [$field] ) !== FALSE) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	protected function validate_alpha_underscore_space($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! preg_match ( "/^([a-z0-9_\s])+$/i", $input [$field] ) !== FALSE) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value contains only alpha characters with star at end
	 *
	 * Usage: '<index>' => 'alpha_star'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_alpha_star($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		foreach ( $input [$field] as $input ) {
			$string_position = stripos ( $input, "*" );
			$sting_length = strlen ( $input );
			if (! empty ( $string_position )) {
				if ($string_position != ($sting_length - 1)) {
					return array (
							'field' => $field,
							'value' => $input,
							'rule' => __FUNCTION__,
							'param' => $param 
					);
				}
			}
		}
	}
	
	/**
	 * Determine if the provided value contains only alpha characters with hyphen
	 *
	 * Usage: '<index>' => 'alpha_hyphen'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_alpha_hyphen($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! preg_match ( '/^[a-z0-9 \-]+$/i', $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value contains only alpha characters with hyphen & dash
	 *
	 * Usage: '<index>' => 'alpha_hyphen_dash'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_alpha_hyphen_dash($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! preg_match ( '/^[a-z0-9_\-]+$/i', $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value is empty
	 *
	 * Usage: '<index>' => 'validate_empty'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_empty($field, $input, $param = NULL) {
		if (isset ( $input [$field] ) && empty ( $input [$field] ) && ! is_numeric ( $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		} else {
			return;
		}
	}
	
	/**
	 * Determine if the provided value is array
	 *
	 * Usage: '<index>' => 'validate_array'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_array($field, $input, $param = NULL) {
		if (isset ( $input [$field] ) && ! is_array ( $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		} else {
			return;
		}
	}
	
	/**
	 * Determine if the provided value is valid currency
	 *
	 * Usage: '<index>' => 'validate_array'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_currency($field, $input, $param = NULL) {
		if (isset ( $input [$field] ) && ! ValidationMethods::is_supported_currency ( $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		} else {
			return;
		}
	}
	protected function validate_encoded($field, $input, $param = NULL) {
		if (isset ( $input [$field] ) && ! ValidationMethods::is_encoded_string ( $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		} else {
			return;
		}
	}
	
	/**
	 * Determine if the provided value is a valid server address
	 *
	 * Usage: '<index>' => '0.0.0.0 - 255.255.255.255 or valid host name'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_server_address($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! preg_match ( "/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^(?:(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-fA-F]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:(?:[0-9a-fA-F]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,1}(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:(?:[0-9a-fA-F]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,2}(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:(?:[0-9a-fA-F]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,3}(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:[0-9a-fA-F]{1,4})):)(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,4}(?:(?:[0-9a-fA-F]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,5}(?:(?:[0-9a-fA-F]{1,4})))?::)(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,6}(?:(?:[0-9a-fA-F]{1,4})))?::))))$/", $input [$field] ) !== FALSE) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	protected function validate_alpha_numeric_comma_at_underscore($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		if (! preg_match ( "/^[A-Za-z0-9_,.@\-]+$/i", $input [$field] ) !== FALSE) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	protected function validate_alpha_numeric_except_comma($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (strpos ( $input [$field], "," ) !== false || strpos ( $input [$field], "\"" ) !== false) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	protected function validate_alpha_numeric_underscore_hypen_dot($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		$string_position = stripos ( $input [$field], "+" );
		if (! preg_match ( "/^[A-Za-z0-9_.@\-\+]+$/i", $input [$field] ) !== FALSE || ($string_position != 0)) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value is a valid group name for deviceparameter
	 *
	 * Usage: '<index>' => 'xml_element'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_xml_element($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		if (preg_match ( "/[\/\?\>\<\'\"\&\=]/i", $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value is a valid host
	 *
	 * Usage: '<index>' => 'smtp_host'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_smtp_host($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		$validhostnameregex = "/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^(?:(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-fA-F]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:(?:[0-9a-fA-F]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,1}(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:(?:[0-9a-fA-F]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,2}(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:(?:[0-9a-fA-F]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,3}(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:[0-9a-fA-F]{1,4})):)(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,4}(?:(?:[0-9a-fA-F]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,5}(?:(?:[0-9a-fA-F]{1,4})))?::)(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,6}(?:(?:[0-9a-fA-F]{1,4})))?::))))$/";
		if (! preg_match ( $validhostnameregex, $input [$field] ) !== FALSE) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value is a valid port
	 *
	 * Usage: '<index>' => 'smtp_port'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_smtp_port($field, $input, $param = NULL) {
		if (! is_numeric ( $input [$field] ) || $input [$field] <= 0 || $input [$field] > 65535 || $input [$field] == 1024) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value is a valid mac_address
	 * This will accept both string and array as input value
	 * Usage: '<index>' => 'mac_address'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_mac_address($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		$validation_failed = false;
		if (is_array ( $input [$field] )) {
			foreach ( $input [$field] as $item ) {
				if (strlen ( $item ) == 17) {
					if (preg_match ( "/^([0-9A-F]{2}[:]){5}([0-9A-F]{2})$/i", $item ) || preg_match ( "/^([0-9A-F]{2}[-]){5}([0-9A-F]{2})$/i", $item ) || preg_match ( "/^([0-9A-F]{2}[\s]){5}([0-9A-F]{2})$/i", $item )) {
						continue;
					} else {
						$validation_failed = true;
						break;
					}
				} elseif (strlen ( $item ) == 12) {
					if (! preg_match ( "/^([0-9A-F]{12}){1}$/i", $item )) {
						$validation_failed = true;
						break;
					}
				} else { // Not having length 17 or 12 then
					$validation_failed = true;
					break;
				}
			}
		} else {
			if (strlen ( $input [$field] ) == 17) {
				if (preg_match ( "/^([0-9A-F]{2}[:]){5}([0-9A-F]{2})$/i", $input [$field] ) || preg_match ( "/^([0-9A-F]{2}[-]){5}([0-9A-F]{2})$/i", $input [$field] ) || preg_match ( "/^([0-9A-F]{2}[\s]){5}([0-9A-F]{2})$/i", $input [$field] )) {
					return;
				} else {
					$validation_failed = true;
				}
			} elseif (strlen ( $input [$field] ) == 12) {
				if (! preg_match ( "/^([0-9A-F]{12}){1}$/i", $input [$field] )) {
					$validation_failed = true;
				} else {
					return;
				}
			} else { // Not having length 17 or 12 then
				$validation_failed = true;
			}
		}
		
		if ($validation_failed) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		} else {
			return;
		}
	}
	
	/**
	 * check for special characters except hypen and underscores
	 *
	 * Usage: '<index>' => 'characters_except_hypen_underscore_dot'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	//
	protected function validate_characters_except_hypen_underscore_dot($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (preg_match ( "/[^a-z_.\-0-9]/i", $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value contains only alpha characters
	 *
	 * Usage: '<index>' => 'alpha_character'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_alpha_character($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! ctype_alnum ( $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	/**
	 * method user to vaildate field list
	 *
	 * Usage: '<index>' => 'valid_fieldsList'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	public function validate_fieldList($field, $input, $param = NULL) {
		$values = trim ( strtolower ( $input [$field] ) );
		$values = explode ( ",", $input [$field] );
		$param = explode ( ";", $param );
		foreach ( $values as $value ) {
			if (! in_array ( $value, $param )) {
				return array (
						'field' => $field,
						'value' => $value,
						'rule' => __FUNCTION__,
						'param' => $param 
				);
			}
		}
	}
	
	/**
	 * Determine if the input is valid pstndid
	 * Can validate an array of pstn dids or a single pstn did
	 * Usage: '<index>' => 'pstndid'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_pstndid($field, $input, $param = NULL) {
		$regex = '/^[0-9]+$/';
		$validation_failed = false;
		if (isset ( $input [$field] )) {
			if (is_array ( $input [$field] )) {
				foreach ( $input [$field] as $item ) {
					if (! preg_match ( $regex, $item )) {
						$validation_failed = true;
						break;
					}
				}
			} else {
				if (! preg_match ( $regex, $input [$field] )) {
					$validation_failed = true;
				}
			}
		}
		if ($validation_failed) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		} else {
			return;
		}
	}
	
	/**
	 * Determine if the input is an array containing numeric values
	 *
	 * Usage: '<index>' => 'numeric_array'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_numeric_array($field, $input, $param = NULL) {
		$regex = '/^[0-9]+$/';
		$validation_failed = false;
		if (isset ( $input [$field] )) {
			if (is_array ( $input [$field] )) {
				foreach ( $input [$field] as $item ) {
					if (! preg_match ( $regex, $item )) {
						$validation_failed = true;
						break;
					}
				}
			} else {
				$validation_failed = true;
			}
		}
		if ($validation_failed) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		} else {
			return;
		}
	}
	
	/**
	 * Determine if the provided value is enabled or disabled
	 *
	 * Usage: '<index>' => 'validate_enabled_disabled'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_enabled_disabled($field, $input, $param = NULL) {
		if (isset ( $input [$field] ) && (empty ( $input [$field] ) || ($input [$field] != 'enabled' && $input [$field] != 'disabled'))) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		} else {
			return;
		}
	}
	
	/**
	 * Determine if the provided value is a valid 12 hrs time
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_time($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		if (! isset ( $input [$field] ) || empty ( $input [$field] ) || ! preg_match ( "/^(2[0-3]|[01][0-9]):([0-5][0-9])$/", $input [$field] ) !== FALSE) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	/**
	 * Determine if the input containing valid ip address
	 *
	 * Usage: '<index>' => 'ip_address'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_ip_address($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! preg_match ( "/^(?=\d+\.\d+\.\d+\.\d+$)(?:(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])\.?){4}$/", $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the input containing valid ip address
	 *
	 * Usage: '<index>' => 'host_name'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_host_or_ip($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! preg_match ( "/^((http|https|ftp):\/\/)?(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9-]*[a-zA-Z0-9])\.)+(([A-Za-z]|[A-Za-z][A-Za-z0-9-])+([A-Za-z0-9]))$/", $input [$field] ) && ! preg_match ( "/^(?=\d+\.\d+\.\d+\.\d+$)(?:(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])\.?){4}$/", $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value is a valid userid for debug - user registration
	 *
	 * Usage: '<index>' => 'alpha_numeric_or_numeric'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_alpha_numeric_or_numeric($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (preg_match ( "/^[A-Za-z]+$/i", $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value is a valid for pstndid
	 *
	 * Usage: '<index>' => 'numeric_integer'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_numeric_integer($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! preg_match ( "/^[0-9]+$/i", $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value is a valid url characters
	 *
	 * Usage: '<index>' => 'valid_url_char'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_valid_url_char($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		if (! preg_match ( "/^(\/([\w#!:.?+=&%@!\-\/])+)$/i", $input [$field] )) {
			return array (
					'field' => $field,
					'value' => $input [$field],
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
	
	/**
	 * Determine if the provided value is a valid Array of Array
	 *
	 * Usage: '<index>' => 'validate_array_of_array'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @param null $param        	
	 * @return mixed
	 */
	protected function validate_array_of_array($field, $input, $param = NULL) {
		if (! isset ( $input [$field] ) || empty ( $input [$field] ) || ! is_array ( $input [$field] )) {
			return;
		}
		foreach ( $input [$field] as $array ) {
			if (! is_array ( $array )) {
				return array (
						'field' => $field,
						'value' => $input [$field],
						'rule' => __FUNCTION__,
						'param' => $param 
				);
			}
		}
	}
	
	/**
	 * Verify that a value is unique within the pre-defined value set.
	 * OUTPUT: will NOT show the list of values.
	 *
	 * Usage: '<index>' => 'unique,value;value;value'
	 *
	 * @access protected
	 * @param string $field        	
	 * @param array $input        	
	 * @return mixed
	 */
	protected function validate_unique($field, $input, $param = NULL) {
		$param = trim ( strtolower ( $param ) );
		
		if (! isset ( $input [$field] ) || empty ( $input [$field] )) {
			return;
		}
		
		$value = trim ( strtolower ( $input [$field] ) );
		
		$param = explode ( ";", $param );
		if (count ( $param ) == 0) {
			return;
		}
		
		if (! in_array ( $value, array_map ( 'strtolower', $param ) )) { // valid, return nothing
			return;
		} else {
			return array (
					'field' => $field,
					'value' => $value,
					'rule' => __FUNCTION__,
					'param' => $param 
			);
		}
	}
}
