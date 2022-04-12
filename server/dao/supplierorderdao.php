<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class SupplierorderDao extends Dao {
	/* get order count */
	public static function get_count($connection, $group_id, $supplier_id) {
		$count = self::getAllOrderDetails ( $connection, null, null, null, 'COUNT(*) as count', null, null, $group_id, $supplier_id);
		return $count [0] ['count'];
	}
	
	/* get all order details */
	public static function getAllOrderDetails($connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $group_id, $supplier_id) {
		$param = array ();
		
		$sql = "SELECT {$fields} FROM ( SELECT o.`order_id` AS order_number, LOWER(o.`order_status`) AS status, o.`order_total`, 
				o.`amount_paid`, (SELECT user_name FROM `user` WHERE user_id = o.`user_id`) AS `user`, o.created_timestamp, 
				o.last_updated_stamp FROM `supplier_order_details` o WHERE group_id = ? and supplier_id = ?) AS temp";
		
		if (UtilityMethods::isNotEmpty ( $order_by ) && UtilityMethods::isNotEmpty ( $order_type )) {
			$sql .= " ORDER BY $order_by $order_type";
		}
		
		if (UtilityMethods::isNotEmpty ( $limit ) && UtilityMethods::isNotEmpty ( $offset )) {
			$sql .= " LIMIT $offset,$limit";
		}
		
		return Dao::getAll ( $connection, $sql, array (
				$group_id,
				$supplier_id
		) );
	}
	
	/* get order details */
	public static function getOrderDetails($connection, $order_number, $group_id, $fields = '*') {
		$param = array ();
		$sql = "SELECT {$fields} FROM ( SELECT o.`order_id` AS order_number, LOWER(o.`order_status`) AS status, o.`order_total`, 
				o.`amount_paid`,(SELECT user_name FROM `user` WHERE user_id = o.`user_id`) AS `user`, o.created_timestamp, 
				o.last_updated_stamp FROM `supplier_order_details` o WHERE o.group_id = ? and o.order_id = ?) AS temp";
		
		return Dao::getRow ( $connection, $sql, array (
				$group_id,
				$order_number 
		) );
	}
	
	public static function getOrderMappingDetails($connection, $order_id) {
		$param = array ();
		$sql = "SELECT * FROM ( SELECT (SELECT product_name FROM product WHERE product_id = om.`product_id`) AS product_name,  
				om.`quantity`, om.`created_timestamp`,om.`last_updated_stamp` FROM `supplier_order_mapping` om WHERE om.`order_id` = ?) AS temp";
		
		return Dao::getAll ( $connection, $sql, array (
				$order_id 
		) );
	}
	
	/* create order */
	public static function create_order($connection, $parameter_array) {
		$sql = "INSERT INTO `supplier_order_details` (order_status,`order_total`,user_id,group_id,created_timestamp,last_updated_stamp";
		$sub_sql = "";
		$param = array (
				$parameter_array ['order_status'],
				$parameter_array ['order_total'],
				$parameter_array ['user_id'],
				$parameter_array ['group_id'] 
		);
		
		$sql .= ") VALUES (?,?,?,?,NOW(),NOW())";
		
		Dao::executeDMLQuery ( $connection, $sql, $param );
		
		$last_inserted_id = $connection->lastInsertId ();
		
		foreach ( $parameter_array ['products'] as $product ) {
			$sub_sql = "";
			$order_mapping_sql = "INSERT INTO `supplier_order_mapping` (order_id,product_id,quantity,created_timestamp,last_updated_stamp";
			$param = array (
					$last_inserted_id,
					$product ['product_id'],
					$product ['quantity'] 
			);
			
			$order_mapping_sql .= ") VALUES (?,?,?,NOW(),NOW())";
			Dao::executeDMLQuery ( $connection, $order_mapping_sql, $param );
		}
		
		return $last_inserted_id;
	}
	
	/* update order */
	public static function updateOrder($connection, $parameter_array) {
		$sql = "UPDATE `supplier_order_details` SET order_total = ? WHERE order_id = ? AND group_id = ?";
		return Dao::executeDMLQuery ( $connection, $sql, array (
				$parameter_array ['order_total'],
				$parameter_array ['order_id'],
				$parameter_array ['group_id'] 
		) );
	}
	
	/* update order status */
	public static function updateOrderStatus($connection, $order_number, $group_id, $status) {
		$sql = "UPDATE `supplier_order_details` SET order_status = ? WHERE order_id =? AND group_id = ?";
		return Dao::executeDMLQuery ( $connection, $sql, array (
				$status,
				$order_number,
				$group_id 
		) );
	}
	
	/* for updating order mappings */
	public static function updateOrderMapping($connection, $parameter_array) {
		$sql = "UPDATE `supplier_order_mapping` SET quantity = ?, last_updated_stamp = NOW() WHERE order_id = ? AND product_id = ?";
		
		return Dao::executeDMLQuery ( $connection, $sql, array (
				$parameter_array ['quantity'],
				$parameter_array ['order_id'],
				$parameter_array ['product_id'] 
		) );
	}
	
	/* for creating order mappings */
	public static function createOrderMapping($connection, $parameter_array) {
		$sql = "INSERT INTO `supplier_order_mapping` (order_id,product_id,quantity,created_timestamp,last_updated_stamp";
		$sub_sql = "";
		$param = array (
				$parameter_array ['order_id'],
				$parameter_array ['product_id'],
				$parameter_array ['quantity'] 
		);
		
		$sql .= ") VALUES (?,?,?,?,NOW(),NOW())";
		return Dao::executeDMLQuery ( $connection, $sql, $param );
	}
	
	/* for deleting order mappings */
	public static function deleteOrderMapping($connection, $order_id, $product_id) {
		$sql = "DELETE FROM `supplier_order_mapping` WHERE order_id = ? AND product_id = ?";
		return Dao::executeDMLQuery ( $connection, $sql, array (
				$order_id,
				$product_id 
		) );
	}
	
	/* for getting order mapping product details */
	public static function getOrderProductDetails($connection, $order_id, $fields = '*') {
		$sql = "SELECT $fields FROM supplier_order_mapping WHERE order_id = ?";
		return Dao::getSingleColumnAsArray ( $connection, $sql, array (
				$order_id 
		) );
	}
	
	/* check order */
	public static function check_order_exist($connection, $order_no, $group_id) {
		$sql = "SELECT count(*) FROM `supplier_order_details` WHERE order_id = ? AND group_id = ?";
		return self::fetchColumn ( $connection, $sql, array (
				$order_no,
				$group_id 
		) );
	}
}	
