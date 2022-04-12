<?php
namespace facade;

use utility\constant\CommonConstant;
use utility\constant\PageSizeConstant;
use dao\GroupDao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;
use utility\constant\ApiResponseConstant;
use utility\DbConnector;
use dao\PstnDao;
use dao\Dao;
use dao\TenantDao;
use dao\BankinfoDao;

class BankinfoFacade extends Facade {
	public function __construct() {
		$this->_errorLogger ( __CLASS__ );
	}

	/* get details */
	public function get_bank_info($account_number, $group_id){
		$connection = DbConnector::getConnection();
		$respone_array = array();
		$exist_count = BankinfoDao::check_bank_info_exist($connection, $account_number, $group_id);
		if($exist_count <= 0){
			$respone_array[CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
			$respone_array[CommonConstant::ERROR_MESSAGE] = $account_number;
		}
		
		$respone_array = BankinfoDao::get_bank_info_details($connection, $account_number, $group_id);
		return $respone_array;
	}
	
	/* create bank info */
	public function create_bank_info($parameter){
		$connection = DbConnector::getConnection();
		$respone_array = array();
	
		$exist_count = BankinfoDao::check_duplicate_for_create($connection,$parameter['account_number'], $parameter['group_id']);
		if($exist_count > 0){
			$respone_array[CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
			$respone_array[CommonConstant::ERROR_MESSAGE] = $parameter['account_number'];
		}
		
		$create_bank_info = BankinfoDao::create_bank_info($connection, $parameter);
		if(UtilityMethods::isNotEmpty($create_bank_info)){
			return $this->get_bank_info($parameter['account_number'], $parameter['group_id']);
		}
	}
	
	/* update bank info */
	public function update_bank_info($parameter){
		$connection = DbConnector::getConnection();
		$respone_array = array();
	
		$exist_count = BankinfoDao::check_duplicate_for_create($connection, $parameter['id']);
		if($exist_count <= 0){
			$respone_array[CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
			$respone_array[CommonConstant::ERROR_MESSAGE] = $account_number;
		}
		
		$exist_count = BankinfoDao::check_duplicate_for_edit($connection, $parameter['id'], $parameter['account_number'], $parameter['group_id']);
		if($exist_count > 0){
			$respone_array[CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_ALREADY_EXIST;
			$respone_array[CommonConstant::ERROR_MESSAGE] = $parameter['account_number'];
		}
		
		$primary_array = array("account_number"=>$parameter['id']);
		$update_array = array();
		if(UtilityMethods::isNotEmpty($parameter['account_number'])){
			$update_array['account_number'] = $parameter['account_number'];
		}
		
		if(UtilityMethods::isNotEmpty($parameter['bank_name'])){
			$update_array['bank_name'] = $parameter['bank_name'];
		}
		
		if(UtilityMethods::isNotEmpty($parameter['bank_code'])){
			$update_array['bank_code'] = $parameter['bank_code'];
		}
		
		if(UtilityMethods::isNotEmpty($parameter['bank_branch'])){
			$update_array['bank_branch'] = $parameter['bank_branch'];
		}
		
		if(UtilityMethods::isNotEmpty($parameter['account_type'])){
			$update_array['account_type'] = $parameter['account_type'];
		}
		
		return Dao::updateBasedOnGivenKey($connection, 'bank_info', $primary_array, $update_array);
	}
	
	/* delete bank info */
	public function delete_bank_info($account_number){
		$connection = DbConnector::getConnection();
		$respone_array = array();
	
		$exist_count = BankinfoDao::check_bank_info_exist($connection, $bank_id);
		if($exist_count <= 0){
			$respone_array[CommonConstant::ERROR_CODE] = ApiResponseConstant::RESOURCE_NOT_EXISTS;
			$respone_array[CommonConstant::ERROR_MESSAGE] = 'bank_info';
		}
	
		return BankinfoDao::delete_bank_info($connection, $bank_id);
	}
}