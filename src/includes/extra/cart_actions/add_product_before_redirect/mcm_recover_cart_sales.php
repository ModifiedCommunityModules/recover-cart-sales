<?php

use ModifiedCommunityModules\RecoverCartSales\Classes\RecoverCartSales;

if (rth_is_module_disabled('MODULE_MCM_RECOVER_CART_SALES')) {
    return;
}

RecoverCartSales::checkoutSite('cart');
