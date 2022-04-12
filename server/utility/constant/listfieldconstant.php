<?php
namespace utility\constant;
/**
 * List Field constant - where have define the posibble field parameters in an array
 **/
class ListFieldConstant {
	
	public static $group_fields  = array("group_name", "group_attention", "address_line_1", "address_line_2","email_address", "group_type", "parent_group_id", "created_timestamp", "last_updated_stamp");
	public static $access_level_fields  = array("access_level_name", "access_permissons","created_timestamp", "last_updated_stamp");
	public static $user_fields  = array("first_name", "last_name","user_name", "phone_number", "email_address", "alternate_number", "address_line_1", "address_line_2","access_level_name", "is_active", "last_updated_stamp", "created_timestamp");
	public static $order_fields  = array("order_number", "status","order_total", "user", "last_updated_stamp", "created_timestamp");
	public static $discount_fields = array("discount_code", "discount_name", "discount", "provisionable", "created_timestamp", "last_updated_stamp");
	public static $specialdiscount_fields = array("specialdiscount_name","discountcode","provisionable","group_provisionable","user");
	public static $product_fields = array("product_code", "product_name", "product_type", "provisionable", "seasonable", "varition_type", "varaition_value", "created_timestamp", "last_updated_stamp");
	public static $product_details_fields = array("product_code", "product_name", "product_type", "provisionable", "discount_name", "seasonable", "varition_type", "varaition_value", "created_timestamp", "last_updated_stamp");
	public static $price_fields = array("seller_price", "supplier_price", "seasonable_price", "updated", "created_timestamp", "last_updated_stamp");
	
	public static $supplier_fields = array("supplier_name", "supplier_attention", "phone_number", "alternate_number", "email_address", "address_line_1", "address_line_2", "created_timestamp", "last_updated_stamp");
	public static $supplier_order_fields  = array("order_number", "status","order_total", "amount_paid", "user", "last_updated_stamp", "created_timestamp");
}