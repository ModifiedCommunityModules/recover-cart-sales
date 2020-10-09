<?php

function xtc_get_products_special_price_ow($productId, $customerId, $qty = 1)
{
    $customerGroupQuery = xtc_db_query("SELECT customers_status FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . $customerId . "'");
    $customerGroup = xtc_db_fetch_array($customerGroupQuery);
    $personalQuery = xtc_db_query("SELECT personal_offer FROM " . TABLE_PERSONAL_OFFERS_BY . $customerGroup['customers_status'] . " WHERE products_id = " . (int) $productId . " AND quantity <= " . (int) $qty . " ORDER BY quantity DESC LIMIT 1");
    
    if (xtc_db_num_rows($personalQuery)) {
        $personal = xtc_db_fetch_array($personalQuery);
        return $personal['personal_offer'];
    }

    $productQuery = xtc_db_query("SELECT specials_new_products_price FROM " . TABLE_SPECIALS . " WHERE products_id = '" . (int) $productId . "' AND status");
    $product = xtc_db_fetch_array($productQuery);
    return $product['specials_new_products_price'];
}