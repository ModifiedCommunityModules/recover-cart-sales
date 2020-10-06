<?php
/* -----------------------------------------------------------------------------------------
$Id: order_rcs.php André Estel $

Estelco - Ebusiness & more
http://www.estelco.de

Copyright (c) 2008 Estelco
-----------------------------------------------------------------------------------------
based on:
(c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
(c) 2002-2003 osCommerce(order.php,v 1.32 2003/02/26); www.oscommerce.com
(c) 2003	 nextcommerce (order.php,v 1.28 2003/08/18); www.nextcommerce.org
(c) 2003	 xtcommerce (order.php 1533 2006-08-20 19:03:11Z mz); www.xt-commerce.com

Released under the GNU General Public License
-----------------------------------------------------------------------------------------
Third Party contribution:

Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
http://www.oscommerce.com/community/contributions,282
Copyright (c) Strider | Strider@oscworks.com
Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
Copyright (c) Andre ambidex@gmx.net
Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

credit card encryption functions for the catalog module
BMC 2003 for the CC CVV Module


Released under the GNU General Public License
---------------------------------------------------------------------------------------*/

// include needed functions
// require_once DIR_FS_INC . 'xtc_date_long.inc.php';
// require_once DIR_FS_INC . 'xtc_address_format.inc.php';
// require_once DIR_FS_INC . 'xtc_get_country_name.inc.php';
// require_once DIR_FS_INC . 'xtc_get_zone_code.inc.php';
require_once DIR_FS_INC . 'xtc_get_tax_description.inc.php';


class Order
{
    public $info = [];
    public $totals = [];
    public $products = [];
    public $customer = [];
    public $delivery = [];
    public $taxDiscount = [];
    public $contentType;
    
    public function __construct($customerId)
    {
        global $xtPrice; //TODO: I think we do not need $xtPrice in this method

        $this->cart($customerId);
    }

    private function getCustomerAddress($customerId)
    {
        $sql = "SELECT c.payment_unallowed,c.shipping_unallowed,c.customers_firstname,c.customers_cid, c.customers_gender,c.customers_lastname, c.customers_telephone, c.customers_email_address, c.customers_default_address_id, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, co.countries_id, co.countries_name, co.countries_iso_code_2, co.countries_iso_code_3, co.address_format_id, ab.entry_state FROM " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " ab LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id) LEFT JOIN " . TABLE_COUNTRIES . " co ON (ab.entry_country_id = co.countries_id) WHERE c.customers_id = '" . $customerId . "' AND ab.customers_id = '" . $customerId . "' AND c.customers_default_address_id = ab.address_book_id";

        $query = xtc_db_query($sql);
        return xtc_db_fetch_array($query);
    }

    private function getShippingAddress($customerId, $customerDefaultAddressId)
    {
        $sql = "SELECT ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state FROM " . TABLE_ADDRESS_BOOK . " ab LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id) LEFT JOIN " . TABLE_COUNTRIES . " c ON (ab.entry_country_id = c.countries_id) WHERE ab.customers_id = '" . $customerId . "' AND ab.address_book_id = '" . $$customerDefaultAddressId . "'";
      
        $query = xtc_db_query($sql);
        return xtc_db_fetch_array($query);
    }

    private function getBillingAddress($customerId, $customerDefaultAddressId)
    {
        $sql = "SELECT ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state FROM " . TABLE_ADDRESS_BOOK . " ab LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id) LEFT JOIN " . TABLE_COUNTRIES . " c ON (ab.entry_country_id = c.countries_id) WHERE ab.customers_id = '" . $customerId . "' AND ab.address_book_id = '" . $customerDefaultAddressId . "'";
      
        $query = xtc_db_query($sql);
        return xtc_db_fetch_array($query);
    }

