<?php

namespace facade;

use utility\constant\CommonConstant;
use utility\constant\PageSizeConstant;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;
use utility\constant\ApiResponseConstant;
use utility\DbConnector;
use dao\ProductDao;
use dao\SupplierorderDao;

class SupplierorderFacade extends Facade {
	public function __construct() {
		$this->_errorLogger ( __CLASS__ );
	}
	
	/* get order details */
	public function getAllOrderDetails($parameter_array) {
		$response_array = array ();
		try {
			$connection = DbConnector::getConnection ();
			$search_text = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SEARCH_KEYWORD, "" );
			$limit = UtilityMethods::getPageLimit ( $parameter_array, PageSizeConstant::SUPPLIER_ORDER_PAGE_LIMIT );
			$offset = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_OFFSET, 0 );
			$order_by = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_BY, 'order_number' );
			$order_type = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_ORDER, CommonConstant::SORTING_ORDER_ASCENDING );
			$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$supplier_order_fields ) );
			
			$result_array = SupplierorderDao::getAllOrderDetails ( $connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $parameter_array ['group_id'], $parameter_array ['supplier_id'] );
			if (UtilityMethods::isNotEmpty ( $result_array )) {
				for($i = 0; $i < sizeof ( $result_array ); $i ++) {
					$result_array [$i] ['order_number'] = UtilityMethods::format_order_number ( $result_array [$i] ['order_number'], $parameter_array ['supplier_id'] );
				}
			}
			
			$response_array ['order_details'] = $result_array;
			$response_array [CommonConstant::QUERY_PARAM_OFFSET] = $offset;
			$response_array [CommonConstant::QUERY_PARAM_LIMIT] = $limit;
			$response_array [CommonConstant::QUERY_PARAM_COUNT] = SupplierorderDao::get_count ( $connection, $parameter_array ['group_id'], $parameter_array ['supplier_id'] );
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
	
	/* create order */
	public function createOrder($parameter_array) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			$productFacade = new ProductFacade ();
			$order_array = array ();
			$orderTotal = 0;
			
			/* check product exist */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['product_details'] )) {
				foreach ( $parameter_array ['product_details'] as $product ) {
					$count = ProductDao::check_duplicate_product_name_for_create ( $connection, $product ['product_name'], $parameter_array ['group_id'] );
					if ($count == 0) {
						$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
						$response_array [CommonConstant::ERROR_MESSAGE] = $product ['product_name'];
						return $response_array;
					}
					
					$product_details = $productFacade->getProductDetails ( $product ['product_name'], $parameter_array ['group_id'] );
					$product_id = ProductDao::getProductDetails ( $connection, $product ['product_name'], $parameter_array ['group_id'], 'product_id' );
					$product_id = $product_id ['product_id'];
					if (isset ( $product_details ['provisionable'] ) && UtilityMethods::isEqual ( $product_details ['provisionable'], CommonConstant::NO )) {
						$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::PRODUCT_NON_PROVISIONABLE;
						$response_array [CommonConstant::ERROR_MESSAGE] = " " . $product ['product_name'] . " ";
						return $response_array;
					}
					
					$product_price = $product_details ['price'] ['seller_price'];
					$order_array ['products'] [] = array (
							'product_id' => $product_id,
							'quantity' => $product ['quantity'] 
					);
					
					$orderTotal += $product ['quantity'] * $product_price;
				}
			}
			$order_array ['order_total'] = $orderTotal;
			$order_array ['order_status'] = CommonConstant::ORDER_STATUS_ACCEPTED;
			$order_array ['group_id'] = $parameter_array ['group_id'];
			$order_array ['user_id'] = $parameter_array ['user_id'];
			
			$order_id = SupplierorderDao::create_order ( $connection, $order_array );
			
			if (UtilityMethods::isNotEmpty ( $order_id )) {
				$order_number = UtilityMethods::format_order_number ( $order_id, $parameter_array ['group_id'] );
				return $this->getOrderDetails ( $order_number, $order_array ['group_id'] );
			}
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create group failed", $e );
			}
			throw $e;
		}
	}
	
	/* update order details */
	public function updateOrder($parameter_array) {
		try {
			$response_array = array ();
			$productFacade = new ProductFacade ();
			$connection = DbConnector::getConnection ();
			
			$temp = explode ( "-", $parameter_array ['order_number'] );
			$parameter_array ['order_number'] = $temp [0];
			
			/* check order exist */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['order_number'] )) {
				$count = SupplierorderDao::check_order_exist ( $connection, $parameter_array ['order_number'], $parameter_array ['group_id'] );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "order";
					return $response_array;
				}
			}
			
			if (isset ( $order_details ['status'] ) && ! UtilityMethods::isEqual ( $order_details ['status'], CommonConstant::ORDER_STATUS_ACCEPTED )) {
				$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::ORDER_EXPIRED;
				return $response_array;
			}
			
			$order_array = array ();
			$orderTotal = 0;
			
			/* check product exist */
			if (UtilityMethods::isNotEmpty ( $parameter_array ['product_details'] )) {
				$order_productid = SupplierorderDao::getOrderProductDetails ( $connection, $parameter_array ['order_number'], 'product_id' );
				foreach ( $parameter_array ['product_details'] as $product ) {
					$count = ProductDao::check_duplicate_product_name_for_create ( $connection, $product ['product_name'], $parameter_array ['group_id'] );
					if ($count == 0) {
						$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
						$response_array [CommonConstant::ERROR_MESSAGE] = $product ['product_name'];
						return $response_array;
					}
					$product_id = ProductDao::getProductDetails ( $connection, $product ['product_name'], $parameter_array ['group_id'], 'product_id' );
					$product_id = $product_id ['product_id'];
					
					$product_details = $productFacade->getProductDetails ( $product ['product_name'], $parameter_array ['group_id'] );
					if (isset ( $product_details ['provisionable'] ) && UtilityMethods::isEqual ( $product_details ['provisionable'], CommonConstant::NO )) {
						$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::PRODUCT_NON_PROVISIONABLE;
						$response_array [CommonConstant::ERROR_MESSAGE] = $product ['product_name'];
						return $response_array;
					}
					
					$product_price = $product_details ['price'] ['seller_price'];
					
					$product_mapping_details = array (
							'order_id' => $parameter_array ['order_number'],
							'product_id' => $product_id,
							'quantity' => $product ['quantity'] 
					);
					
					if (in_array ( $product_id, $order_productid )) {
						SupplierorderDao::updateOrderMapping ( $connection, $product_mapping_details );
					} else {
						SupplierorderDao::createOrderMapping ( $connection, $product_mapping_details );
					}
					unset ( $order_productid [array_search ( $product_id, $order_productid )] );
				
					$orderTotal += $product ['quantity'] * $product_price;
				}
				
				if (count ( $order_productid ) > 0) {
					foreach ( $order_productid as $product_id ) {
						SupplierorderDao::deleteOrderMapping ( $connection, $parameter_array ['order_number'], $product_id );
					}
				}
			}
			
			$order_array ['order_total'] = $orderTotal;
			$order_array ['order_id'] = $parameter_array ['order_number'];
			$order_array ['group_id'] = $parameter_array ['group_id'];
			$order_array ['user_id'] = $parameter_array ['user_id'];
			
			$response_array = SupplierorderDao::updateOrder ( $connection, $order_array );
			
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create group failed", $e );
			}
			throw $e;
		}
	}
	
	/* get order details */
	public function getOrderDetails($order_number, $group_id) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			$temp = explode ( "-", $order_number );
			$order_number = $temp [0];
			
			if (UtilityMethods::isNotEmpty ( $order_number )) {
				$count = SupplierorderDao::check_order_exist ( $connection, $order_number, $group_id );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "order";
					return $response_array;
				}
			}
			
			$fields = implode ( ',', ListFieldConstant::$supplier_order_fields );
			$response_array = SupplierorderDao::getOrderDetails ( $connection, $order_number, $group_id, $fields );
			$response_array ['order_details'] = SupplierorderDao::getOrderMappingDetails ( $connection, $order_number );
			
			if (UtilityMethods::isNotEmpty ( $response_array )) {
				$response_array ['order_number'] = UtilityMethods::format_order_number ( $response_array ['order_number'], $temp [1] );
			}
			
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "fetch user details failed", $e );
			}
			throw $e;
		}
	}
	
	/* get order status */
	public function updateOrderStatus($order_number, $group_id, $status) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			
			$temp = explode ( "-", $order_number );
			$order_number = $temp [0];
			
			if (UtilityMethods::isNotEmpty ( $order_number )) {
				$count = SupplierorderDao::check_order_exist ( $connection, $order_number, $group_id );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "order";
					return $response_array;
				}
				
				$order_details = SupplierorderDao::getOrderDetails ( $connection, $order_number, $group_id );
				
				if (isset ( $order_details ['status'] ) && UtilityMethods::isNotEqual ( $order_details ['status'], CommonConstant::ORDER_STATUS_ACCEPTED, true )) {
					if (UtilityMethods::isEqual ( $order_details ['status'], CommonConstant::ORDER_STATUS_CONFIRMED, true )) {
						$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::ORDER_PRODUCT_CONFIRMED;
					} else if (UtilityMethods::isEqual ( $order_details ['status'], CommonConstant::ORDER_STATUS_RETURNED, true )) {
						$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::ORDER_PRODUCT_RETURNED;
					} else {
						$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::ORDER_PRODUCT_REJECTED;
					}
					return $response_array;
				}
			}
			
			if (UtilityMethods::isEqual ( $status, CommonConstant::ORDER_STATUS_CONFIRMED, true )) {
				return $order_id = SupplierorderDao::updateOrderStatus ( $connection, $order_number, $group_id, CommonConstant::ORDER_STATUS_CONFIRMED );
			} else if (UtilityMethods::isEqual ( $status, CommonConstant::ORDER_STATUS_REJECTED, true )) {
				return $order_id = SupplierorderDao::updateOrderStatus ( $connection, $order_number, $group_id, CommonConstant::ORDER_STATUS_REJECTED );
			} else if (UtilityMethods::isEqual ( $status, CommonConstant::ORDER_STATUS_RETURNED, true )) {
				return $order_id = SupplierorderDao::updateOrderStatus ( $connection, $order_number, $group_id, CommonConstant::ORDER_STATUS_RETURNED );
			}
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "fetch user details failed", $e );
			}
			throw $e;
		}
	}
}
