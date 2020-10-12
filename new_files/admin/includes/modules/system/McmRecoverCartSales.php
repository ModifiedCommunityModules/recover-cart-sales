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

        // Create new Configuration Group
        xtc_db_query("DELETE FROM `configuration_group` WHERE `configuration_group_title` LIKE 'Recover Cart Sales';");
        xtc_db_query("INSERT INTO `configuration_group` ( `configuration_group_id` , `configuration_group_title` , `configuration_group_description` , `sort_order` , `visible` ) VALUES ('33', 'Recover Cart Sales', 'Recover Cart Sales (RCS) Configuration Values', '33', '1');");

        // Add Configuration Values
        $this->addConfiguration('BASE_DAYS', '30', 33, 10);
        $this->addConfiguration('REPORT_DAYS', '90', 33, 15);
        $this->addConfiguration('EMAIL_TTL', '90', 33, 15);
        $this->addConfigurationSelect('EMAIL_FRIENDLY', 'true', 33, 30);
        $this->addConfiguration('EMAIL_COPIES_TO', '', 33, 35);
        $this->addConfigurationSelect('SHOW_ATTRIBUTES', 'false', 33, 40);
        $this->addConfigurationSelect('CHECK_SESSIONS', 'false', 33, 40);
        $this->addConfiguration('CURCUST_COLOR', '#0000FF', 33, 50);
        $this->addConfiguration('UNCONTACTED_COLOR', '#9FFF9F', 33, 60);
        $this->addConfiguration('CONTACTED_COLOR', '#FF9F9F', 33, 70);
        $this->addConfiguration('MATCHED_ORDER_COLOR', '#9FFFFF', 33, 72);
        $this->addConfigurationSelect('SKIP_MATCHED_CARTS', 'true', 33, 80);
        $this->addConfigurationSelect('AUTO_CHECK', 'true', 33, 82);
        $this->addConfigurationSelect('CARTS_MATCH_ALL_DATES', 'true', 33, 84);

        xtc_db_query("INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) VALUES (NULL, 'MODULE_MCM_RECOVER_CART_SALES_PENDING_SALE_STATUS', '1', 33, 85, NULL, NOW(), 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');");
        
        $this->addConfiguration('REPORT_EVEN_STYLE', 'dataTableRow', 33, 90);
        $this->addConfiguration('REPORT_ODD_STYLE', '', 33, 92);
        $this->addConfigurationSelect('SHOW_BRUTTO_PRICE', 'true', 33, 94);
        $this->addConfiguration('DEFAULT_SHIPPING', '', 33, 95);
        $this->addConfiguration('DEFAULT_PAYMENT', '', 33, 96);
        $this->addConfigurationSelect('DELETE_COMPLETED_ORDERS', 'true', 33, 97);
    }

    public function remove()
    {
        parent::remove();

        $this->deleteConfiguration('BASE_DAYS');
        $this->deleteConfiguration('REPORT_DAYS');
        $this->deleteConfiguration('EMAIL_TTL');
        $this->deleteConfiguration('EMAIL_FRIENDLY');
        $this->deleteConfiguration('EMAIL_COPIES_TO');
        $this->deleteConfiguration('SHOW_ATTRIBUTES');
        $this->deleteConfiguration('CHECK_SESSIONS');
        $this->deleteConfiguration('CURCUST_COLOR');
        $this->deleteConfiguration('UNCONTACTED_COLOR');
        $this->deleteConfiguration('CONTACTED_COLOR');
        $this->deleteConfiguration('MATCHED_ORDER_COLOR');
        $this->deleteConfiguration('SKIP_MATCHED_CARTS');
        $this->deleteConfiguration('AUTO_CHECK');
        $this->deleteConfiguration('CARTS_MATCH_ALL_DATES');
        $this->deleteConfiguration('PENDING_SALE_STATUS');
        $this->deleteConfiguration('REPORT_EVEN_STYLE');
        $this->deleteConfiguration('REPORT_ODD_STYLE');
        $this->deleteConfiguration('SHOW_BRUTTO_PRICE');
        $this->deleteConfiguration('DEFAULT_SHIPPING');
        $this->deleteConfiguration('DEFAULT_PAYMENT');
        $this->deleteConfiguration('DELETE_COMPLETED_ORDERS');
    }
}