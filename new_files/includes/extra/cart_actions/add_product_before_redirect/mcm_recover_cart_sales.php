<?php
if (!defined('MODULE_MCM_RECOVER_CART_SALES_STATUS') || MODULE_MCM_RECOVER_CART_SALES_STATUS != 'true') {
    return;
}

use ModifiedCommunityModules\RecoverCartSales\Classes\RecoverCartSales;
require_once DIR_FS_DOCUMENT_ROOT . '/vendor-no-composer/autoload.php';

RecoverCartSales::checkoutSite('cart');