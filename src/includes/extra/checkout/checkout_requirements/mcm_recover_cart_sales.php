<?php

use ModifiedCommunityModules\RecoverCartSales\Classes\RecoverCartSales;

if (rth_is_module_disabled('MODULE_MCM_RECOVER_CART_SALES')) {
    return;
}

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
