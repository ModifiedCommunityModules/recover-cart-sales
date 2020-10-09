<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!defined('MODULE_MCM_RECOVER_CART_SALES_STATUS') || MODULE_MCM_RECOVER_CART_SALES_STATUS != 'true') {
    return;
}

define('FILENAME_MCM_RECOVER_CART_SALES', 'mcm_recover_cart_sales.php');
define('FILENAME_MCM_RECOVER_CART_SALES_STATS', 'mcm_recover_cart_sales_stats.php');
define('FILENAME_CATALOG_PRODUCT_INFO', 'product_info.php');
define('FILENAME_CATALOG_LOGIN', 'login.php');