    private function getTaxAddress($customerId, $customerDefaultAddressId)
    {
        $sql = "SELECT ab.entry_country_id, ab.entry_zone_id FROM " . TABLE_ADDRESS_BOOK . " ab LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id) WHERE ab.customers_id = '" . $customerId . "' AND ab.address_book_id = '" . $customerDefaultAddressId . "'";
        
        $query = xtc_db_query($sql);
        return xtc_db_fetch_array($query);
    }

    private function getAttributes($optionId, $optionValueId, $languagedId)
    {
        $sql = "SELECT popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix FROM " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa WHERE pa.products_id = '" . $productId . "' AND pa.options_id = '" . $optionId . "' AND pa.options_id = popt.products_options_id AND pa.options_values_id = '" . $optionValueId . "' AND pa.options_values_id = poval.products_options_values_id AND popt.language_id = '" . $languagedId . "' AND poval.language_id = '" . $languagedId . "'";
                    
        $query = xtc_db_query($sql);
        return xtc_db_fetch_array($query);
    }

    private function buildInfoArray()
    {
        global $xtPrice;

        $result = [
            'order_status' => DEFAULT_ORDERS_STATUS_ID,
            'currency' => DEFAULT_CURRENCY,
            'currency_value' => $xtPrice->currencies[$_SESSION['currency']]['value'],
            //'payment_method' => $_SESSION['payment'],
            'payment_method' => $_SESSION['payment'] ?? '',
            //'cc_type' => (isset($_SESSION['payment'])=='cc' && isset($_SESSION['ccard']['cc_type']) ? $_SESSION['ccard']['cc_type'] : ''),
            //'cc_owner'=>(isset($_SESSION['payment'])=='cc' && isset($_SESSION['ccard']['cc_owner']) ? $_SESSION['ccard']['cc_owner'] : ''),
            //'cc_number' => (isset($_SESSION['payment'])=='cc' && isset($_SESSION['ccard']['cc_number']) ? $_SESSION['ccard']['cc_number'] : ''),
            //'cc_expires' => (isset($_SESSION['payment'])=='cc' && isset($_SESSION['ccard']['cc_expires']) ? $_SESSION['ccard']['cc_expires'] : ''),
            //'cc_start' => (isset($_SESSION['payment'])=='cc' && isset($_SESSION['ccard']['cc_start']) ? $_SESSION['ccard']['cc_start'] : ''),
            //'cc_issue' => (isset($_SESSION['payment'])=='cc' && isset($_SESSION['ccard']['cc_issue']) ? $_SESSION['ccard']['cc_issue'] : ''),
            //'cc_cvv' => (isset($_SESSION['payment'])=='cc' && isset($_SESSION['ccard']['cc_cvv']) ? $_SESSION['ccard']['cc_cvv'] : ''),
            //'shipping_method' => $_SESSION['shipping']['title'],
            'shipping_method' => isset($_SESSION['shipping']) && is_array($_SESSION['shipping']) ? $_SESSION['shipping']['title'] : '',
            //'shipping_cost' => $_SESSION['shipping']['cost'],
            'shipping_cost' => isset($_SESSION['shipping']) && is_array($_SESSION['shipping']) ? $xtPrice->xtcCalculateCurr($_SESSION['shipping']['cost']) : 0,
            //'comments' => $_SESSION['comments'],
            'comments' => $_SESSION['comments'] ?? '',
            //'shipping_class'=>$_SESSION['shipping']['id'],
            'shipping_class' => isset($_SESSION['shipping']) && is_array($_SESSION['shipping']) && array_key_exists('id', $_SESSION['shipping']) ? $_SESSION['shipping']['id'] : '',
            //'payment_class' => $_SESSION['payment'],
            'payment_class' => $_SESSION['payment'] ?? '',
            'subtotal' => 0,
            'tax' => 0,
            'tax_groups' => []
        ];

        if (isset($_SESSION['payment']) && is_object($_SESSION['payment'])) {
            $result['payment_method'] = $_SESSION['payment']->title;
            $result['payment_class'] = $_SESSION['payment']->title;
            if ( isset($_SESSION['payment']->order_status) && is_numeric($_SESSION['payment']->order_status) && ($_SESSION['payment']->order_status > 0) ) {
                $result['order_status'] = $_SESSION['payment']->order_status;
            }
        }

        return $result;
    }

