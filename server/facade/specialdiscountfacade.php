<?php
namespace facade;

use utility\constant\CommonConstant;
use utility\constant\PageSizeConstant;
use dao\GroupDao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;
use utility\DbConnector;

class GroupFacade extends Facade {
	public function __construct() {
		$this->_errorLogger ( __CLASS__ );
	}

	/* get group details */
	public function getAllGroupDetails($parameter_array) {
		$response_array = array ();
		try {
			$connection = DbConnector::getConnection ();
			$search_text = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SEARCH_KEYWORD, "" );
			$limit = UtilityMethods::getPageLimit ( $parameter_array, PageSizeConstant::SPECIAL_DISCOUNT_LIMIT);
			$offset = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_OFFSET, 0 );
			$order_by = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_BY, '' );
			$order_type = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_ORDER, '' );
			$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$specialdiscount_fields) );
				
			$result_array = GroupDao::getAllGroupDetails ( $connection, $offset, $limit, $search_text, $fields, $order_by, $order_type );
			$response_array [CommonConstant::QUERY_PARAM_OFFSET] = $offset;
			$response_array ['group_details'] = $result_array;
			$response_array [CommonConstant::QUERY_PARAM_LIMIT] = $limit;
			$response_array [CommonConstant::QUERY_PARAM_COUNT] = GroupDao::getCount ( $connection );
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "get group list failed", $e );
			}
			throw $e;
		}
	}
}