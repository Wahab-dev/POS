<?php

namespace dao;

use dao\Dao;
use utility\UtilityMethods;
use utility\constant\ListFieldConstant;

class TenantDao extends Dao {
	/* get Tenant mapping */
	public static function get_tenant_mapping_details($connection, $group_id) {
		$pstn_sql = "SELECT `tenant_id` FROM `tenant_group_mapping` WHERE `group_id` = ?";
		return Dao::getSingleColumnAsArray($connection, $pstn_sql, array($group_id));
	}
}
