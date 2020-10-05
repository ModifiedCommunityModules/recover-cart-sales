<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class rcs_shopping_cart { 
  
    //--- BEGIN DEFAULT CLASS METHODS ---//
    function __construct()
    {
        $this->code = 'rcs_shopping_cart'; //Important same name as class name
        $this->title = 'rcs_shopping_cart';
        $this->description = '';        
        $this->name = 'MODULE_SHOPPING_CART_'.strtoupper($this->code);
        $this->enabled = defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true' ? true : false;
        $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : '';
        
        $this->translate();
    }
    
    function translate() {
        switch ($_SESSION['language_code']) {
            case 'de':
                $this->title = 'rcs_shopping_cart';
                $this->description = '';
                break;
          default:
                $this->title = 'rcs_shopping_cart';
                $this->description = '';
                break;
        }
    }
    
    function check() {
        if (!isset($this->_check)) {
            $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '".$this->name."_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }
    
    function keys() {
        define($this->name.'_STATUS_TITLE', TEXT_DEFAULT_STATUS_TITLE);
        define($this->name.'_STATUS_DESC', TEXT_DEFAULT_STATUS_DESC);
        define($this->name.'_SORT_ORDER_TITLE', TEXT_DEFAULT_SORT_ORDER_TITLE);
        define($this->name.'_SORT_ORDER_DESC', TEXT_DEFAULT_SORT_ORDER_DESC);
        
        return array(
            $this->name.'_STATUS', 
            $this->name.'_SORT_ORDER'
        );
    }

    // BOF Offener Warenkorb Plus
    function restoreCustomersCart($shopping_cart, $customers_id) {

        $shopping_cart->reset(false);

        $products_query = xtc_db_query("select products_id, customers_basket_quantity from ".TABLE_CUSTOMERS_BASKET." where customers_id = '".$customers_id."'");
        while ($products = xtc_db_fetch_array($products_query)) {
            $shopping_cart->contents[$products['products_id']] = array ('qty' => $products['customers_basket_quantity']);
            // attributes
            $attributes_query = xtc_db_query("select products_options_id, products_options_value_id from ".TABLE_CUSTOMERS_BASKET_ATTRIBUTES." where customers_id = '".$customers_id."' and products_id = '".$products['products_id']."'");
            while ($attributes = xtc_db_fetch_array($attributes_query)) {
                $shopping_cart->contents[$products['products_id']]['attributes'][$attributes['products_options_id']] = $attributes['products_options_value_id'];
            }
        }
        $shopping_cart->calculate();
    }
    // EOF Offener Warenkorb Plus

    function install() {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('".$this->name."_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$this->name."_SORT_ORDER', '10','6', '2', now())");
    }

    function remove() {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE '".$this->name."_%'");
    }
    
    
    //--- BEGIN CUSTOM  CLASS METHODS ---//

}