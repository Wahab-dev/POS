<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class GroupDao extends Dao {
	/* get all group details */
	public static function getAllGroupDetails($connection, $offset, $limit, $search_text, $fields, $order_by, $order_type) {
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT g.`group_id`, g.`group_name`,g.`group_attn` AS group_attention, g.`group_address_line_1` 
				AS address_line_1, IF( (g.`group_address_line_2` IS NOT NULL),g.`group_address_line_2`, '')  AS address_line_2, 
				g.group_type, IF((g.parent_group_id IS NOT NULL),g.parent_group_id,'') as parent_group_id,
				g.`group_email` AS email_address,g.`created_timestamp`,g.`last_updated_stamp` FROM `group` g) AS temp";
		
		if (UtilityMethods::isNotEmpty ( $order_by ) && UtilityMethods::isNotEmpty ( $order_type )) {
			$sql .= "ORDER BY $order_by $order_type";
		}
		
		if (UtilityMethods::isNotEmpty ( $limit ) && UtilityMethods::isNotEmpty ( $offset )) {
			$sql .= "LIMIT $offset,$limit";
		}
		return Dao::getAll ( $connection, $sql, $param );
	}
	
	/* get group count */
	public static function getCount($connection) {
		$count = self::getAllGroupDetails ( $connection, null, null, null, 'COUNT(*) as count', null, null );
		return $count [0] ['count'];
	}
	
	/* get group specific details */
	public static function getGroupDetails($connection, $group_name, $fields = '*') {
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT g.`group_id`, g.`group_name`, g.`group_attn` AS group_attention, g.`group_address_line_1` AS address_line_1, 
				IF( (g.`group_address_line_2` IS NOT NULL),g.`group_address_line_2`, '') AS address_line_2,g.`group_email` AS email_address, 
				g.group_type, IF((g.parent_group_id IS NOT NULL),g.parent_group_id,'') as parent_group_id,g.`created_timestamp`,g.`last_updated_stamp` 
				FROM `group` g";
		if (isset ( $group_name )) {
			$sql .= " WHERE g.group_name = ?)";
			$param [] = $group_name;
		}
		$sql .= " AS temp";
		return Dao::getRow ( $connection, $sql, $param );
	}
	
	/* get group specific details by id */
	public static function getGroupDetailsById($connection, $group_id, $fields = '*') {
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT g.`group_id`, g.`group_name`, g.`group_attn` AS group_attention, g.`group_address_line_1` AS address_line_1, 
				IF( (g.`group_address_line_2` IS NOT NULL),g.`group_address_line_2`, '') AS address_line_2,g.`group_email` AS email_address,
				g.group_type, IF((g.parent_group_id IS NOT NULL),g.parent_group_id,'') as parent_group_id, g.`created_timestamp`,
				g.`last_updated_stamp` FROM `group` g";
		if (isset ( $group_id )) {
			$sql .= " WHERE g.group_id = ?)";
			$param [] = $group_id;
		}
		$sql .= " AS temp";
		
		return Dao::getRow ( $connection, $sql, $param );
	}
	
	/* insert group */
	public static function insertGroupDetails($connection, $parameter_array) {
		$sql = "INSERT INTO `group` (`group_name`,`group_email`,`group_attn`,`group_address_line_1`,";
		
		$param = array (
				$parameter_array ['group_name'],
				$parameter_array ['email_address'],
				$parameter_array ['group_attention'],
				$parameter_array ['address_line_1'] 
		);
		
		if (isset($parameter_array ['parent_group_id']) && UtilityMethods::isNotEmpty ( $parameter_array ['parent_group_id'] )) {
			$sql .= "`parent_group_id`,";
			$param [] = $parameter_array ['parent_group_id'];
		}
		
		if (isset($parameter_array ['group_type']) && UtilityMethods::isNotEmpty ( $parameter_array ['group_type'] )) {
			$sql .= "`group_type`,";
			$param [] = $parameter_array ['group_type'];
		}
		
		if (isset($parameter_array ['address_line_2']) && UtilityMethods::isNotEmpty ( $parameter_array ['address_line_2'] )) {
			$sql .= "`address_line_2`,";
			$param [] = $parameter_array ['address_line_2'];
		}
		
		$sql .= "`created_timestamp`,`last_updated_stamp`)
					VALUES (?,?,?,?";

		if (isset($parameter_array ['group_type']) && UtilityMethods::isNotEmpty ( $parameter_array ['group_type'] )) {
			$sql .= ",?";
		}
		
		if (isset($parameter_array ['parent_group_id']) && UtilityMethods::isNotEmpty ( $parameter_array ['parent_group_id'] )) {
			$sql .= ",?";
		}
		
		if (isset($parameter_array ['address_line_2']) && UtilityMethods::isNotEmpty ( $parameter_array ['address_line_2'] )) {
			$sql .= ",?";
		}
		
		$sql .= ",NOW(),NOW())";
		
		$stmt = $connection->prepare ( $sql );
		$stmt->execute ( $param );
		
		$last_inserted_id = $connection->lastInsertId ();
		return $last_inserted_id;
	}
	
	/* for pstn */
	public static function insertPstn($connection, $pstn,$group_id,$fields){
		if (UtilityMethods::isNotEmpty ( $pstn) && UtilityMethods::isNotEmpty($group_id) && UtilityMethods::isNotEmpty($fields)) {
			$pstn_id = PstnDao::group_pstn_create ( $connection, $pstn, $group_id );
			$group_querry = "UPDATE `group` SET ".$fields." = ? where group_id = ?";
			$grp_stmt = $connection->prepare ( $group_querry );
			$grp_stmt->execute ( array (
					$pstn_id,
					$group_id
			) );
		}
	}
	
	/* delete group */
	public static function deleteGroupDetails($connection, $group_id) {
		$sql = "DELETE From `group` where group_id = ?";
		return Dao::executeDMLQuery( $connection,$sql, array (
				$group_id 
		) );
	}
	
	/* check duplicate name - create */
	public static function check_duplicate_group_name_for_create($connection, $group_name) {
		$sql = "SELECT count(*) FROM `group` WHERE (lower(group_name) = ?)";
		return self::fetchColumn ( $connection, $sql, array ( $group_name ) );
	}
	
	/* check duplicate name - edit */
	public static function check_duplicate_group_name_for_edit($connection, $group_name, $new_group_name) {
		$sql = "SELECT count(*) FROM `group` WHERE (lower(group_name) = ?) AND (lower(group_name) <> ?)";
		return self::fetchColumn ( $connection,$sql, array (
				strtolower ( $new_group_name ),
				strtolower ( $group_name ) 
		) );
	}
}
