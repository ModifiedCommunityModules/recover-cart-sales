<?php

class RecoverCartSales
{
    public static function checkoutSite($site)
    {
        if (!$_SESSION['customer_id']) {
            return false;
        }

        if ($site != 'cart' && $site != 'shipping' && $site != 'payment' && $site != 'confirm') {
            return false;
        }

        $query = xtc_db_query("SELECT checkout_site FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = " . $_SESSION['customer_id']);
        $result = xtc_db_fetch_array($query);
        
        self::compareSite($site, $result['checkout_site']);
    }

    private static function compareSite($currentSite, $oldSite)
    {
        $sorting = [
            'cart' => 1,
            'shipping' => 2,
            'payment' => 3,
            'confirm' => 4
        ];

        if ($sorting[$currentSite] >= $sorting[$oldSite]) {
            xtc_db_query("UPDATE " . TABLE_CUSTOMERS_BASKET . " SET checkout_site = '" . xtc_db_input($currentSite) . "', language = '" . xtc_db_input($_SESSION['language']) . "' WHERE customers_id = " . (int) $_SESSION['customer_id']);
        }
    }
}