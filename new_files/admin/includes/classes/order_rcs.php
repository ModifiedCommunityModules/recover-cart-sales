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
    public $info;
    public $totals;
    public $products;
    public $customer;
    public $delivery;
    public $contentType;
    public $taxDiscount;

    public function __construct($customerId)
    {
        global $xtPrice;
        $this->info = [];
        $this->totals = [];
        $this->products = [];
        $this->customer = [];
        $this->delivery = [];

        $this->cart($customerId);
    }
    
    // function query($order_id) {

    //     $order_id = xtc_db_prepare_input($order_id);

    //     $order_query = xtc_db_query("SELECT
    //                                 *
    //                                 FROM " . TABLE_ORDERS . " WHERE
    //                                 orders_id = '" . xtc_db_input($order_id) . "'");

    //     $order = xtc_db_fetch_array($order_query);

    //     $totals_query = xtc_db_query("SELECT * FROM " . TABLE_ORDERS_TOTAL . " where orders_id = '" . xtc_db_input($order_id) . "' order by sort_order");
    //     while ($totals = xtc_db_fetch_array($totals_query)) {
    //         $this->totals[] = array('title' => $totals['title'],
    //         'text' =>$totals['text'],
    //         'value'=>$totals['value']);
    //     }

    //     $order_total_query = xtc_db_query("select text from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . $order_id . "' and class = 'ot_total'");
    //     $order_total = xtc_db_fetch_array($order_total_query);

    //     $shipping_method_query = xtc_db_query("select title from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . $order_id . "' and class = 'ot_shipping'");
    //     $shipping_method = xtc_db_fetch_array($shipping_method_query);

    //     $order_status_query = xtc_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . $order['orders_status'] . "' and language_id = '" . $_SESSION['languages_id'] . "'");
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
    //     $orders_products_query = xtc_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . xtc_db_input($order_id) . "'");
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
    //         $attributesQuery = xtc_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . xtc_db_input($order_id) . "' and orders_products_id = '" . $orders_products['orders_products_id'] . "'");
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

    public function cart($customerId)
    {
        global $currencies, $xtPrice;

        $this->contentType = $_SESSION['cart']->get_content_type();

        $customerAddressQuery = xtc_db_query("select c.payment_unallowed,c.shipping_unallowed,c.customers_firstname,c.customers_cid, c.customers_gender,c.customers_lastname, c.customers_telephone, c.customers_email_address, c.customers_default_address_id, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, co.countries_id, co.countries_name, co.countries_iso_code_2, co.countries_iso_code_3, co.address_format_id, ab.entry_state from " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " co on (ab.entry_country_id = co.countries_id) where c.customers_id = '" . $customerId . "' and ab.customers_id = '" . $customerId . "' and c.customers_default_address_id = ab.address_book_id");
      
        $customerAddress = xtc_db_fetch_array($customerAddressQuery);

        $shippingAddressQuery = xtc_db_query("select ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " c on (ab.entry_country_id = c.countries_id) where ab.customers_id = '" . $customerId . "' and ab.address_book_id = '" . $customerAddress['customers_default_address_id'] . "'");
      
        $shippingAddress = xtc_db_fetch_array($shippingAddressQuery);

        $billingAddressQuery = xtc_db_query("select ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " c on (ab.entry_country_id = c.countries_id) where ab.customers_id = '" . $customerId . "' and ab.address_book_id = '" . $customerAddress['customers_default_address_id'] . "'");
      
        $billingAddress = xtc_db_fetch_array($billingAddressQuery);

        $taxAddressQuery = xtc_db_query("select ab.entry_country_id, ab.entry_zone_id from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) where ab.customers_id = '" . $customerId . "' and ab.address_book_id = '" . $customerAddress['customers_default_address_id'] . "'");
        
        $taxAddress = xtc_db_fetch_array($taxAddressQuery);

        $this->info = [
            'order_status' => DEFAULT_ORDERS_STATUS_ID,
            'currency' => DEFAULT_CURRENCY,
            'currency_value' => $xtPrice->currencies[$_SESSION['currency']]['value'],
            //'payment_method' => $_SESSION['payment'],
            'payment_method' => isset($_SESSION['payment']) ? $_SESSION['payment'] : '',
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
            'comments' => isset($_SESSION['comments']) ? $_SESSION['comments'] : '',
            //'shipping_class'=>$_SESSION['shipping']['id'],
            'shipping_class' => isset($_SESSION['shipping']) && is_array($_SESSION['shipping']) && array_key_exists('id', $_SESSION['shipping']) ? $_SESSION['shipping']['id'] : '',
            //'payment_class' => $_SESSION['payment'],
            'payment_class' => isset($_SESSION['payment']) ? $_SESSION['payment'] : '',
            'subtotal' => 0,
            'tax' => 0,
            'tax_groups' => []
        ];

        if (isset($_SESSION['payment']) && is_object($_SESSION['payment'])) {
            $this->info['payment_method'] = $_SESSION['payment']->title;
            $this->info['payment_class'] = $_SESSION['payment']->title;
            if ( isset($_SESSION['payment']->order_status) && is_numeric($_SESSION['payment']->order_status) && ($_SESSION['payment']->order_status > 0) ) {
                $this->info['order_status'] = $_SESSION['payment']->order_status;
            }
        }

        $this->customer = [
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

        $this->delivery = [
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

        $this->billing = [
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

        $index = 0;
        // BOF - web28 - 2010-05-06 - PayPal API Modul / Paypal Express Modul
        $this->taxDiscount = [];
        // EOF - web28 - 2010-05-06 - PayPal API Modul / Paypal Express Modul
        $products = $_SESSION['cart']->get_products();
        
        for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
            $productPrice = $xtPrice->xtcGetPrice($products[$i]['id'], $format = false, $products[$i]['quantity'], $products[$i]['tax_class_id'], '');
            $productPrice += $xtPrice->xtcFormat($_SESSION['cart']->attributes_price($products[$i]['id']), false, $products[$i]['tax_class_id']);

            $this->products[$index] = [
                'qty' => $products[$i]['quantity'],
                'name' => $products[$i]['name'],
                'model' => $products[$i]['model'],
                'tax_class_id'=> $products[$i]['tax_class_id'],
                'tax' => xtc_get_tax_rate($products[$i]['tax_class_id'], $taxAddress['entry_country_id'], $taxAddress['entry_zone_id']),
                'tax_description' => xtc_get_tax_description($products[$i]['tax_class_id'], $taxAddress['entry_country_id'], $taxAddress['entry_zone_id']),
                'price' =>  $productPrice ,
                'final_price' => $productPrice * $products[$i]['quantity'],
                'shipping_time'=>$products[$i]['shipping_time'],
                'weight' => $products[$i]['weight'],
                'id' => $products[$i]['id']
            ];

            if ($products[$i]['attributes']) {
                $subindex = 0;
                reset($products[$i]['attributes']);
                while (list($option, $value) = each($products[$i]['attributes'])) {
                    $attributesQuery = xtc_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $products[$i]['id'] . "' and pa.options_id = '" . $option . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $value . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $_SESSION['languages_id'] . "' and poval.language_id = '" . $_SESSION['languages_id'] . "'");
                    $attributes = xtc_db_fetch_array($attributesQuery);

                    $this->products[$index]['attributes'][$subindex] = [
                        'option' => $attributes['products_options_name'],
                        'value' => $attributes['products_options_values_name'],
                        'option_id' => $option,
                        'value_id' => $value,
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
                    $this->info['tax_groups'][$taxIndex] += (($shownPriceTax / (100 + $productTax)) * $productTax);
                } else {
                    $this->info['tax'] += $shownPrice - ($shownPrice / (($productTax < 10) ? "1.0" . str_replace('.', '', $productTax) : "1." . str_replace('.', '', $productTax)));
                    //$this->info['tax_groups'][TAX_ADD_TAX . "$productTaxDescription"] += (($shownPrice / (100 + $productTax)) * $productTax);
                    $this->info['tax_groups'][$taxIndex] += (($shownPrice / (100 + $productTax)) * $productTax);
                }
            } else {
                $taxIndex = TAX_NO_TAX . $productTaxDescription;
                if (!isset($this->info['tax_groups'][$taxIndex])) {
                    $this->info['tax_groups'][$taxIndex] = 0;
                }

                if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1) {
                    // BOF - web28 - 2010-05-06 - PayPal API Modul / Paypal Express Modul
                    //$this->info['tax'] += ($shownPriceTax / 100) * ($productTax);
                    $this->taxDiscount[$products[$i]['tax_class_id']] += ($shownPriceTax / 100) * $productTax;
                    // EOF - web28 - 2010-05-06 - PayPal API Modul / Paypal Express Modul
                    //$this->info['tax_groups'][TAX_NO_TAX . "$productTaxDescription"] += ($shownPriceTax / 100) * ($productTax);
                    $this->info['tax_groups'][$taxIndex] += ($shownPriceTax / 100) * ($productTax);
                } else {
                    $this->info['tax'] += ($shownPrice / 100) * ($productTax);
                    //$this->info['tax_groups'][TAX_NO_TAX . "$productTaxDescription"] += ($shownPrice / 100) * ($productTax);
                    $this->info['tax_groups'][$taxIndex] += ($shownPrice / 100) * ($productTax);
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
}