<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class ProductDao extends Dao {
	
	/* get all product list */
	public static function getAllProductDetails($connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $group_id, $isCount = true) {
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT p.`product_id`, p.`product_code`, p.`product_name`, p.`product_type`, 
				IF((p.`is_provisionable` = 1),'yes','no') AS provisionable , p.`varition_type`, p.`varaition_value`, 
				IF((p.`is_seasonable` = 1),'yes','no') AS seasonable, p.`created_timestamp`, p.`last_updated_stamp` FROM `product` p 
				where p.group_id = ? AND p.is_hidden = 0) AS temp";
		
		if (UtilityMethods::isNotEmpty ( $order_by ) && UtilityMethods::isNotEmpty ( $order_type )) {
			$sql .= "ORDER BY $order_by $order_type";
		}
		
		if (UtilityMethods::isNotEmpty ( $limit ) && UtilityMethods::isNotEmpty ( $offset )) {
			$sql .= "LIMIT $offset,$limit";
		}
		
		return Dao::getAll ( $connection, $sql, array (
				$group_id 
		), $param );
	}
	
	/* get specific price details */
	public static function getPriceDetails($connection, $product_name, $group_id, $fields = '*') {
		/*
		 * Need Appropriate Response message when trying to fetch soft deleted product name
		 * Currently returns -Product_details : false
		 * DO we need to hard code is_hidden value?
		 */
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT pr.`seller_price`, pr.`supplier_price`, pr.`seasonable_price`, 
				IF((pr.`is_updated` = 1),'yes','no') AS updated, pr.`created_timestamp`, pr.`last_updated_stamp`, p.group_id FROM `product_price_mapping` 
				pr, product p where pr.product_id =p.product_id AND p.product_name = ? and p.group_id = ? AND p.is_hidden = 0) AS temp";
		return Dao::getRow ( $connection, $sql, array (
				$product_name,
				$group_id 
		) );
	}
	
	/* get specific product details */
	public static function getProductDetails($connection, $product_name, $group_id, $fields = '*') {
		/*
		 * Need Appropriate Response message when trying to fetch soft deleted product name
		 * Currently returns -Product_details : false
		 */
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT p.`product_id`, p.`product_code`, p.`product_name`, p.`product_type`, 
				IF((p.`is_provisionable` = 1),'yes','no') AS provisionable , p.`varition_type`, p.`varaition_value`, 
				(SELECT discount_name from discount WHERE discount_id = p.`discount_id`) AS discount_name,
				IF((p.`is_seasonable` = 1),'yes','no') AS seasonable, p.`created_timestamp`, p.`last_updated_stamp` FROM `product` p 
				where p.product_name = ? and  p.group_id = ? AND p.is_hidden = 0 ) AS temp";
		
		return Dao::getRow ( $connection, $sql, array (
				$product_name,
				$group_id 
		) );
	}
	
	/* insert Product */
	public static function insertProductDetails($connection, $parameter_array) {
		$sql = "INSERT INTO `product` (`product_code`,`product_name`,`product_type`,`discount_id`, 
				is_provisionable, is_seasonable, varition_type, varaition_value, group_id,is_hidden ";
		$param = array (
				$parameter_array ['product_code'],
				$parameter_array ['product_name'],
				$parameter_array ['product_type'],
				$parameter_array ['discount_id'],
				$parameter_array ['provisionable'],
				$parameter_array ['seasonable'],
				$parameter_array ['varition_type'],
				$parameter_array ['varaition_value'],
				$parameter_array ['group_id'],
				$parameter_array ['is_hidden'] 
		);
		$sql .= ",`created_timestamp`,`last_updated_stamp`)
					VALUES (?,?,?,?,?,?,?,?,?,?";
		$sql .= ",NOW(),NOW())";
		$stmt = $connection->prepare ( $sql );
		$stmt->execute ( $param );
		
		$productid = $connection->lastInsertId ();
		$parameter_array ['seasonable_price'] == '0';
		
		$sql_price = "INSERT INTO `product_price_mapping` (product_id, seller_price, supplier_price, seasonable_price, 
							is_updated,created_timestamp,last_updated_stamp) VALUES (?,?,?,?,?,NOW(),NOW())";
		$stmt_price = $connection->prepare ( $sql_price );
		$stmt_price->execute ( array (
				$productid,
				$parameter_array ['seller_price'],
				$parameter_array ['supplier_price'],
				$parameter_array ['seasonable_price'],
				$parameter_array ['is_updated'] 
		) );
		
		$product_price_mapping_id = $connection->lastInsertId ();
		$sql_supplier = "INSERT INTO `supplier_mapping` (product_id, supplier_id,created_timestamp,last_updated_stamp) VALUES (?,?,NOW(),NOW())";
		$stmt_supplier = $connection->prepare ( $sql_supplier );
		$stmt_supplier->execute ( array (
				$productid,
				$parameter_array ['supplier_id'] 
		) );
	}
	
	/* get Product count */
	public static function getCount($connection, $group_id) { // Count Not Working in any module except group
		$count = self::getAllProductDetails ( $connection, null, null, null, 'COUNT(*) as count', null, null, null, $group_id, true );
		return $count [0] ['count'];
	}
	
	/* getProductDetailsById check duplicate product name - create */
	public static function check_duplicate_product_name_for_create($connection, $product_name, $group_id) {
		$sql = "SELECT count(*) FROM `product` WHERE (lower(product_name) = ?) and group_id = ?";
		return self::fetchColumn ( $connection, $sql, array (
				$product_name,
				$group_id 
		) );
	}
	
	/* getProductDetailsById check duplicate price name - create */
	public static function check_duplicate_price_name_for_create($connection, $product_name, $group_id) {
		$sql = "SELECT count(*) FROM `product` p WHERE (lower(p.product_name) = ?) and group_id = ?";
		
		return self::fetchColumn ( $connection, $sql, array (
				$product_name,
				$group_id 
		) );
	}
	
	/* check duplicate product name - aftr edit */
	public static function check_duplicate_product_name_for_edit($connection, $product_name, $new_product_name, $group_id) {
		$sql = "SELECT count(*) FROM `product` WHERE (lower(product_name) = ?) AND (lower(product_name) <> ?) and group_id = ?";
		return self::fetchColumn ( $connection, $sql, array (
				strtolower ( $new_product_name ),
				strtolower ( $product_name ),
				$group_id 
		) );
	}
	
	/* check discount dependency against product */
	public static function check_discount_dependency($connection, $discount_id, $group_id) {
		$sql = "SELECT count(*) FROM `product` WHERE discount_id = ? AND group_id = ? AND is_provisionable = 1 AND is_hidden = 0";
		return self::fetchColumn ( $connection, $sql, array (
				$discount_id ,
				$group_id
		) );
	}
}
