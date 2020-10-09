<?php
if (!defined('MODULE_MCM_RECOVER_CART_SALES_STATUS') || MODULE_MCM_RECOVER_CART_SALES_STATUS != 'true') {
    return;
}

require_once DIR_FS_INC . 'mcm_recover_cart_sales.inc.php';

$site = '';
switch ($checkout_position[$current_page]) {
    case '1':
        $site = 'shipping';
        break;
    case '2':
        $site = 'payment';
        break;
    case '3':
        $site = 'confirm';
        break;
}

xtc_checkout_site($site);
