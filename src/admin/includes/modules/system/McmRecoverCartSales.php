<?php

/**
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 * @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName
 */

use RobinTheHood\ModifiedStdModule\Classes\StdModule;

class McmRecoverCartSales extends StdModule
{
    public function __construct()
    {
        parent::__construct('MODULE_MCM_RECOVER_CART_SALES');
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
        xtc_db_query(
            "CREATE TABLE `mcm_recover_cart_sales` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customers_id` int(11) NOT NULL,
            `date_added` varchar(8) NOT NULL,
            `date_modified` varchar(8) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `customers_id` (`customers_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        // Add new fields to tables
        xtc_db_query("ALTER TABLE `customers_basket` ADD `mcm_checkout_site` ENUM( 'cart', 'shipping', 'payment', 'confirm' ) NOT NULL DEFAULT 'cart' COMMENT 'Module: mcm_recover_cart_sales'");
        xtc_db_query("ALTER TABLE `customers_basket` ADD `mcm_language` VARCHAR(32) NULL DEFAULT NULL COMMENT 'Module: mcm_recover_cart_sales'");

        // Create new Configuration Group
        $groupId = 33;

        xtc_db_query("INSERT INTO `configuration_group` ( `configuration_group_id` , `configuration_group_title` , `configuration_group_description` , `sort_order` , `visible` ) VALUES ('" . $groupId . "', 'Recover Cart Sales', 'Recover Cart Sales (RCS) Configuration Values', '33', '1')");

        // Add Configuration Values
        $this->addConfiguration('BASE_DAYS', '30', $groupId, 10);
        $this->addConfiguration('REPORT_DAYS', '90', $groupId, 15);
        $this->addConfiguration('EMAIL_TTL', '90', $groupId, 15);
        $this->addConfigurationSelect('EMAIL_FRIENDLY', 'true', $groupId, 30);
        $this->addConfiguration('EMAIL_COPIES_TO', '', $groupId, 35);
        $this->addConfigurationSelect('SHOW_ATTRIBUTES', 'false', $groupId, 40);
        $this->addConfigurationSelect('CHECK_SESSIONS', 'false', $groupId, 40);
        $this->addConfiguration('CURCUST_COLOR', '#0000FF', $groupId, 50);
        $this->addConfiguration('UNCONTACTED_COLOR', '#9FFF9F', $groupId, 60);
        $this->addConfiguration('CONTACTED_COLOR', '#FF9F9F', $groupId, 70);
        $this->addConfiguration('MATCHED_ORDER_COLOR', '#9FFFFF', $groupId, 72);
        $this->addConfigurationSelect('SKIP_MATCHED_CARTS', 'true', $groupId, 80);
        $this->addConfigurationSelect('AUTO_CHECK', 'true', $groupId, 82);
        $this->addConfigurationSelect('CARTS_MATCH_ALL_DATES', 'true', $groupId, 84);

        xtc_db_query("INSERT INTO `configuration` ( `configuration_id` , `configuration_key` , `configuration_value` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` ) VALUES (NULL, 'MODULE_MCM_RECOVER_CART_SALES_PENDING_SALE_STATUS', '1', " . $groupId . ", 85, NULL, NOW(), 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(')");

        $this->addConfiguration('REPORT_EVEN_STYLE', 'dataTableRow', $groupId, 90);
        $this->addConfiguration('REPORT_ODD_STYLE', '', $groupId, 92);
        $this->addConfigurationSelect('SHOW_BRUTTO_PRICE', 'true', 33, 94);
        $this->addConfiguration('DEFAULT_SHIPPING', '', $groupId, 95);
        $this->addConfiguration('DEFAULT_PAYMENT', '', $groupId, 96);
        $this->addConfigurationSelect('DELETE_COMPLETED_ORDERS', 'true', $groupId, 97);
    }

    public function remove()
    {
        parent::remove();

        $this->deleteAdminAccess('mcm_recover_cart_sales');
        $this->deleteAdminAccess('mcm_recover_cart_sales_stats');

        $groupId = 33;
        xtc_db_query("DELETE FROM `configuration_group` WHERE `configuration_group_id` = '" . $groupId . "'");
        xtc_db_query('DROP TABLE IF EXISTS `mcm_recover_cart_sales`');

        xtc_db_query("ALTER TABLE `customers_basket` DROP `mcm_checkout_site`");
        xtc_db_query("ALTER TABLE `customers_basket` DROP `mcm_language`");

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
