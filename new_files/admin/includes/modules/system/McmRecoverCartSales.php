<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
// restore_error_handler();
// restore_exception_handler();

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

use RobinTheHood\ModifiedStdModule\Classes\StdModule;
require_once DIR_FS_DOCUMENT_ROOT . '/vendor-no-composer/autoload.php';

class McmRecoverCartSales extends StdModule
{
    public function __construct()
    {
        $this->init('MODULE_MCM_RECOVER_CART_SALES');
    }
       
    public function display()
    {
        return $this->displaySaveButton();
    }

    public function install()
    {
        parent::install();

        $this->setAdminAccess('mcm_recover_cart_sales');
        $this->setAdminAccess('mcm_recover_cart_sales_stats');

        // Create table
        xtc_db_query("CREATE TABLE `mcm_recover_cart_sales` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customers_id` int(11) NOT NULL,
            `date_added` varchar(8) NOT NULL,
            `date_modified` varchar(8) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `customers_id` (`customers_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        // Add new fields to tables
        xtc_db_query("ALTER TABLE `customers_basket` ADD `checkout_site` ENUM( 'cart', 'shipping', 'payment', 'confirm' ) NOT NULL DEFAULT 'cart';");
        xtc_db_query("ALTER TABLE `customers_basket` ADD `language` VARCHAR(32) NULL DEFAULT NULL;");

        // DELETE FROM `configuration_group` WHERE `configuration_group_title` LIKE 'Recover Cart Sales';
        xtc_db_query("DELETE FROM `configuration_group` WHERE `configuration_group_title` LIKE 'Recover Cart Sales';");

        // INSERT INTO `configuration_group` ( `configuration_group_id` , `configuration_group_title` , `configuration_group_description` , `sort_order` , `visible` )
        //     VALUES ('33', 'Recover Cart Sales', 'Recover Cart Sales (RCS) Configuration Values', '33', '1');
        xtc_db_query("INSERT INTO `configuration_group` ( `configuration_group_id` , `configuration_group_title` , `configuration_group_description` , `sort_order` , `visible` ) VALUES ('33', 'Recover Cart Sales', 'Recover Cart Sales (RCS) Configuration Values', '33', '1');");

        // Create / Add Configuration
        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_BASE_DAYS', '30', 33, 10, NULL, NOW(), '', '');
        $this->addConfiguration('BASE_DAYS', '30', 33, 10);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_REPORT_DAYS', '90', 33, 15, NULL, NOW(), '', '');
        $this->addConfiguration('REPORT_DAYS', '90', 33, 15);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_EMAIL_TTL', '90', 33, 20, NULL, NOW(), '', '');
        $this->addConfiguration('EMAIL_TTL', '90', 33, 15);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_EMAIL_FRIENDLY', 'true', 33, 30, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
        $this->addConfigurationSelect('EMAIL_FRIENDLY', 'true', 33, 30);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_EMAIL_COPIES_TO', '', 33, 35, NULL, NOW(), '', '');
        $this->addConfiguration('EMAIL_COPIES_TO', '', 33, 35);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_SHOW_ATTRIBUTES', 'false',  33, 40, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
        $this->addConfigurationSelect('HOW_ATTRIBUTES', 'false', 33, 40);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_CHECK_SESSIONS', 'false', 33, 40, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
        $this->addConfigurationSelect('CHECK_SESSIONS', 'false', 33, 40);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_CURCUST_COLOR', '#0000FF', 33, 50, NULL, NOW(), '', '');
        $this->addConfiguration('CURCUST_COLOR', '#0000FF', 33, 50);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_UNCONTACTED_COLOR', '#9FFF9F', 33, 60, NULL, NOW(), '', '');
        $this->addConfiguration('UNCONTACTED_COLOR', '#9FFF9F', 33, 60);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_CONTACTED_COLOR', '#FF9F9F', 33, 70, NULL, NOW(), '', '');
        $this->addConfiguration('CONTACTED_COLOR', '#FF9F9F', 33, 70);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_MATCHED_ORDER_COLOR', '#9FFFFF', 33, 72, NULL, NOW(), '', '');
        $this->addConfiguration('MATCHED_ORDER_COLOR', '#9FFFFF', 33, 72);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_SKIP_MATCHED_CARTS', 'true', 33, 80, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
        $this->addConfigurationSelect('SKIP_MATCHED_CARTS', 'true', 33, 80);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_AUTO_CHECK', 'true', 33, 82, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
        $this->addConfigurationSelect('AUTO_CHECK', 'true', 33, 82);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_CARTS_MATCH_ALL_DATES', 'true', 33, 84, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
        $this->addConfigurationSelect('CARTS_MATCH_ALL_DATES', 'true', 33, 84);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_PENDING_SALE_STATUS', '1', 33, 85, NULL, NOW(), 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
        xtc_db_query("INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) VALUES (NULL, 'MODULE_MCM_RECOVER_CART_SALES_PENDING_SALE_STATUS', '1', 33, 85, NULL, NOW(), 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');");
        
        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_REPORT_EVEN_STYLE', 'dataTableRow', 33, 90, NULL, NOW(), '', '');
        $this->addConfiguration('REPORT_EVEN_STYLE', 'dataTableRow', 33, 90);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_REPORT_ODD_STYLE', '', 33, 92, NULL, NOW(), '', '');
        $this->addConfiguration('REPORT_ODD_STYLE', '', 33, 92);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_SHOW_BRUTTO_PRICE', 'true', 33, 94, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
        $this->addConfigurationSelect('SHOW_BRUTTO_PRICE', 'true', 33, 94);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'DEFAULT_RCS_SHIPPING', '', 33, 95, NULL, NOW(), '', '');
        $this->addConfiguration('DEFAULT_SHIPPING', '', 33, 95);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'DEFAULT_RCS_PAYMENT', '', 33, 96, NULL, NOW(), '', '');
        $this->addConfiguration('DEFAULT_PAYMENT', '', 33, 96);

        // INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
        //     VALUES (NULL, 'RCS_DELETE_COMPLETED_ORDERS', 'true', 33, 97, NULL, NOW(), '', "xtc_cfg_select_option(array('true', 'false'),");
        $this->addConfigurationSelect('DELETE_COMPLETED_ORDERS', '', 33, 97);
    }

    public function remove()
    {
        parent::remove();
    }
}