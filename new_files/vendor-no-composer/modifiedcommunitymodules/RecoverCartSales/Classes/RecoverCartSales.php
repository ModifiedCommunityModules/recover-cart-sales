<?php
/**
 * Recover Cart Sales
 *  
 *  Licensed under GNU General Public License 2.0. 
 *  Some rights reserved. See LICENSE, README.md.
 *
 * @license GPL-2.0 <https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html>
 */
namespace ModifiedCommunityModules\RecoverCartSales\Classes;

class RecoverCartSales
{
    public static function checkoutSite($site)
    {
        $customerId = $_SESSION['customer_id'] ?? null;

        if (!$customerId) {
            return false;
        }

        if ($site != 'cart' && $site != 'shipping' && $site != 'payment' && $site != 'confirm') {
            return false;
        }

        $query = xtc_db_query("SELECT mcm_checkout_site FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = " . $customerId);
        $result = xtc_db_fetch_array($query);
        
        self::compareSite($site, $result['mcm_checkout_site']);
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
            xtc_db_query("UPDATE " . TABLE_CUSTOMERS_BASKET . " SET mcm_checkout_site = '" . xtc_db_input($currentSite) . "', mcm_language = '" . xtc_db_input($_SESSION['language']) . "' WHERE customers_id = " . (int) $_SESSION['customer_id']);
        }
    }
}