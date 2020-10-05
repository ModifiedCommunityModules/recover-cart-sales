<?php
  // BOF Offener Warenkorb Plus
  function xtc_get_products_special_price_ow($product_id, $customer_id, $qty = 1) {
    $customer_group_query = xtc_db_query("select customers_status from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
    $customer_group = xtc_db_fetch_array($customer_group_query);
    $personal_query = xtc_db_query("SELECT personal_offer FROM " . TABLE_PERSONAL_OFFERS_BY . $customer_group['customers_status'] . " WHERE products_id=" . (int)$product_id . " AND quantity<=" . (int)$qty . " ORDER BY quantity DESC LIMIT 1");
    if (xtc_db_num_rows($personal_query)) {
      $personal = xtc_db_fetch_array($personal_query);
      return $personal['personal_offer'];
    }
    $product_query = xtc_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status");
    $product = xtc_db_fetch_array($product_query);
    return $product['specials_new_products_price'];
  }
  // EOF Offener Warenkorb Plus