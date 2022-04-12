<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;
use utility\constant\CommonConstant;
use utility\constant\PermissionConstant;

class AccesslevelDao extends Dao {
	/* insert accesslevel */
	public static function create_access_level($connection, $parameter, $group_id, $group_type) {
		$sql = "INSERT INTO `access_level`(access_level_name,group_id,created_timestamp,last_updated_stamp) VALUES (?, ?, NOW(),NOW())";
		$stmt = $connection->prepare ( $sql );
		$stmt->execute(array($parameter['access_level_name'], $group_id));
		$last_inserted_id = $connection->lastInsertId();
		
		// for inserting access permission.
		$permissions = $parameter['permissions'];
		$sql = "INSERT INTO `access_permission_mapping` (access_level_id,permission_id,is_active,created_time_stamp,last_updated_stamp) VALUES (?,?,?,NOW(),NOW())";
		$stmt = $connection->prepare ( $sql );
		if ($group_type != CommonConstant::GROUP_TYPE_GROUP) $permissions['super_admin'] = CommonConstant::NO;
		$stmt->execute(array($last_inserted_id, PermissionConstant::SUPER_ADMIN, $permissions['super_admin'] == CommonConstant::YES ? 1 : 0));
		$stmt->execute(array($last_inserted_id, PermissionConstant::ADMIN, $permissions['admin'] == CommonConstant::YES ? 1 : 0));
		$stmt->execute(array($last_inserted_id, PermissionConstant::MANAGER, $permissions['manager'] == CommonConstant::YES ? 1 : 0));
		$stmt->execute(array($last_inserted_id, PermissionConstant::EMPLOYEE, $permissions['employee'] == CommonConstant::YES ? 1 : 0));
		$stmt->execute(array($last_inserted_id, PermissionConstant::CUSTOMER, $permissions['customer'] == CommonConstant::YES ? 1 : 0));
		return $last_inserted_id;	
	}
	
	/* update accesslevel */
	public static function update_access_level($connection, $parameter) {
		
		if(isset($parameter['access_level_name']) && UtilityMethods::isNotEmpty($parameter['access_level_name'])){
			$sql = "UPDATE `access_level` SET `access_level_name` = ? WHERE `access_level_name` = ? AND `group_id` = ?";
			Dao::executeDMLQuery($connection, $sql, array($parameter['access_level_name'], $parameter['id'], $parameter['group_id']));
		}
	
		// for inserting access permission.
		$permissions = $parameter['permissions'];
		$sql = "UPDATE `access_permission_mapping` SET is_active = ? WHERE `access_level_id`= ? AND `permission_id` = ?";
		if ($parameter['group_type'] != CommonConstant::GROUP_TYPE_GROUP) $permissions['super_admin'] = CommonConstant::NO;
		$stmt = $connection->prepare ( $sql );
		$stmt->execute(array($permissions['super_admin'] == CommonConstant::YES ? 1 : 0, $parameter['access_level_id'], PermissionConstant::SUPER_ADMIN));
		$stmt->execute(array($permissions['admin'] == CommonConstant::YES ? 1 : 0, $parameter['access_level_id'], PermissionConstant::ADMIN));
		$stmt->execute(array($permissions['manager'] == CommonConstant::YES ? 1 : 0, $parameter['access_level_id'], PermissionConstant::MANAGER));
		$stmt->execute(array($permissions['employee'] == CommonConstant::YES ? 1 : 0, $parameter['access_level_id'], PermissionConstant::EMPLOYEE));
		$stmt->execute(array($permissions['customer'] == CommonConstant::YES ? 1 : 0, $parameter['access_level_id'], PermissionConstant::CUSTOMER));
	}
	
	/* get group count */
	public static function getCount($connection, $group_id) {
		$count = self::get_all_access_level( $connection, null, null, null, 'COUNT(*) as count', null, null, $group_id );
		return $count [0] ['count'];
	}
		
	/* get all access level */
	public static function get_all_access_level($connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $group_id) {
		$sql = "SELECT {$fields} FROM (SELECT access_level_name AS access_level_name, (SELECT GROUP_CONCAT(permission_name) FROM `permissions` WHERE permission_id IN 
				(SELECT permission_id FROM `access_permission_mapping` WHERE access_level_id = a.access_level_id AND is_active = 1)) 
				 AS access_permissons, created_timestamp, last_updated_stamp FROM `access_level` a WHERE a.group_id = ?) AS temp ";
		
		if (UtilityMethods::isNotEmpty ( $order_by ) && UtilityMethods::isNotEmpty ( $order_type )) {
			$sql .= "ORDER BY $order_by $order_type";
		}
		
		if (UtilityMethods::isNotEmpty ( $limit ) && UtilityMethods::isNotEmpty ( $offset )) {
			$sql .= "LIMIT $offset,$limit";
		}
		
		return Dao::getAll ( $connection, $sql, array($group_id));
	}
	
	/* get indiviual access level details */
	public static function get_access_level_details($connection, $access_level_name, $group_id, $fields = '*') {
		$sql = "SELECT {$fields} FROM (SELECT access_level_id, access_level_name AS access_level_name, (SELECT GROUP_CONCAT(permission_name) FROM 
		permissions WHERE permission_id IN (SELECT permission_id FROM `access_permission_mapping` WHERE access_level_id = 
		(SELECT access_level_id FROM access_level WHERE access_level_name = ? AND group_id = ?))) AS access_permissons, created_timestamp, 
		last_updated_stamp FROM `access_level` a WHERE a.group_id = ? and a.access_level_name = ?) AS temp ";
		return Dao::getRow( $connection, $sql, array($access_level_name,$group_id,$group_id,$access_level_name) );
	} 
	
	/* delete access level */
	public static function deleteAccessLevel($connection, $access_level_name, $group_id) {
		$sql = "DELETE FROM `access_level` WHERE access_level_name =  ? AND group_id = ? ";
		return Dao::executeDMLQuery ( $connection, $sql, array (
				$access_level_name,
				$group_id 
		) );
	}
	
	/* check duplicate name - create */
	public static function check_duplicate_acess_level_name_for_create($connection, $access_level_name, $group_id) {
		$sql = "SELECT count(*) FROM `access_level` WHERE (lower(access_level_name) = ?) AND `group_id` = ?";
		return self::fetchColumn ( $connection, $sql, array ( $access_level_name ,$group_id) );
	}
	
	/* check duplicate name - edit */
	public static function check_duplicate_acess_level_name_for_edit($connection, $access_level_name, $new_access_level_name, $group_id) {
		$sql = "SELECT count(*) FROM `access_level` WHERE (lower(access_level_name) = ?) AND (lower(access_level_name) <> ?) AND `group_id` = ?";
		return self::fetchColumn ( $connection,$sql, array (
				strtolower ( $new_access_level_name ),
				strtolower ( $access_level_name ),
				$group_id
		) );
	}
}
