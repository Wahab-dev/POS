<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class GroupDao extends Dao {
	public static function getAllGroupDetails($connection, $offset, $limit, $search_text, $fields, $order_by, $order_type) {
		$param = array ();
		$sql = "SELECT s.special_discount_id AS special_discount, s.special_discount_name AS discount_name,s.discount_code, (SELECT user_name FROM `user` WHERE user_id = s.user_id ) AS user_name FROM `special_discount` s WHERE s.group_id = 1";
		
		if (UtilityMethods::isNotEmpty ( $order_by ) && UtilityMethods::isNotEmpty ( $order_type )) {
			$sql .= "ORDER BY $order_by $order_type";
		}
		
		if (UtilityMethods::isNotEmpty ( $limit ) && UtilityMethods::isNotEmpty ( $offset )) {
			$sql .= "LIMIT $offset,$limit";
		}
		return Dao::getAll ( $connection, $sql, $param );
	}
}