<?php
if (!defined('MODULE_MCM_RECOVER_CART_SALES_STATUS') || MODULE_MCM_RECOVER_CART_SALES_STATUS != 'true') {
    return;
}

require_once DIR_FS_INC . 'mcm_recover_cart_sales.inc.php';

if (strpos($_SERVER['REQUEST_URI'], 'login') !== false && isset($_SESSION['customer_id'])) {
    xtc_checkout_site('cart');
}