    private function buildCustomerArrayFromCustomerAddress($customerAddress)
    {
        return [
            'firstname' => $customerAddress['customers_firstname'],
            'lastname' => $customerAddress['customers_lastname'],
            'csID' => $customerAddress['customers_cid'],
            'gender' => $customerAddress['customers_gender'],
            'company' => $customerAddress['entry_company'],
            'street_address' => $customerAddress['entry_street_address'],
            'suburb' => $customerAddress['entry_suburb'],
            'city' => $customerAddress['entry_city'],
            'postcode' => $customerAddress['entry_postcode'],
            'state' => ((xtc_not_null($customerAddress['entry_state'])) ? $customerAddress['entry_state'] : $customerAddress['zone_name']),
            'zone_id' => $customerAddress['entry_zone_id'],
            'country' => [
                'id' => $customerAddress['countries_id'],
                'title' => $customerAddress['countries_name'],
                'iso_code_2' => $customerAddress['countries_iso_code_2'],
                'iso_code_3' => $customerAddress['countries_iso_code_3']
            ],
            'format_id' => $customerAddress['address_format_id'],
            'telephone' => $customerAddress['customers_telephone'],
            'payment_unallowed' => $customerAddress['payment_unallowed'],
            'shipping_unallowed' => $customerAddress['shipping_unallowed'],
            'email_address' => $customerAddress['customers_email_address']
        ];
    }

    public function buildDeliveryArrayFromShippingAddress($shippingAddress)
    {
        return [
            'firstname' => $shippingAddress['entry_firstname'],
            'lastname' => $shippingAddress['entry_lastname'],
            'company' => $shippingAddress['entry_company'],
            'street_address' => $shippingAddress['entry_street_address'],
            'suburb' => $shippingAddress['entry_suburb'],
            'city' => $shippingAddress['entry_city'],
            'postcode' => $shippingAddress['entry_postcode'],
            'state' => ((xtc_not_null($shippingAddress['entry_state'])) ? $shippingAddress['entry_state'] : $shippingAddress['zone_name']),
            'zone_id' => $shippingAddress['entry_zone_id'],
            'country' => [
                'id' => $shippingAddress['countries_id'],
                'title' => $shippingAddress['countries_name'],
                'iso_code_2' => $shippingAddress['countries_iso_code_2'],
                'iso_code_3' => $shippingAddress['countries_iso_code_3']
            ],
            'country_id' => $shippingAddress['entry_country_id'],
            'format_id' => $shippingAddress['address_format_id']
        ];
    }

    private function buildBillingFromBillingAddress($billingAddress)
    {
        return [
            'firstname' => $billingAddress['entry_firstname'],
            'lastname' => $billingAddress['entry_lastname'],
            'company' => $billingAddress['entry_company'],
            'street_address' => $billingAddress['entry_street_address'],
            'suburb' => $billingAddress['entry_suburb'],
            'city' => $billingAddress['entry_city'],
            'postcode' => $billingAddress['entry_postcode'],
            'state' => ((xtc_not_null($billingAddress['entry_state'])) ? $billingAddress['entry_state'] : $billingAddress['zone_name']),
            'zone_id' => $billingAddress['entry_zone_id'],
            'country' => [
                'id' => $billingAddress['countries_id'],
                'title' => $billingAddress['countries_name'],
                'iso_code_2' => $billingAddress['countries_iso_code_2'],
                'iso_code_3' => $billingAddress['countries_iso_code_3']
            ],
            'country_id' => $billingAddress['entry_country_id'],
            'format_id' => $billingAddress['address_format_id']
        ];
    }

