<?php
if (!defined('MODULE_MCM_RECOVER_CART_SALES_STATUS') || MODULE_MCM_RECOVER_CART_SALES_STATUS != 'true') {
    return;
}

require_once DIR_FS_INC . 'mcm_recover_cart_sales.inc.php';

xtc_checkout_site('cart');