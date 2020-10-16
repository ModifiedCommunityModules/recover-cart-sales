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

    private function getAttributes($optionId, $optionValueId, $languageId)
    {
        $sql = "SELECT popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix FROM " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa WHERE pa.products_id = '" . $productId . "' AND pa.options_id = '" . $optionId . "' AND pa.options_id = popt.products_options_id AND pa.options_values_id = '" . $optionValueId . "' AND pa.options_values_id = poval.products_options_values_id AND popt.language_id = '" . $languageId . "' AND poval.language_id = '" . $languageId . "'";
                    
        $query = xtc_db_query($sql);
        return xtc_db_fetch_array($query);
    }

    private function buildInfoArrayFromSession()
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

        $this->info = $this->buildInfoArrayFromSession();
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
}