    public function cart($customerId)
    {
        global $currencies, $xtPrice;

        $this->contentType = $_SESSION['cart']->get_content_type();

        $customerAddress = $this->getCustomerAddress($customerId);
        $shippingAddress = $this->getShippingAddress($customerId, $customerAddress['customers_default_address_id']);
        $billingAddress = $this->getBillingAddress($customerId, $customerAddress['customers_default_address_id']);
        $taxAddress = $this->getTaxAddress($customerId, $customerAddress['customers_default_address_id']);

        $this->info = $this->buildInfoArray();
        $this->customer = $this->buildCustomerArrayFromCustomerAddress($customerAddress);
        $this->delivery = $this->buildDeliveryArrayFromShippingAddress($shippingAddress);
        $this->billing = $this->buildBillingFromBillingAddress($billingAddress);

        $index = 0;
        $products = $_SESSION['cart']->get_products();
        for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
            $product = $products[$i];

            $productPrice = $xtPrice->xtcGetPrice($product['id'], false, $product['quantity'], $product['tax_class_id'], '');
            $productPrice += $xtPrice->xtcFormat($_SESSION['cart']->attributes_price($product['id']), false, $product['tax_class_id']);

            $this->products[$index] = [ // TODO: Maybe we can use [] instead of [$index]
                'qty' => $product['quantity'],
                'name' => $product['name'],
                'model' => $product['model'],
                'tax_class_id'=> $product['tax_class_id'],
                'tax' => xtc_get_tax_rate($product['tax_class_id'], $taxAddress['entry_country_id'], $taxAddress['entry_zone_id']),
                'tax_description' => xtc_get_tax_description($product['tax_class_id'], $taxAddress['entry_country_id'], $taxAddress['entry_zone_id']),
                'price' =>  $productPrice ,
                'final_price' => $productPrice * $product['quantity'],
                'shipping_time'=>$product['shipping_time'],
                'weight' => $product['weight'],
                'id' => $product['id']
            ];

            if ($product['attributes']) {
                $subindex = 0;
                reset($product['attributes']);
                while (list($optionId, $optionValueId) = each($product['attributes'])) { // TODO: Maybe we can foreach
                    
                    $attributes = $this->getAttributes($product['id'], $optionId, $optionValueId, $_SESSION['languages_id']);

                    $this->products[$index]['attributes'][$subindex] = [ // TODO: Maybe we can use [] instead of [$subindex]
                        'option' => $attributes['products_options_name'],
                        'value' => $attributes['products_options_values_name'],
                        'option_id' => $optionId,
                        'value_id' => $optionValueId,
                        'prefix' => $attributes['price_prefix'],
                        'price' => $attributes['options_values_price']
                    ];

                    $subindex++;
                }
            }

            $shownPrice = $this->products[$index]['final_price'];
            $this->info['subtotal'] += $shownPrice;
            if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1){
                $shownPriceTax = $shownPrice - ($shownPrice / 100 * $_SESSION['customers_status']['customers_status_ot_discount']);
            }

            $productTax = $this->products[$index]['tax'];
            $productTaxDescription = $this->products[$index]['tax_description'];
            
