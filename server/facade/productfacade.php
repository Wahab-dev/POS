<?php

namespace facade;

use utility\constant\CommonConstant;
use utility\constant\PageSizeConstant;
use dao\ProductDao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;
use utility\constant\ApiResponseConstant;
use utility\DbConnector;
use dao\PstnDao;
use dao\Dao;
use dao\TenantDao;
use dao\DiscountDao;
use dao\SupplierDao;

class ProductFacade extends Facade {
	public function __construct() {
		$this->_errorLogger ( __CLASS__ );
	}

	/* for getting Product list*/
	public function getAllProductDetails($parameter_array) {
		$response_array = array ();
		try {
			$this->_debug("Module Product --> Get Product");
			$connection = DbConnector::getConnection ();
			$search_text = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SEARCH_KEYWORD, "" );
			$limit = UtilityMethods::getPageLimit ( $parameter_array, PageSizeConstant::GROUP_PAGE_LIMIT );
			$offset = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_OFFSET, 0 );
			$order_by = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_BY, '' );
			$order_type = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_SORT_ORDER, '' );
			$fields = UtilityMethods::getValueFromArray ( $parameter_array, CommonConstant::QUERY_PARAM_FIELDS, implode ( ',', ListFieldConstant::$product_fields ) );
			$this->_debug("Group ID ".$parameter_array['group_id']);
			$result_array = ProductDao::getAllProductDetails ( $connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $parameter_array['group_id'] );
			$response_array [CommonConstant::QUERY_PARAM_OFFSET] = $offset;
			$response_array ['product_details'] = $result_array;
			$response_array [CommonConstant::QUERY_PARAM_LIMIT] = $limit;
			$response_array [CommonConstant::QUERY_PARAM_COUNT] = ProductDao::getCount ( $connection , $parameter_array['group_id']);
			$this->_debug("Fetch All Product : ". count.$response_array ['product_details']);
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "get Product list failed", $e );
			}
			$this->_error("Empty Array ".$e);
			throw $e;
		}
	}
	 
	/* get indiviual price details */
	public function getPriceDetails($product_name,$group_id) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			if (UtilityMethods::isNotEmpty ( $product_name)) {
				$count = ProductDao::check_duplicate_price_name_for_create ( $connection, $product_name , $group_id);
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Price";
					return $response_array;
				}
			}
			$fields = implode ( ',', ListFieldConstant::$price_fields );
			$response_array = ProductDao::getPriceDetails ( $connection, $product_name,$group_id, $fields );
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "price failed", $e );
			}
			throw $e;
		}
	}
	
	/* get indiviual product details */
	public function getProductDetails($product_name,$group_id) { //group hard coded now
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
	
			if (UtilityMethods::isNotEmpty ( $product_name)) {
				$count = ProductDao::check_duplicate_product_name_for_create ( $connection, $product_name, $group_id );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Product";
					return $response_array;
				}
			}
	
			$fields = implode ( ',', ListFieldConstant::$product_details_fields );
	
			$response_array = ProductDao::getProductDetails ( $connection, $product_name,$group_id, $fields );
			$fieldsprice = implode ( ',', ListFieldConstant::$price_fields );
			$response_array['price'] = ProductDao::getPriceDetails( $connection, $product_name,$group_id, $fieldsprice );
	
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create product failed", $e );
			}
			throw $e;
		}
	}
	
	
	/* insert Product */
	public function createProduct($parameter_array) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
			if (UtilityMethods::isNotEmpty ( $parameter_array ['product_name'] )) {
				$count = ProductDao::check_duplicate_product_name_for_create ( $connection, $parameter_array ['product_name'], $parameter_array['group_id'] );
				if ($count > 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Product";
					return $response_array;
				}
			}
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['discount_name'] )) {
				$count = DiscountDao::check_duplicate_discount_name_for_create( $connection, $parameter_array ['discount_name'], $parameter_array['group_id'] );
				if ($count == 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Discount";
					return $response_array;
				}
				
				$parameter_array['discount_id'] = DiscountDao::getDiscountDetails($connection, $parameter_array ['discount_name'], $parameter_array ['group_id'],'discount_id');
				unset($parameter_array['discount_name']);
			}
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['supplier_name'] )) {
				$count = SupplierDao::check_duplicate_supplier_name_for_create( $connection, $parameter_array ['supplier_name'], $parameter_array['group_id'] );
				if ($count == 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Discount";
					return $response_array;
				}
			
				$parameter_array['supplier_id'] = SupplierDao::getSupplierDetails($connection, $parameter_array ['supplier_name'], $parameter_array ['group_id'],'supplier_id');
				unset($parameter_array['discount_name']);
			}
			
			if(isset($parameter_array['provisionable']) && UtilityMethods::isNotEmpty ( $parameter_array ['provisionable'] )){
				$parameter_array['provisionable'] = ($parameter_array['provisionable'] == 'yes')? 1: 0;
			}
			if(isset($parameter_array['seasonable']) && UtilityMethods::isNotEmpty ( $parameter_array ['seasonable'] )){
				$parameter_array['seasonable'] = ($parameter_array['seasonable'] == 'yes')? 1: 0;
			}
			if(isset($parameter_array['hidden']) && UtilityMethods::isNotEmpty ( $parameter_array ['hidden'] )){
				$parameter_array['hidden'] = ($parameter_array['hidden'] == 'yes')? 1: 0;
			}
			
			$product_id = ProductDao::insertProductDetails($connection,$parameter_array);
			
			if (UtilityMethods::isNotEmpty ( $product_id )) {
				$fields = implode ( ',', ListFieldConstant::$product_fields );
				return ProductDao::getProductDetails ( $connection, $parameter_array['product_name'],$parameter_array['group_id'],$fields );
			}
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "create Product failed", $e );
			}
			throw $e;
		}
	}
	

	/* update Product details */
	public function updateProduct($parameter_array) {
		try {
			$response_array = array ();
			$connection = DbConnector::getConnection ();
			if (UtilityMethods::isNotEmpty ( $parameter_array ['id'] ) ) {
				$count = ProductDao::check_duplicate_product_name_for_create( $connection, $parameter_array ['id'],$parameter_array ['group_id']  );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "product name";
					return $response_array;
				}
			}
				
			if (UtilityMethods::isNotEmpty ( $parameter_array ['id'] )) {
				$count = ProductDao::check_duplicate_product_name_for_edit ( $connection,  $parameter_array ['id'], $parameter_array ['product_name'], $parameter_array ['group_id'] );
	
				if ($count > 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
					$response_array [CommonConstant::ERROR_MESSAGE] = "product name";
					return $response_array;
				}
			}
			
			if (UtilityMethods::isNotEmpty ( $parameter_array ['discount_name'] )) {
				$count = DiscountDao::check_duplicate_discount_name_for_create( $connection, $parameter_array ['discount_name'], $parameter_array['group_id'] );
				if ($count = 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Discount";
					return $response_array;
				}
				
				$parameter_array['discount_id'] = DiscountDao::getDiscountDetails($connection, $parameter_array ['discount_name'], $parameter_array ['group_id'],'discount_id');
				unset($parameter_array['discount_name']);
			}
				
			$product_info = ProductDao::getProductDetails ( $connection, $parameter_array ['id'], $parameter_array ['group_id']);
			$primary_key_details = self::build_primarykey ( $product_info );
			$update_details = self::build_updateArray ( $parameter_array );

			$response_array = Dao::updateBasedOnGivenKey ( $connection, 'product', $primary_key_details, $update_details );
			if (UtilityMethods::isNotEmpty ( $parameter_array ['seller_price'] ) ) { // Need  a method to catch error
				$update_Pricedetails = self::build_updatePriceArray ( $parameter_array );
				$update_Supplierdetails = self::build_updateSuppArray ( $parameter_array );
				$response_array = Dao::updateBasedOnGivenKey ( $connection, 'product_price_mapping', $primary_key_details, $update_Pricedetails );
				$response_array = Dao::updateBasedOnGivenKey ( $connection, 'supplier_mapping', $primary_key_details, $update_Supplierdetails );
					
			}
			//Need to Implement Committ Roll back method
			return $response_array;
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "Update Product failed", $e );
			}
			throw $e;
		}
	}
	
	/* delete Product */
	public function deleteProduct($parameter_array) {
		try {
			$connection = DbConnector::getConnection ();
			$response_array = array ();
				
			if (UtilityMethods::isNotEmpty ( $parameter_array ['product_name'] )) {
				$count =ProductDao::check_duplicate_product_name_for_create ( $connection, $parameter_array ['product_name'], $parameter_array['group_id'] );
				if ($count <= 0) {
					$response_array [CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
					$response_array [CommonConstant::ERROR_MESSAGE] = "Product";
					return $response_array;
				}
			}
			$product_info = ProductDao::getProductDetails($connection,$parameter_array['product_name'],$parameter_array['group_id'],"product_id");
			$primary_key_details = self::build_primarykey ( $product_info );
			$update_details = self::build_hideArray ( $parameter_array );
			$response_array = Dao::updateBasedOnGivenKey ( $connection, 'product', $primary_key_details, $update_details );
			return $response_array;
			//return ProductDao::deleteProduct($connection,$product_info['product_id'],$parameter_array['group_id']);
	
		} catch ( \Exception $e ) {
			$response_array ["STATUS"] = "ERROR";
			$response_array ["MESSAGE"] = $e->getMessage ();
			if ($this->_isErrorEnabled ()) {
				$this->_error ( $this->_getVarDump ( $response_array ) . "Delete Product failed", $e );
			}
			throw $e;
		}
	}
	
	
	public function build_primarykey($parameter_array) {
		$primary_details = array ();
		if (isset ( $parameter_array ['product_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['product_id'] )) {
			$primary_details ['product_id'] = $parameter_array ['product_id'];
		}
		return $primary_details;
	}
	
	
	public function build_updateArray($parameter_array) {
		$response_array = array ();
	
		if (isset ( $parameter_array ['product_name'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['product_name'] )) {
			$response_array ['product_name'] = $parameter_array ['product_name'];
		}
		if (isset ( $parameter_array ['product_code'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['product_code'] )) {
			$response_array ['product_code'] = $parameter_array ['product_code'];
		}
		if (isset ( $parameter_array ['product_type'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['product_type'] )) {
			$response_array ['product_type'] = $parameter_array ['product_type'];
		}
	
		if (isset ( $parameter_array ['provisionable'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['provisionable'] )) {
			$response_array['is_provisionable'] =($parameter_array['provisionable'] == 'yes')? 1: 0;
		}
	
		if (isset ( $parameter_array ['varition_type'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['varition_type'] )) {
			$response_array ['varition_type'] = $parameter_array ['varition_type'];
		}
		if (isset ( $parameter_array ['varaition_value'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['varaition_value'] )) {
			$response_array ['varaition_value'] = $parameter_array ['varaition_value'];
		}
		if (isset ( $parameter_array ['seasonable'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['seasonable'] )) {
			$response_array['is_seasonable'] =($parameter_array['seasonable'] == 'yes')? 1: 0;
		}
		if (isset ( $parameter_array ['group_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['group_id'] )) {
			$response_array ['group_id'] = $parameter_array ['group_id'];
		}
		if (isset ( $parameter_array ['discount_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['discount_id'] )) {
			$response_array ['discount_id'] = $parameter_array ['discount_id'];
		}
		if (isset ( $parameter_array ['hidden'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['hidden'] )) {
			$response_array['is_hidden'] =($parameter_array['hidden'] == 'yes')? 1: 0;
		}
		return $response_array;
	}
	
	public function build_updatePriceArray($parameter_array) {
		$response_array = array ();
		if (isset ( $parameter_array ['seller_price'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['seller_price'] )) {
			$response_array['seller_price'] =$parameter_array['seller_price'];
		}
		if (isset ( $parameter_array ['supplier_price'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['supplier_price'] )) {
			$response_array['supplier_price'] =$parameter_array['supplier_price'];
		}
		if (isset ( $parameter_array ['seasonable_price'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['seasonable_price'] )) {
			$response_array['seasonable_price'] =$parameter_array['seasonable_price'];
		}
		if (isset ( $parameter_array ['is_updated'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['is_updated'] )) {
			$response_array['is_updated'] =($parameter_array['is_updated'] == 'yes')? 1: 0;
		}
		return $response_array;
	}
	
	public function build_updateSuppArray($parameter_array) {
		$response_array = array ();
		if (isset ( $parameter_array ['supplier_id'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['supplier_id'] )) {
			$response_array['supplier_id'] =$parameter_array['supplier_id'];
		}
		return $response_array;
	}
	
	public function build_hideArray($parameter_array) {
		$response_array = array ();
		if (isset ( $parameter_array ['hidden'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['hidden'] )) {
			$response_array['is_hidden'] =$parameter_array['hidden'];
		}
		return $response_array;
	}
}
