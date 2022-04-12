<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;
use utility\constant\CommonConstant;

class SupplierDao extends Dao {
	
	/* get all supplier list */
	public static function getAllSupplierDetails($connection, $offset, $limit, $search_text, $fields, $order_by, $order_type, $group_id) {
		$param = array ();
		$sql = "SELECT {$fields} FROM (SELECT s.supplier_id, s.supplier_name, s.supplier_attn AS supplier_attention, (SELECT pstn_no FROM pstn WHERE pstn_id = s.supplier_phno) AS phone_number,
			IF((s.supplier_alt_no IS NOT NULL) ,(SELECT pstn_no FROM pstn WHERE pstn_id = s.supplier_alt_no), '')AS alternate_number, s.supplier_email AS email_address,
			s.supplier_address_line_1 AS address_line_1, IF((s.supplier_address_line_2 IS NOT NULL),s.supplier_address_line_2, '') AS address_line_2,
			s.created_timestamp, s.last_updated_stamp FROM supplier_details s WHERE s.group_id = ?) AS temp";
		
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
	
	/* get supplier count */
	public static function getCount($connection, $group_id) {
		$count = self::getAllSupplierDetails ( $connection, null, null, null, 'COUNT(*) as count', null, null, $group_id );
		return $count [0] ['count'];
	}
	
	/* get supplier outstanding balance */
	public static function get_outstanding_balance($connection, $supplier_id, $group_id) {
		$amount_paid = 0;
		$total1 = 0;
		$return_total = 0;
		/* get accepted order total */
		$sql = "SELECT SUM(order_total) as order_total FROM `supplier_order_details` WHERE supplier_id = ? AND order_status = '".CommonConstant::ORDER_STATUS_CONFIRMED."'";
		$total1 += Dao::fetchColumn ( $connection, $sql, array (
				$group_id 
		) );
		
		$sql = "SELECT SUM(amount_paid) AS amount_paid FROM `supplier_order_details` WHERE supplier_id = ? AND order_status = '".CommonConstant::ORDER_STATUS_CONFIRMED."'";
		$amount_paid = Dao::fetchColumn ( $connection, $sql, array (
				$group_id 
		) );
		
		/* get return total */
		$sql = "SELECT SUM(order_total) AS order_total FROM `supplier_order_details` WHERE supplier_id = ? AND order_status = '".CommonConstant::ORDER_STATUS_RETURNED."'";
		$return_total += Dao::fetchColumn( $connection, $sql, array (
				$group_id 
		) );
		
		$outstanding_balance = $amount_paid  - ($total1 + $return_total);
		return $outstanding_balance;
	}
	
	/* get specific supplier details */
	public static function getSupplierDetails($connection, $supplier_name, $group_id, $fields = '*') {
		$param = array ();
		
		$sql = "SELECT {$fields} FROM (SELECT s.supplier_id, s.supplier_name, s.supplier_attn AS supplier_attention, 
				(SELECT pstn_no FROM pstn WHERE pstn_id = s.supplier_phno) AS phone_number,IF((s.supplier_alt_no IS NOT NULL) ,
				(SELECT pstn_no FROM pstn WHERE pstn_id = s.supplier_alt_no), '')AS alternate_number, s.supplier_email AS 
				email_address,s.supplier_address_line_1 AS address_line_1, IF((s.supplier_address_line_2 IS NOT NULL),
				s.supplier_address_line_2, '') AS address_line_2, s.bank_info, s.created_timestamp, s.last_updated_stamp FROM supplier_details s 
				WHERE s.group_id = ? AND s.supplier_name = ?) AS temp";
		
		return Dao::getRow ( $connection, $sql, array (
				$group_id,
				$supplier_name
		) );
	}
	
	/* insert group */
	public static function insertSupplierDetails($connection, $parameter_array) {
		$subsql = "";
		$sql = "INSERT INTO `supplier_details` (supplier_name,supplier_attn,supplier_phno,supplier_address_line_1,bank_info,group_id,
				created_timestamp,last_updated_stamp,created_timestamp,last_updated_stamp"; 
		
		$param = array (
				$parameter_array ['supplier_name'],
				$parameter_array ['supplier_attention'],
				$parameter_array ['phone_number'],
				$parameter_array ['address_line_1'],
				$parameter_array ['bank_info'],
				$parameter_array ['group_id']
		);
		
		if(isset($parameter_array['alternate_number']) && UtilityMethods::isNotEmpty($parameter_array['alternate_number'])){
			$sql .= ",supplier_alt_no";
			$param[] = $parameter_array['alternate_number'];
			$subsql .= ",?";
		}
		
		if(isset($parameter_array['email_address']) && UtilityMethods::isNotEmpty($parameter_array['email_address'])){
			$sql .= ",supplier_email";
			$param[] = $parameter_array['email_address'];
			$subsql .= ",?";
		}
		
		if(isset($parameter_array['address_line_2']) && UtilityMethods::isNotEmpty($parameter_array['address_line_2'])){
			$sql .= ",supplier_address_line_2";
			$param[] = $parameter_array['address_line_2'];
			$subsql .= ",?";
		}
			
		$sql .= ")VALUES (?,?,?,?,?,?,NOW(),NOW()".$subsql.")";
		
		Dao::executeDMLQuery ( $connection, $sql, $param );
		return $connection->lastInsertId();
	}
	
	/* delete discount */
	public static function deleteSupplier($connection, $discount_id, $group_id) {
		$param = array ();
		
		$sql = "DELETE FROM `supplier_details` WHERE `supplier_id` = ? AND `group_id` = ?";
		return Dao::executeDMLQuery ( $connection, $sql, array (
				$discount_id,
				$group_id 
		) );
	}
		
	/* check duplicate supplier name - create */
	public static function check_duplicate_supplier_name_for_create($connection, $supplier_name, $group_id) {
		$sql = "SELECT count(*) FROM `supplier_details` WHERE (lower(supplier_name) = ?) and group_id = ?";
		return self::fetchColumn ( $connection, $sql, array (
				$supplier_name,
				$group_id 
		) );
	}
	
	/* check duplicate supplier name - edit */
	public static function check_duplicate_supplier_name_for_edit($connection, $supplier_name, $new_supplier_name, $group_id) {
		$sql = "SELECT count(*) FROM `supplier_details` WHERE (lower(supplier_name) = ?) AND (lower(supplier_name) <> ?) and group_id = ?";
		return self::fetchColumn ( $connection, $sql, array (
				strtolower ( $new_supplier_name ),
				strtolower ( $supplier_name ),
				$group_id 
		) );
	}
}
