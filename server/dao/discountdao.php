<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class DiscountDao extends Dao {

	/* get all discount list */
	public static function getAllDiscountDetails($connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $group_id, $isCount = false) {
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT d.`discount_id`, d.`discount_code`, d.`discount_name`, d.`discount_value` AS discount, IF((d.`is_valid` = 1),'yes','no') AS provisionable, d.`created_timestamp`,   d.`last_updated_stamp` FROM `discount` d where d.group_id = ?) AS temp";
		
		if (UtilityMethods::isNotEmpty ( $order_by ) && UtilityMethods::isNotEmpty ( $order_type )) {
			$sql .= "ORDER BY $order_by $order_type";
		}
		
		if (UtilityMethods::isNotEmpty ( $limit ) && UtilityMethods::isNotEmpty ( $offset )) {
			$sql .= "LIMIT $offset,$limit";
		}
		return Dao::getAll ( $connection, $sql, array($group_id));
	}
	


	/* get specific discount details */
	public static function getDiscountDetails($connection, $discount_name,$group_id, $fields = '*') {

		$param = array ();

		$sql = "SELECT {$fields} FROM (SELECT d.`discount_id`, d.`discount_code`, d.`discount_name`, d.`discount_value` AS discount, IF((d.`is_valid` = 1),'yes','no') AS provisionable, d.`created_timestamp`,   d.`last_updated_stamp` FROM `discount` d where d.group_id = ? and d.discount_name=?) AS temp";
		/* By default it executes as select statement without where condition  */
		return Dao::getRow ( $connection, $sql, array($group_id, $discount_name) );
	}
	
	
	/* insert group */
	public static function insertDiscountDetails($connection, $parameter_array) {
		$sql = "INSERT INTO `discount` (`discount_code`,`discount_name`,`discount_value`,`is_valid`,`group_id`";
		
		$param = array (
				$parameter_array ['discount_code'],
				$parameter_array ['discount_name'],
				$parameter_array ['discount_value'],
				$parameter_array ['provisionable'],  
				$parameter_array ['group_id']  
				//Need to pass as Foreign key from group table
		);
		
		$sql .= ",`created_timestamp`,`last_updated_stamp`)
					VALUES (?,?,?,?,?";

		$sql .= ",NOW(),NOW())";

		return Dao::executeDMLQuery($connection,$sql,$param);
	}

	/* delete discount */
	public static function deleteDiscount($connection, $discount_id,$group_id) {

		$param = array ();

		$sql = "DELETE FROM `discount` WHERE `discount_id` = ? AND `group_id` = ?";
		return Dao::executeDMLQuery ( $connection, $sql, array($discount_id, $group_id) );
	}
	

	/* get discount count */
	public static function getCount($connection,$group_id) {
		$count = self::getAllDiscountDetails ( $connection, null, null, null, 'COUNT(*) as count', null, null, null, $group_id,  true );
		return $count [0] ['count'];
	}

	/*  getGroupDetailsById  check duplicate discount name - create */
	public static function check_duplicate_discount_name_for_create($connection, $discount_name, $group_id) {
		$sql = "SELECT count(*) FROM `discount` WHERE (lower(discount_name) = ?) and group_id = ?";
		return self::fetchColumn ( $connection, $sql, array ( $discount_name,  $group_id) );
	}

	/* check duplicate discount name  - edit */
	public static function check_duplicate_discount_name_for_edit($connection, $discount_name, $new_discount_name, $group_id) {
		$sql = "SELECT count(*) FROM `discount` WHERE (lower(discount_name) = ?) AND (lower(discount_name) <> ?) and group_id = ?";
		return self::fetchColumn ( $connection,$sql, array (
				strtolower ( $new_discount_name ),
				strtolower ( $discount_name ) ,$group_id
		) );
	}	
}
