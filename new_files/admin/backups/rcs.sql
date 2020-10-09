DELETE FROM `configuration_group` WHERE `configuration_group_title` LIKE 'Recover Cart Sales';
DELETE FROM `configuration` WHERE `configuration_key` LIKE 'RCS_%';
DELETE FROM `configuration` WHERE `configuration_key` LIKE 'DEFAULT_RCS_%';

DROP TABLE IF EXISTS `mcm_recover_cart_sales`;
-- CREATE TABLE `mcm_recover_cart_sales` (
--    `scartid` INT( 11 ) NOT NULL AUTO_INCREMENT, `customers_id` INT( 11 ) NOT NULL UNIQUE, `dateadded` VARCHAR( 8 ) NOT NULL, `datemodified` VARCHAR( 8 ) NOT NULL, PRIMARY KEY ( `scartid` ));

INSERT INTO `configuration_group` ( `configuration_group_id` , `configuration_group_title` , `configuration_group_description` , `sort_order` , `visible` )
   VALUES ('33', 'Recover Cart Sales', 'Recover Cart Sales (RCS) Configuration Values', '33', '1');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_BASE_DAYS', '30', 33, 10, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_REPORT_DAYS', '90', 33, 15, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_EMAIL_TTL', '90', 33, 20, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_EMAIL_FRIENDLY', 'true', 33, 30, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_EMAIL_COPIES_TO', '', 33, 35, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_SHOW_ATTRIBUTES', 'false',  33, 40, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_CHECK_SESSIONS', 'false', 33, 40, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_CURCUST_COLOR', '#0000FF', 33, 50, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_UNCONTACTED_COLOR', '#9FFF9F', 33, 60, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_CONTACTED_COLOR', '#FF9F9F', 33, 70, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_MATCHED_ORDER_COLOR', '#9FFFFF', 33, 72, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_SKIP_MATCHED_CARTS', 'true', 33, 80, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_AUTO_CHECK', 'true', 33, 82, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_CARTS_MATCH_ALL_DATES', 'true', 33, 84, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_PENDING_SALE_STATUS', '1', 33, 85, NULL, NOW(), 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_REPORT_EVEN_STYLE', 'dataTableRow', 33, 90, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_REPORT_ODD_STYLE', '', 33, 92, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_SHOW_BRUTTO_PRICE', 'true', 33, 94, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'DEFAULT_RCS_SHIPPING', '', 33, 95, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'DEFAULT_RCS_PAYMENT', '', 33, 96, NULL, NOW(), '', '');
INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
   VALUES (NULL, 'RCS_DELETE_COMPLETED_ORDERS', 'true', 33, 97, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");

-- ALTER TABLE `customers_basket` ADD `checkout_site` ENUM( 'cart', 'shipping', 'payment', 'confirm' ) NOT NULL DEFAULT 'cart';
-- ALTER TABLE `customers_basket` ADD `language` VARCHAR(32) NULL DEFAULT NULL;

-- ALTER TABLE `admin_access` ADD `recover_cart_sales` INT( 1 ) DEFAULT '0' NOT NULL ;
-- UPDATE `admin_access` SET `recover_cart_sales` = '1' WHERE `customers_id` = '1' LIMIT 1 ;

-- ALTER TABLE `admin_access` ADD `stats_recover_cart_sales` INT( 1 ) DEFAULT '0' NOT NULL ;
-- UPDATE `admin_access` SET `stats_recover_cart_sales` = '1' WHERE `customers_id` = '1' LIMIT 1 ;
