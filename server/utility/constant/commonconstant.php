<?php

namespace utility\constant;

class CommonConstant {
	const ERROR_CODE = 'error_code';
	const ERROR_MESSAGE = 'error_message';
	const MESSAGE_TYPE_ERROR = "ERROR";
	const MESSAGE_TYPE_SUCCESS = "SUCCESS";
	const MESSAGE_TYPE_WARNING = "WARNING";
	const RESPONSE_TYPE_SUCCESS = 'success';
	const RESPONSE_TYPE_FAILURE = 'failure';
	const RESPONSE_TYPE_WARNING = 'warning';
	const RESPONSE_SUCCESS_CODE = '0000';
	const RESPONSE_SUCCESS_MESSAGE = 'Ok';
	
	const QUERY_PARAM_SEARCH_KEYWORD = 'search_text';
	const QUERY_PARAM_OFFSET = 'offset';
	const QUERY_PARAM_LIMIT = 'limit';
	const QUERY_PARAM_COUNT = 'count';
	const QUERY_PARAM_SORT_BY = 'sort_by';
	const QUERY_PARAM_SORT_ORDER = 'sort_order';
	const QUERY_PARAM_FIELDS = "fields";
	
	const GROUP_TYPE_TENANT = "TENANT";
	const GROUP_TYPE_GROUP = "GROUP";
	
	const ACTION_TYPE_ADD = 'add';
	const ACTION_TYPE_EDIT = 'edit';
	const ACTION_TYPE_OTHER = 'other';
	const ACTION_TYPE_GET_LIST = 'get_list';
	const ACTION_TYPE_GET_ITEM = 'get_item';
	const ACTION_TYPE_DELETE = 'delete';
	const ACTION_TYPE_GET_COUNT = 'get_count';
	const ACTION_TYPE_RENAME = 'rename';
	const ACTION_TYPE_DELETE_MULTIPLE = 'delete-multiple';

	const API_PARAM_VALUE_TRUE = "true";
	const API_PARAM_VALUE_FALSE = "false";
	const SORTING_ORDER_ASCENDING = "ASC";
	const SORTING_ORDER_DESCENDING = "DESC";
	const YES = "yes";
	const NO = "no";
	const ENABLE = "enabled";
	const DISABLE = "disabled";
	
	const API_SCREEN_NAME_PLACE_HOLDER = '<<Resource>>';
	const CONTACT_SUPPORT_MSG = 'Please contact support.';
	
	const ORDER_STATUS_PLACED = "PLACED";
	const ORDER_STATUS_CONFIRMED = "CONFIRMED";
	const ORDER_STATUS_REJECTED = "REJECTED";
	const ORDER_STATUS_ACCEPTED = "ACCEPTED";
	const ORDER_STATUS_RETURNED = "RETURNED";
	
	/* default access level name */
	const DEFAULT_ACCESS_LEVEL_NAME = "Manager";
	
	const DEFAULT_USER_NAME = "Manager";
}
