<?php
namespace validator;

use utility\constant\CommonConstant;
use validator\Validator;
/**
 * ListValidator - for validating list fields
 * */

class ListValidator {

	// Method for validating input parameters for add user
	public static function validate_list($parameter_array, $action_type,$client=false) {
		$validator = new Validator();
		$contain_list='';
		if(isset($parameter_array['field_list']) && is_array($parameter_array['field_list'])){
			$contain_list=implode(';',$parameter_array['field_list']);
		}
		$validator->set_validation_rules(array(
			'page_size' => array(
						'get_list' => 'empty|min_numeric,1'
					),
			'start_index' => array(
						'get_list' => 'empty|min_numeric,0'
					),
			'include_count' => array(
						'get_list' => 'empty'
									. '|containsList,'.CommonConstant::API_PARAM_VALUE_TRUE.';'.CommonConstant::API_PARAM_VALUE_FALSE
					),
			CommonConstant::QUERY_PARAM_SORT_ORDER => array(
						'get_list' => 'empty'
						. '|containsList,'.CommonConstant::SORTING_ORDER_ASCENDING.';'.CommonConstant::SORTING_ORDER_DESCENDING
					),
			CommonConstant::QUERY_PARAM_LIMIT => array(
						'get_list' => 'empty|min_numeric,1'
				),
			CommonConstant::QUERY_PARAM_OFFSET => array(
						'get_list' => 'empty|min_numeric,0'
				),
			CommonConstant::QUERY_PARAM_SORT_BY => array(
						'get_list' => 'empty'
						. '|containsList,'.$contain_list
				),
			CommonConstant::QUERY_PARAM_FIELDS => array(
						'get_list' => 'empty'
						. '|fieldList,'.$contain_list
				),
			
		), $action_type);
		return $validator->run($parameter_array, FALSE);
		
	}
}