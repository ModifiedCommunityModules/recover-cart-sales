<?php

use ModifiedCommunityModules\RecoverCartSales\Classes\RecoverCartSales;

if (rth_is_module_disabled('MODULE_MCM_RECOVER_CART_SALES')) {
    return;
}

if (strpos($_SERVER['REQUEST_URI'], 'login') !== false && isset($_SESSION['customer_id'])) {
    RecoverCartSales::checkoutSite('cart');
}
