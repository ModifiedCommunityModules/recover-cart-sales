<?php

if (!defined('MODULE_MCM_RECOVER_CART_SALES_STATUS') || MODULE_MCM_RECOVER_CART_SALES_STATUS != 'true') {
    return;
}

use ModifiedCommunityModules\RecoverCartSales\Classes\RecoverCartSales;

if (strpos($_SERVER['REQUEST_URI'], 'login') !== false && isset($_SESSION['customer_id'])) {
    RecoverCartSales::checkoutSite('cart');
}
