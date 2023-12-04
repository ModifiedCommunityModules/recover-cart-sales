<?php

if (!defined('MODULE_MCM_RECOVER_CART_SALES_STATUS') || MODULE_MCM_RECOVER_CART_SALES_STATUS != 'true') {
    return;
}

use ModifiedCommunityModules\RecoverCartSales\Classes\RecoverCartSales;

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

RecoverCartSales::checkoutSite($site);