            if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '1') {
                $taxIndex = TAX_ADD_TAX . $productTaxDescription;

                if (!isset($this->info['tax_groups'][$taxIndex])) {
                    $this->info['tax_groups'][$taxIndex] = 0;
                }

                if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1) {
                    $this->info['tax'] += $shownPriceTax - ($shownPriceTax / (($productTax < 10) ? "1.0" . str_replace('.', '', $productTax) : "1." . str_replace('.', '', $productTax)));
                    //$this->info['tax_groups'][TAX_ADD_TAX . "$productTaxDescription"] += (($shownPriceTax / (100 + $productTax)) * $productTax);
                    $this->info['tax_groups'][$taxIndex] += ($shownPriceTax / (100 + $productTax)) * $productTax;
                } else {
                    $this->info['tax'] += $shownPrice - ($shownPrice / (($productTax < 10) ? "1.0" . str_replace('.', '', $productTax) : "1." . str_replace('.', '', $productTax)));
                    //$this->info['tax_groups'][TAX_ADD_TAX . "$productTaxDescription"] += (($shownPrice / (100 + $productTax)) * $productTax);
                    $this->info['tax_groups'][$taxIndex] += ($shownPrice / (100 + $productTax)) * $productTax;
                }
            } else {
                $taxIndex = TAX_NO_TAX . $productTaxDescription;
                if (!isset($this->info['tax_groups'][$taxIndex])) {
                    $this->info['tax_groups'][$taxIndex] = 0;
                }

                if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1) {
                    // BOF - web28 - 2010-05-06 - PayPal API Modul / Paypal Express Modul
                    //$this->info['tax'] += ($shownPriceTax / 100) * ($productTax);
                    $this->taxDiscount[$product['tax_class_id']] += $shownPriceTax / 100 * $productTax;
                    // EOF - web28 - 2010-05-06 - PayPal API Modul / Paypal Express Modul
                    //$this->info['tax_groups'][TAX_NO_TAX . "$productTaxDescription"] += ($shownPriceTax / 100) * ($productTax);
                    $this->info['tax_groups'][$taxIndex] += $shownPriceTax / 100 * $productTax;
                } else {
                    $this->info['tax'] += $shownPrice / 100 * $productTax;
                    //$this->info['tax_groups'][TAX_NO_TAX . "$productTaxDescription"] += ($shownPrice / 100) * ($productTax);
                    $this->info['tax_groups'][$taxIndex] += $shownPrice / 100 * $productTax;
                }
            }
            $index++;
        }

        // BOF - web28 - 2010-05-06 - PayPal API Modul / Paypal Express Modul
        foreach ($this->taxDiscount as $value) {
            //$this->info['tax']+=round($value, $xtPrice->get_decimal_places($order->info['currency']));
            $this->info['tax'] += round($value, $xtPrice->get_decimal_places('')); //web28: parameter in get_decimal_places isn't used
        }

        // EOF - web28 - 2010-05-06 - PayPal API Modul / Paypal Express Modul
        //$this->info['shipping_cost']=0;
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '0') {
            $this->info['total'] = $this->info['subtotal'] + $xtPrice->xtcFormat($this->info['shipping_cost'], false, 0, true);
            if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == '1') {
                $this->info['total'] -= ($this->info['subtotal'] / 100 * $_SESSION['customers_status']['customers_status_ot_discount']);
            }
        } else {
            $this->info['total'] = $this->info['subtotal'] + $xtPrice->xtcFormat($this->info['shipping_cost'], false, 0, true);
            if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == '1') {
                $this->info['total'] -= ($this->info['subtotal'] / 100 * $_SESSION['customers_status']['customers_status_ot_discount']);
            }
        }
    }

    // function query($order_id) {

    //     $order_id = xtc_db_prepare_input($order_id);

    //     $order_query = xtc_db_query("SELECT
    //                                 *
    //                                 FROM " . TABLE_ORDERS . " WHERE
    //                                 orders_id = '" . xtc_db_input($order_id) . "'");

    //     $order = xtc_db_fetch_array($order_query);

    //     $totals_query = xtc_db_query("SELECT * FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = '" . xtc_db_input($order_id) . "' order by sort_order");
    //     while ($totals = xtc_db_fetch_array($totals_query)) {
    //         $this->totals[] = array('title' => $totals['title'],
    //         'text' =>$totals['text'],
    //         'value'=>$totals['value']);
    //     }

    //     $order_total_query = xtc_db_query("SELECT text FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = '" . $order_id . "' AND class = 'ot_total'");
    //     $order_total = xtc_db_fetch_array($order_total_query);

    //     $shipping_method_query = xtc_db_query("SELECT title FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = '" . $order_id . "' AND class = 'ot_shipping'");
    //     $shipping_method = xtc_db_fetch_array($shipping_method_query);

    //     $order_status_query = xtc_db_query("SELECT orders_status_name FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_id = '" . $order['orders_status'] . "' AND language_id = '" . $_SESSION['languages_id'] . "'");
    //     $order_status = xtc_db_fetch_array($order_status_query);

    //     $this->info = array('currency' => $order['currency'],
    //         'currency_value' => $order['currency_value'],
    //         'payment_method' => $order['payment_method'],
    //         'cc_type' => $order['cc_type'],
    //         'cc_owner' => $order['cc_owner'],
    //         'cc_number' => $order['cc_number'],
    //         'cc_expires' => $order['cc_expires'],
    //         // BMC CC Mod Start
    //         'cc_start' => $order['cc_start'],
    //         'cc_issue' => $order['cc_issue'],
    //         'cc_cvv' => $order['cc_cvv'],
    //         // BMC CC Mod End
    //         'date_purchased' => $order['date_purchased'],
    //         'orders_status' => $order_status['orders_status_name'],
    //         'last_modified' => $order['last_modified'],
    //         'total' => strip_tags($order_total['text']),
    //         'shipping_method' => ((substr($shipping_method['title'], -1) == ':') ? substr(strip_tags($shipping_method['title']), 0, -1) : strip_tags($shipping_method['title'])),
    //         'comments' => $order['comments']
    //     );

    //     $this->customer = array('id' => $order['customers_id'],
    //         'name' => $order['customers_name'],
    //         'firstname' => $order['customers_firstname'],
    //         'lastname' => $order['customers_lastname'],
    //         'csID' => $order['customers_cid'],
    //         'company' => $order['customers_company'],
    //         'street_address' => $order['customers_street_address'],
    //         'suburb' => $order['customers_suburb'],
    //         'city' => $order['customers_city'],
    //         'postcode' => $order['customers_postcode'],
    //         'state' => $order['customers_state'],
    //         'country' => $order['customers_country'],
    //         'format_id' => $order['customers_address_format_id'],
    //         'telephone' => $order['customers_telephone'],
    //         'email_address' => $order['customers_email_address']
    //     );

    //     $this->delivery = array('name' => $order['delivery_name'],
    //         'firstname' => $order['delivery_firstname'],
    //         'lastname' => $order['delivery_lastname'],
    //         'company' => $order['delivery_company'],
    //         'street_address' => $order['delivery_street_address'],
    //         'suburb' => $order['delivery_suburb'],
    //         'city' => $order['delivery_city'],
    //         'postcode' => $order['delivery_postcode'],
    //         'state' => $order['delivery_state'],
    //         'country' => $order['delivery_country'],
    //         'format_id' => $order['delivery_address_format_id']
    //     );

    //     if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
    //         $this->delivery = false;
    //     }

    //     $this->billing = array('name' => $order['billing_name'],
    //         'firstname' => $order['billing_firstname'],
    //         'lastname' => $order['billing_lastname'],
    //         'company' => $order['billing_company'],
    //         'street_address' => $order['billing_street_address'],
    //         'suburb' => $order['billing_suburb'],
    //         'city' => $order['billing_city'],
    //         'postcode' => $order['billing_postcode'],
    //         'state' => $order['billing_state'],
    //         'country' => $order['billing_country'],
    //         'format_id' => $order['billing_address_format_id']
    //     );

    //     $index = 0;
    //     $orders_products_query = xtc_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_id = '" . xtc_db_input($order_id) . "'");
    //     while ($orders_products = xtc_db_fetch_array($orders_products_query)) {
    //         $this->products[$index] = array('qty' => $orders_products['products_quantity'],
    //             'id' => $orders_products['products_id'],
    //             'name' => $orders_products['products_name'],
    //             'model' => $orders_products['products_model'],
    //             'tax' => $orders_products['products_tax'],
    //             'price'=>$orders_products['products_price'],
    //             'shipping_time'=>$orders_products['products_shipping_time'],
    //             'final_price' => $orders_products['final_price']
    //         );

    //         $subindex = 0;
    //         $attributesQuery = xtc_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE orders_id = '" . xtc_db_input($order_id) . "' AND orders_products_id = '" . $orders_products['orders_products_id'] . "'");
    //         if (xtc_db_num_rows($attributesQuery)) {
    //             while ($attributes = xtc_db_fetch_array($attributesQuery)) {
    //                 $this->products[$index]['attributes'][$subindex] = array('option' => $attributes['products_options'],
    //                     'value' => $attributes['products_options_values'],
    //                     'prefix' => $attributes['price_prefix'],
    //                     'price' => $attributes['options_values_price']
    //                 );

    //                 $subindex++;
    //             }
    //         }

    //         $this->info['tax_groups']["{$this->products[$index]['tax']}"] = '1';

    //         $index++;
    //     }
    // }

    // function getOrderData($oID) {
    //     global $xtPrice;

    //     require_once DIR_FS_INC . 'xtc_get_attributes_model.inc.php';

    //     $order_query = "SELECT
    //                 products_id,
    //                 orders_products_id,
    //                 products_model,
    //                 products_name,
    //                 final_price,
    //                   products_shipping_time,
    //                 products_quantity
    //                 FROM ".TABLE_ORDERS_PRODUCTS."
    //                 WHERE orders_id='".(int) $oID."'";
    //     $order_data = array ();
    //     $order_query = xtc_db_query($order_query);
    //     while ($order_data_values = xtc_db_fetch_array($order_query)) {
    //         $attributesQuery = "SELECT
    //                   products_options,
    //                   products_options_values,
    //                   price_prefix,
    //                   options_values_price
    //                   FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
    //                   WHERE orders_products_id='".$order_data_values['orders_products_id']."'";
    //         $attributes_data = '';
    //         $attributes_model = '';
    //         $attributesQuery = xtc_db_query($attributesQuery);
    //         while ($attributes_data_values = xtc_db_fetch_array($attributesQuery)) {
    //             $attributes_data .= '<br />'.$attributes_data_values['products_options'].':'.$attributes_data_values['products_options_values'];
    //             $attributes_model .= '<br />'.xtc_get_attributes_model($order_data_values['products_id'], $attributes_data_values['products_options_values'],$attributes_data_values['products_options']);
    //         }
            
    //         $order_data[] = array ('PRODUCTS_MODEL' => $order_data_values['products_model'], 'PRODUCTS_NAME' => $order_data_values['products_name'],'PRODUCTS_SHIPPING_TIME' => $order_data_values['products_shipping_time'], 'PRODUCTS_ATTRIBUTES' => $attributes_data, 'PRODUCTS_ATTRIBUTES_MODEL' => $attributes_model, 'PRODUCTS_PRICE' => $xtPrice->xtcFormat($order_data_values['final_price'], true),'PRODUCTS_SINGLE_PRICE' => $xtPrice->xtcFormat($order_data_values['final_price']/$order_data_values['products_quantity'], true), 'PRODUCTS_QTY' => $order_data_values['products_quantity']);

    //     }

    //     return $order_data;
    // }

    // function getTotalData($oID) {
    //     global $xtPrice,$db;

    //     // get order_total data
    //     $oder_total_query = "SELECT
    //             title,
    //             text,
    //                     class,
    //                     value,
    //             sort_order
    //             FROM ".TABLE_ORDERS_TOTAL."
    //             WHERE orders_id='".(int) $oID."'
    //             ORDER BY sort_order ASC";

    //     $order_total = array ();
    //     $oder_total_query = xtc_db_query($oder_total_query);
    //     while ($oder_total_values = xtc_db_fetch_array($oder_total_query)) {


    //         $order_total[] = array ('TITLE' => $oder_total_values['title'], 'CLASS' => $oder_total_values['class'], 'VALUE' => $oder_total_values['value'], 'TEXT' => $oder_total_values['text']);
    //         if ($oder_total_values['class'] = 'ot_total')
    //             $total = $oder_total_values['value'];

    //     }

    //     return array('data'=>$order_total,'total'=>$total);
    // }
}