<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class OrderDao extends Dao {
	/* get order count */
	public static function get_count($connection, $group_id) {
		$count = self::getAllOrderDetails ( $connection, null, null, null, 'COUNT(*) as count', null, null, $group_id );
		return $count [0] ['count'];
	}
	
	/* get all order details */
	public static function getAllOrderDetails($connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $group_id) {
		$param = array ();
		
		$sql = "SELECT {$fields} FROM ( SELECT o.`order_id` AS order_number, LOWER(o.`order_status`) AS status, o.`order_total`, 
				IF((o.`special_discount_name` IS NOT NULL), o.`special_discount_name`, '') AS special_discount_name,  
				IF((o.`special_discount_value` IS NOT NULL),o.`special_discount_value`,'') AS special_discount_value, 
				(SELECT user_name FROM `user` WHERE user_id = o.`user_id`) AS `user`, o.created_timestamp, o.last_updated_stamp 
		FROM `order` o WHERE group_id = ?) AS temp";
		
		if (UtilityMethods::isNotEmpty ( $order_by ) && UtilityMethods::isNotEmpty ( $order_type )) {
			$sql .= " ORDER BY $order_by $order_type";
		}
		
		if (UtilityMethods::isNotEmpty ( $limit ) && UtilityMethods::isNotEmpty ( $offset )) {
			$sql .= " LIMIT $offset,$limit";
		}
		
		return Dao::getAll ( $connection, $sql, array (
				$group_id 
		) );
	}
	
	/* get order details */
	public static function getOrderDetails($connection, $order_number, $group_id, $fields = '*') {
		$param = array ();
		$sql = "SELECT {$fields} FROM ( SELECT o.`order_id` AS order_number, LOWER(o.`order_status`) AS status, o.`order_total`, 
				IF((o.`special_discount_name` IS NOT NULL), o.`special_discount_name`, '') AS special_discount_name,  
				IF((o.`special_discount_value` IS NOT NULL),o.`special_discount_value`,'') AS special_discount_value, 
				(SELECT user_name FROM `user` WHERE user_id = o.`user_id`) AS `user`, o.created_timestamp, o.last_updated_stamp 
				FROM `order` o WHERE o.group_id = ? and o.order_id = ?) AS temp";
				
		return Dao::getRow ( $connection, $sql, array (
				$group_id,
				$order_number
		) );
	}
	
	public static function getOrderMappingDetails($connection, $order_id) {
		$param = array ();
		$sql = "SELECT * FROM ( SELECT (SELECT product_name FROM product WHERE product_id = om.`product_id`) AS product_name, om.`product_price`, 
				om.`quantity`, IF((om.`discount_name` IS NOT NULL), om.`discount_name`,'') AS discount_name,
				IF((om.`discount_value` IS NOT NULL), om.`discount_value`, '') AS discount_value,om.`created_timestamp`,om.`last_updated_stamp` 
				FROM `order_mapping` om WHERE om.`order_id` = ?) AS temp ";
		
		return Dao::getAll ( $connection, $sql, array (
				$order_id 
		) );
	}
	
	/* create order */
	public static function create_order($connection, $parameter_array) {
		$sql = "INSERT INTO `order` (order_status,`order_total`,user_id,group_id,created_timestamp,last_updated_stamp";
		$sub_sql = "";
		$param = array (
				$parameter_array ['order_status'],
				$parameter_array ['order_total'],
				$parameter_array ['user_id'],
				$parameter_array ['group_id'] 
		);
		
		if (isset ( $parameter_array ['special_discount_name'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['special_discount_name'] )) {
			$sql .= ",special_discount_name";
			$sub_sql .= ",?";
			$param [] = $parameter_array ['special_discount_name'];
		}
		
		if (isset ( $parameter_array ['special_discount_value'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['special_discount_value'] )) {
			$sql .= ",special_discount_value";
			$sub_sql .= ",?";
			$param [] = $parameter_array ['special_discount_value'];
		}
		
		$sql .= ") VALUES (?,?,?,?,NOW(),NOW()" . $sub_sql . ")";
		
		Dao::executeDMLQuery ( $connection, $sql, $param );
		
		$last_inserted_id = $connection->lastInsertId ();
		
		foreach ( $parameter_array ['products'] as $product ) {
			$sub_sql = "";
			$order_mapping_sql = "INSERT INTO `order_mapping` (order_id,product_id,product_price,quantity,created_timestamp,last_updated_stamp";
			$param = array (
					$last_inserted_id,
					$product ['product_id'],
					$product ['price'],
					$product ['quantity'] 
			);
			
			if (isset ( $product ['discount_name'] ) && UtilityMethods::isNotEmpty ( $product ['discount_name'] )) {
				$order_mapping_sql .= ",discount_name";
				$sub_sql .= ",?";
				$param [] = $product ['discount_name'];
			}
			
			if (isset ( $product ['discount_value'] ) && UtilityMethods::isNotEmpty ( $product ['discount_value'] )) {
				$order_mapping_sql .= ",discount_value";
				$sub_sql .= ",?";
				$param [] = $product ['discount_value'];
			}
			$order_mapping_sql .= ") VALUES (?,?,?,?,NOW(),NOW()" . $sub_sql . ")";
			Dao::executeDMLQuery ( $connection, $order_mapping_sql, $param );
		}
		
		return $last_inserted_id;
	}
	
	/* update order */
	public static function updateOrder($connection, $parameter_array){
		$sql = "UPDATE `order` SET order_total = ?";
		$param = array($parameter_array['order_total']);
		if(isset($parameter_array['special_discount_name']) && isset($parameter_array['special_discount_value'])){
			$sql .= ",special_discount_name = ?, special_discount_value = ?";
			$param[] = $parameter_array['special_discount_name'];
			$param[] = $parameter_array['special_discount_value'];
		}
		$sql .= 'WHERE order_id = ? AND group_id = ?';
		$param[] = $parameter_array['order_id'];
		$param[] = $parameter_array['group_id'];
		
		return Dao::executeDMLQuery($connection, $sql, $param);
	}
	
	/* update order status */
	public static function updateOrderStatus($connection, $order_number, $group_id, $status){
		$sql = "UPDATE `order` SET order_status = ? WHERE order_id =? AND group_id = ?";
		return Dao::executeDMLQuery($connection, $sql, array($status,$order_number,$group_id));
	}
	
	/* for updating order mappings */
	public static function updateOrderMapping($connection, $parameter_array) {
		$sql = "UPDATE `order_mapping` SET product_price = ?, quantity = ?, last_updated_stamp = NOW()";
		$param = array (
				$parameter_array ['price'],
				$parameter_array ['quantity'] 
		);
		if (isset ( $parameter_array ['discount_name'] ) && isset ( $parameter_array ['discount_value'] )) {
			$sql .= ', discount_name = ?, discount_value= ?';
			$param [] = $parameter_array ['discount_name'];
			$param [] = $parameter_array ['discount_value'];
		}
		$sql .= 'WHERE order_id = ? AND product_id = ?';
		$param [] = $parameter_array ['order_id'];
		$param [] = $parameter_array ['product_id'];
		return Dao::executeDMLQuery ( $connection, $sql, $param );
	}
	
	/* for creating order mappings */
	public static function createOrderMapping($connection, $parameter_array) {
		$sql = "INSERT INTO `order_mapping` (order_id,product_id,product_price,quantity,created_timestamp,last_updated_stamp";
		$sub_sql = "";
		$param = array (
				$parameter_array ['order_id'],
				$parameter_array ['product_id'],
				$parameter_array ['price'],
				$parameter_array ['quantity'] 
		);
		if (isset ( $parameter_array ['discount_name'] ) && isset ( $parameter_array ['discount_value'] )) {
			$sql .= ",discount_name,discount_value";
			$sub_sql = ",?,?";
			$param [] = $parameter_array ['discount_name'];
			$param [] = $parameter_array ['discount_value'];
		}
		$sql .= ") VALUES (?,?,?,?,NOW(),NOW()" . $sub_sql . ")";
		
		return Dao::executeDMLQuery ( $connection, $sql, $param );
	}
	
	/* for deleting order mappings */
	public static function deleteOrderMapping($connection, $order_id, $product_id) {
		$sql = "DELETE FROM `order_mapping` WHERE order_id = ? AND product_id = ?";
		return Dao::executeDMLQuery($connection, $sql, array($order_id, $product_id));
	}
	
	/* for getting order mapping product details */
	public static function getOrderProductDetails($connection, $order_id, $fields = '*') {
		$sql = "SELECT $fields FROM order_mapping WHERE order_id = ?";
		return Dao::getSingleColumnAsArray( $connection, $sql, array (
				$order_id 
		) );
	}
	
	/* check order */
	public static function check_order_exist($connection, $order_no, $group_id) {
		$sql = "SELECT count(*) FROM `order` WHERE order_id = ? AND group_id = ?";
		return self::fetchColumn ( $connection, $sql, array (
				$order_no,
				$group_id 
		) );
	}
}	
