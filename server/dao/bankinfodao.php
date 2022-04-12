<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class BankinfoDao extends Dao {
	
	/* get bank details */
	public static function get_bank_info_details($connection, $account_number, $group_id) {
		$sql = "SELECT bank_name, bank_branch, bank_code, account_number, account_type, created_timestamp, last_updated_stamp FROM bank_info 
				WHERE account_number = ? AND group_id = ?";
		return Dao::getRow ( $connection, $sql, array (
				$account_number,
				$group_id 
		) );
	}
	
	/* create bank info */
	public static function create_bank_info($connection, $parameter) {
		$param = array (
				$parameter ['bank_name'],
				$parameter ['bank_branch'],
				$parameter ['bank_code'],
				$parameter ['account_number'],
				$parameter ['account_type'],
				$parameter ['group_id'] 
		);
		$sql = "INSERT INTO bank_info (bank_name, bank_branch, bank_code, account_number, account_type, group_id, created_timestamp, 
				last_updated_stamp) VALUES (?,?,?,?,?,?,NOW(),NOW())";
		Dao::executeDMLQuery ( $connection, $sql, $param );
		return $connection->lastInsertId();
	}
	
	/* delete bank info */
	public static function delete_bank_info($connection, $account_number, $group_id) {
		$sql = "DELETE FROM bank_info WHERE account_number = ? AND group_id = ?";
		return Dao::executeDMLQuery ( $connection, $sql, array (
				$account_number,
				$group_id 
		) );
	}
	
	/* check bank info exist */
	public static function check_bank_info_exist($connection, $account_number, $group_id) {
		$sql = "SELECT COUNT(*) AS `count` FROM bank_info WHERE account_number = ? AND group_id = ?";
		return Dao::executeDMLQuery ( $connection, $sql, array (
				$bank_id,
				$group_id 
		) );
	}
	
	/* check duplicate for create */
	public static function check_duplicate_for_create($connection, $account_number, $group_id) {
		$sql = "SELECT COUNT(*) AS `count` FROM bank_info WHERE account_number = ? AND group_id = ?";
		return Dao::fetchColumn ( $connection, $sql, array (
				$account_number,
				$group_id 
		) );
	}
	
	/* check duplicate for edit */
	public static function check_duplicate_for_edit($connection, $id, $account_number, $group_id) {
		$sql = "SELECT COUNT(*) AS `count` FROM bank_info WHERE account_number = ? AND account_number <> ? AND group_id = ?";
		return Dao::fetchColumn ( $connection, $sql, array (
				$account_number,
				$id,
				$group_id 
		) );
	}
	
	/* get bank info by ID */
	public static function get_bank_info_by_id($connection,$bank_info_id,$group_id){
		$sql = "SELECT bank_name, bank_branch, bank_code, account_number, account_type, created_timestamp, last_updated_stamp FROM bank_info
				WHERE bank_info_id = ? AND group_id = ?";
		return Dao::getRow ( $connection, $sql, array (
				$bank_info_id,
				$group_id
		) );
	}
}	
