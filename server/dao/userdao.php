<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class UserDao extends Dao {
	/* get Access level inuse */
	public static function get_access_level_inuse($connection, $access_level_id, $group_id) {
		$sql = "SELECT COUNT(*) FROM `user` WHERE `access_level_id` = ? AND `group_id` = ?";
		return Dao::fetchColumn ( $connection, $sql, array (
				$access_level_id,
				$group_id 
		) );
	}
	
	/* get users count */
	public static function get_count($connection, $group_id) {
		$count = self::getAllUserDetails ( $connection, null, null, null, 'COUNT(*) as count', null, null, $group_id );
		return $count [0] ['count'];
	}
	
	/* get all user details */
	public static function getAllUserDetails($connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $group_id) {
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT u.`user_id`, u.`first_name`, IF((u.`last_name` IS NOT NULL), u.`last_name`, '') AS last_name, u.`user_name`,
				u.`user_address_line_1` AS address_line_1, IF( (u.`user_address_line_2` IS NOT NULL),u.`user_address_line_2`, '')  AS address_line_2,
				IF((u.`user_phno` IS NOT NULL), (SELECT `pstn_no` FROM pstn where pstn_id = u.`user_phno`), '') AS phone_number,
				IF((u.`user_alt_no` IS NOT NULL), (SELECT `pstn_no` FROM pstn where pstn_id = u.`user_alt_no`), '') AS alternate_number,
				u.`user_email` AS email_address,(SELECT `access_level_name` FROM access_level WHERE `access_level_id` = u.`access_level_id` ) AS access_level_name,
				u.`is_active`,u.`created_timestamp`,u.`last_updated_stamp` FROM `user` u WHERE u.`group_id` = ? ) AS temp";
		
		if (UtilityMethods::isNotEmpty ( $order_by ) && UtilityMethods::isNotEmpty ( $order_type )) {
			$sql .= "ORDER BY $order_by $order_type";
		}
		
		if (UtilityMethods::isNotEmpty ( $limit ) && UtilityMethods::isNotEmpty ( $offset )) {
			$sql .= "LIMIT $offset,$limit";
		}
		return Dao::getAll ( $connection, $sql, array (
				$group_id 
		) );
	}
	
	/* get user details */
	public static function getUserDetails($connection, $user_name, $group_id, $fields = '*') {
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT u.`user_id`, u.`first_name`, IF((u.`last_name` IS NOT NULL), u.`last_name`, '') AS last_name,u.`user_name`,
		u.`user_address_line_1` AS address_line_1, IF( (u.`user_address_line_2` IS NOT NULL),u.`user_address_line_2`, '')  AS address_line_2,
		IF((u.`user_phno` IS NOT NULL), (SELECT `pstn_no` FROM pstn where pstn_id = u.`user_phno`), '') AS phone_number,
		IF((u.`user_alt_no` IS NOT NULL), (SELECT `pstn_no` FROM pstn where pstn_id = u.`user_alt_no`), '') AS alternate_number,
		u.`user_email` AS email_address,(SELECT `access_level_name` FROM access_level WHERE `access_level_id` = u.`access_level_id` ) AS access_level_name,
		(SELECT GROUP_CONCAT(permission_name) FROM permissions WHERE permission_id IN (SELECT permission_id FROM `access_permission_mapping` 
		WHERE access_level_id = u.`access_level_id` )) AS access_permissions,u.`is_active`,
		u.`created_timestamp`,u.`last_updated_stamp` FROM `user` u WHERE u.`user_name` = ? AND u.`group_id` = ?) AS temp";
		
		return Dao::getRow ( $connection, $sql, array (
				$user_name,
				$group_id 
		) );
	}
	
	/* get user details */
	public static function create_user($connection, $parameter_array, $group_id) {
		$sql = "INSERT INTO `user` (first_name,user_name,`password`,user_phno,user_address_line_1,access_level_id,created_timestamp,last_updated_stamp,is_active";
		$sub_sql = "";
		$param = array (
				$parameter_array ['first_name'],
				$parameter_array ['user_name'],
				$parameter_array ['password'],
				$parameter_array ['phone_number'],
				$parameter_array ['address_line_1'],
				$parameter_array ['access_level_id'] 
		);
		if (isset ( $parameter_array ['last_name'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['last_name'] )) {
			$sql .= ",last_name";
			$sub_sql .= ",?";
			$param [] = $parameter_array ['last_name'];
		}
		
		if (isset ( $parameter_array ['alternate_number'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['alternate_number'] )) {
			$sql .= ",user_alt_no";
			$sub_sql .= ",?";
			$param [] = $parameter_array ['alternate_number'];
		}
		
		if (isset ( $parameter_array ['email_address'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['email_address'] )) {
			$sql .= ",user_email";
			$sub_sql .= ",?";
			$param [] = $parameter_array ['email_address'];
		}
		
		if (isset ( $parameter_array ['address_line_2'] ) && UtilityMethods::isNotEmpty ( $parameter_array ['address_line_2'] )) {
			$sql .= ",user_address_line_2";
			$sub_sql .= ",?";
			$param [] = $parameter_array ['address_line_2'];
		}
		
		$sql .= ",group_id) VALUES (?,?,?,?,?,?,NOW(),NOW(),1" . $sub_sql . ",?)";
		$param [] = $group_id;
		
		return Dao::executeDMLQuery ( $connection, $sql, $param );
	}
	
	/* delete user */
	public static function deleteUserDetails($connection, $user_name, $group_id) {
		$sql = "UPDATE FROM `user` SET is_active = 0 where user_name = ? AND group_id = ? ";
		return Dao::executeDMLQuery ( $connection, $sql, array (
				$user_name,
				$group_id 
		) );
	}
	
	/* check duplicate name - create */
	public static function check_duplicate_user_name_for_create($connection, $user_name, $group_id) {
		$sql = "SELECT count(*) FROM `user` WHERE user_name = ? AND group_id = ?";
		return self::fetchColumn ( $connection, $sql, array (
				$user_name,
				$group_id 
		) );
	}
	
	/* check duplicate name - edit */
	public static function check_duplicate_user_name_for_edit($connection, $user_name, $new_user_name, $group_id) {
		$sql = "SELECT count(*) FROM `user` WHERE user_name = ? AND user_name <> ? AND group_id = ? AND is_active = 1";
		return self::fetchColumn ( $connection, $sql, array (
				$new_user_name,
				$user_name,
				$group_id 
		) );
	}
	
	/* user authentication */
	public static function authenticate_user($connection, $user_name, $password, $group_id) {
		$sql = "SELECT count(*) FROM `user` WHERE user_name = ? AND password = ? AND group_id = ? AND is_active = 1";
		return self::fetchColumn ( $connection, $sql, array (
				$user_name,
				$password,
				$group_id 
		) );
	}
}
