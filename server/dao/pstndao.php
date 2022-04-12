<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class PstnDao extends Dao {
	/* insert pstn */
	public static function create_pstn($connection, $pstn, $group_id) {
		$pstn_sql = "INSERT INTO `pstn` (`pstn_no`,`group_id`,`created_timestamp`,`last_updated_stamp`) VALUES (?,?,NOW(),NOW())";
		$pstn_stmt = $connection->prepare ( $pstn_sql );
		Dao::executeDMLQuery ( $connection, $pstn_sql, array (
				$pstn,
				$group_id 
		) );
		return $connection->lastInsertId ();
	}
	
	public static function get_pstn_details($connection,$pstn_no,$fields = '*'){
		$sql = "SELECT {$fields} FROM `pstn` WHERE pstn_no = ?";
		return Dao::getRow( $connection, $sql, array (
				$pstn_no
		) );
	}
	
	/* check duplicate pstn - create */
	public static function check_duplicate_pstn_for_create($connection, $pstn, $group_id) {
		$sql = "SELECT count(*) FROM `pstn` WHERE pstn_no = ? AND group_id = ?";
		return Dao::fetchColumn ( $connection, $sql, array (
				$pstn,
				$group_id 
		) );
	}
	
	/* check duplicate pstn - edit */
	public static function check_duplicate_pstn_name_for_edit($connection, $pstn_no, $new_pstn_no) {
		$sql = "SELECT count(*) FROM `pstn` WHERE (lower(pstn_no) = ?) AND (lower(pstn_no) <> ?)";
		return Dao::fetchColumn($connection, $sql,array($new_pstn_no,$pstn_no));
	}
	
	/* Delete pstn */
	public static function delete_pstn($connection, $pstn_id) {
		$sql = "DELETE FROM pstn WHERE pstn_id = ?";
		return Dao::executeDMLQuery($connection, $sql ,array($pstn_id));
	}
}
