<?php
/* ---------------------------------------------------------------------------------------------
$Id: recover_cart_sales.php,v 3.0 2007/05/15 06:10:35 Estelco Exp $
Recover Cart Sales Tool v3.0 for xtCommerce

Copyright (c) 2007 Andre Estel www.estelco.de

Copyright (c) 2003-2005 JM Ivler / Ideas From the Deep / OSCommerce
Released under the GNU General Public License
------------------------------------------------------------------------------------------------
Based on an original release of unsold carts by: JM Ivler

That was modifed by Aalst (aalst@aalst.com) until v1.7 of stats_unsold_carts.php

Then, the report was turned into a sales tool (recover_cart_sales.php) by
JM Ivler based on the scart.php program that was written off the Oct 8 unsold carts code release.

Modifed by Aalst (recover_cart_sales.php,v 1.2 ... 1.36)
aalst@aalst.com

Modifed by willross (recover_cart_sales.php,v 1.4)
reply@qwest.net
- don't forget to flush the 'scart' db table every so often

Modified by Lane Roathe (recover_cart_sales.php,v 1.4d .. v2.11)
lane@ifd.com    www.osc-modsquad.com / www.ifd.com
-----------------------------------------------------------------------------------------------*/

use currencies as Currencies;
use main as Main;
use xtcPrice as XtcPrice;
use order_total as OrderTotal;
use shipping as Shipping;
use payment as Payment;
use shoppingCart as ShoppingCart;
use ModifiedCommunityModules\RecoverCartSales\Classes\Order;
use ModifiedCommunityModules\RecoverCartSales\Classes\ShoppingCart as RcsShoppingCart;

require_once 'includes/application_top.php';
require_once DIR_FS_DOCUMENT_ROOT . '/vendor-no-composer/autoload.php';

// Load from admin
require_once DIR_WS_CLASSES . 'currencies.php';

// Load from frontend
require_once DIR_FS_INC . 'xtc_image_button.inc.php';
require_once DIR_FS_INC . 'xtc_php_mail.inc.php';
require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'main.php';
require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'xtcPrice.php';
require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'payment.php';
require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'shipping.php';
require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'order_total.php';
require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'shopping_cart.php';

$currencies = new Currencies();
$configuration = new Configuration('MODULE_MCM_RECOVER_CART_SALES');

$action = $_GET['action'] ?? '';
$getDelete = $_GET['delete'] ?? '';

function getCustomerStatus(int $customerId, int $languageId): array
{
    $sql = "SELECT c.customers_status, cs.customers_status_name,  cs.customers_status_image, cs.customers_status_ot_discount_flag, cs.customers_status_ot_discount FROM " . TABLE_CUSTOMERS . " c, " . TABLE_CUSTOMERS_STATUS . " cs WHERE c.customers_status=cs.customers_status_id AND c.customers_id=" . (int) $customerId . " AND cs.language_id=" . (int) $languageId;

    $query = xtc_db_query($sql);
    return xtc_db_fetch_array($statusQuery);
}

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

function seadate($day)
{
    $rawtime = strtotime("-" . $day . " days");
    $ndate = date("Ymd", $rawtime);
    return $ndate;
}

function cart_date_short($raw_date)
{
    if ( ($raw_date == '00000000') || ($raw_date == '') ) {
        return false;
    }

    $year = substr($raw_date, 0, 4);
    $month = (int) substr($raw_date, 4, 2);
    $day = (int) substr($raw_date, 6, 2);

    if (@date('Y', mktime(0, 0, 0, $month, $day, $year)) == $year) {
        return date(DATE_FORMAT, mktime(0, 0, 0, $month, $day, $year));
    } else {
        return preg_replace('#2037' . '$#', $year, date(DATE_FORMAT, mktime(0, 0, 0, $month, $day, 2037)));  
    }
}

// This will return a list of customers with sessions. Handles either the mysql or file case
// Returns an empty array if the check sessions flag is not true (empty array means same SQL statement can be used)
function getCustomerSessions()
{
    $configuration = new Configuration('MODULE_MCM_RECOVER_CART_SALES');
    $customerSessionIds = [];

    if ($configuration->checkSessions == 'true' ) {
        if (STORE_SESSIONS == 'mysql') {
            // --- DB RECORDS ---
            $sesquery = xtc_db_query("SELECT value FROM " . TABLE_SESSIONS . " WHERE 1");
            while ($ses = xtc_db_fetch_array($sesquery)) {
                if ( preg_match( "/customer_id[^\"]*\"([0-9]*)\"/", $ses['value'], $custval ) )
                $customerSessionIds[] = $custval[1];
            }
        } else {
            if ($handle = opendir(xtc_session_save_path())) {
                while (false !== ($file = readdir($handle)) ) {
                    if ($file != "." && $file != "..") {
                        $file = xtc_session_save_path() . '/' . $file;    // create full path to file!
                        if ($fp = fopen($file, 'r')) {
                            $val = fread($fp, filesize($file));
                            fclose($fp);

                            if (preg_match( "/customer_id[^\"]*\"([0-9]*)\"/", $val, $custval)) {
                                $customerSessionIds[] = $custval[1];
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }
    }
    return $customerSessionIds;
}

if ($action == 'complete') {
    $customerId = $_GET['customer_id'] ?? 0;
    $_SESSION['saved_cart'] = $_SESSION['cart'];

    $main = new Main();

    $status = getCustomerStatus($customerId, $_SESSION['languages_id']);

    $xtPrice = new XtcPrice(DEFAULT_CURRENCY, $status['customers_status']);
    $rcsShoppingCart = new RcsShoppingCart();
    $_SESSION['cart'] = new ShoppingCart();

    $rcsShoppingCart->restoreCustomersCart($_SESSION['cart'], $customerId);

    // load selected payment module
    $_SESSION['payment'] = $configuration->defaultPayment;

    $paymentModules = new Payment($_SESSION['payment']);

    $order = new Order($customerId);

    if ($order->billing['country']['iso_code_2'] != '' && $order->delivery['country']['iso_code_2'] == '') {
        $_SESSION['delivery_zone'] = $order->billing['country']['iso_code_2'];
    } else {
        $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
    }

    // load the selected shipping module
    $shipping_num_boxes = 1;
    $_SESSION['shipping'] = $configuration->defaultShipping;

    $shippingModules = new Shipping($_SESSION['shipping']);

    list($module, $method) = explode('_', $_SESSION['shipping']);
    if (is_object($$module)) {
        $quote = $shippingModules->quote($method, $module);
        if (isset($quote['error'])) {
            unset($_SESSION['shipping']);
        } else {
            if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
                $_SESSION['shipping'] = [
                    'id' => $_SESSION['shipping'],
                    'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                    'cost' => $quote[0]['methods'][0]['cost']
                ];
            }
        }
    } else {
        $shippingModules = MODULE_SHIPPING_INSTALLED;
    }
    $order = new Order($customerId);

    // load the before_process function from the payment modules
    //$paymentModules->before_process();

    $orderTotalModules = new OrderTotal();
    $orderTotals = $orderTotalModules->process();

    $tmp = false;
    $tmpStatus = $order->info['order_status'];

    if ($status['customers_status_ot_discount_flag'] == 1) {
        $discount = $status['customers_status_ot_discount'];
    } else {
        $discount = '0.00';
    }

    $sqlDataArray = [
        'customers_id' => $customerId,
        'customers_name' => $order->customer['firstname'].' '.$order->customer['lastname'],
        'customers_firstname' => $order->customer['firstname'],
        'customers_lastname' => $order->customer['lastname'],
        'customers_cid' => $order->customer['csID'],
        'customers_vat_id' => '',
        'customers_company' => $order->customer['company'],
        'customers_status' => $status['customers_status'],
        'customers_status_name' => $status['customers_status_name'],
        'customers_status_image' => $status['customers_status_image'],
        'customers_status_discount' => $discount,
        'customers_street_address' => $order->customer['street_address'],
        'customers_suburb' => $order->customer['suburb'],
        'customers_city' => $order->customer['city'],
        'customers_postcode' => $order->customer['postcode'],
        'customers_state' => $order->customer['state'],
        'customers_country' => $order->customer['country']['title'],
        'customers_telephone' => $order->customer['telephone'],
        'customers_email_address' => $order->customer['email_address'],
        'customers_address_format_id' => $order->customer['format_id'],
        'delivery_name' => $order->delivery['firstname'].' '.$order->delivery['lastname'],
        'delivery_firstname' => $order->delivery['firstname'],
        'delivery_lastname' => $order->delivery['lastname'],
        'delivery_company' => $order->delivery['company'],
        'delivery_street_address' => $order->delivery['street_address'],
        'delivery_suburb' => $order->delivery['suburb'],
        'delivery_city' => $order->delivery['city'],
        'delivery_postcode' => $order->delivery['postcode'],
        'delivery_state' => $order->delivery['state'],
        'delivery_country' => $order->delivery['country']['title'],
        'delivery_country_iso_code_2' => $order->delivery['country']['iso_code_2'],
        'delivery_address_format_id' => $order->delivery['format_id'],
        'billing_name' => $order->billing['firstname'].' '.$order->billing['lastname'],
        'billing_firstname' => $order->billing['firstname'],
        'billing_lastname' => $order->billing['lastname'],
        'billing_company' => $order->billing['company'],
        'billing_street_address' => $order->billing['street_address'],
        'billing_suburb' => $order->billing['suburb'],
        'billing_city' => $order->billing['city'],
        'billing_postcode' => $order->billing['postcode'],
        'billing_state' => $order->billing['state'],
        'billing_country' => $order->billing['country']['title'],
        'billing_country_iso_code_2' => $order->billing['country']['iso_code_2'],
        'billing_address_format_id' => $order->billing['format_id'],
        'payment_method' => $order->info['payment_method'],
        'payment_class' => $order->info['payment_class'],
        'shipping_method' => $order->info['shipping_method'],
        'shipping_class' => $order->info['shipping_class'],
        //'cc_type' => $order->info['cc_type'],
        //'cc_owner' => $order->info['cc_owner'],
        //'cc_number' => $order->info['cc_number'],
        //'cc_expires' => $order->info['cc_expires'],
        //'cc_start' => $order->info['cc_start'],
        //'cc_cvv' => $order->info['cc_cvv'],
        //'cc_issue' => $order->info['cc_issue'],
        'date_purchased' => 'now()',
        'orders_status' => $tmpStatus,
        'currency' => $order->info['currency'],
        'currency_value' => $order->info['currency_value'],
        'customers_ip' => $customers_ip,
        'language' => $_SESSION['language'],
        'comments' => $order->info['comments']
    ];

    xtc_db_perform(TABLE_ORDERS, $sqlDataArray);
    $insertId = xtc_db_insert_id();
    $_SESSION['tmp_oID'] = $insertId;

    foreach ($orderTotals as $orderTotal) {
        $sqlDataArray = [
            'orders_id' => $insertId,
            'title' => $orderTotal['title'],
            'text' => $orderTotal['text'],
            'value' => $orderTotal['value'],
            'class' => $orderTotal['code'],
            'sort_order' => $orderTotal['sort_order']
        ];
        xtc_db_perform(TABLE_ORDERS_TOTAL, $sqlDataArray);
    }

    $customerNotification = (SEND_EMAILS == 'true') ? '1' : '0';
    $sqlDataArray = [
        'orders_id' => $insertId, 
        'orders_status_id' => $order->info['order_status'],
        'date_added' => 'now()',
        'customer_notified' => $customerNotification,
        'comments' => $order->info['comments']
    ];
    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sqlDataArray);

    // initialized for the email confirmation
    $productsOrdered = '';
    $productsOrdered_html = '';
    $subtotal = 0;
    $totalTax = 0;

    for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
        // Stock Update - Joao Correia
        if (STOCK_LIMITED == 'true') {
            if (DOWNLOAD_ENABLED == 'true') {
                $stockQueryRaw = "SELECT products_quantity, pad.products_attributes_filename
                                        FROM " . TABLE_PRODUCTS . " p
                                        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                        ON p.products_id = pa.products_id
                                        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                        ON pa.products_attributes_id = pad.products_attributes_id
                                        WHERE p.products_id = '" . xtc_get_prid($order->products[$i]['id']) . "'";
                // Will work with only one option for downloadable products
                // otherwise, we have to build the query dynamically with a loop
                $productsAttributes = $order->products[$i]['attributes'];
                if (is_array($productsAttributes)) {
                    $stockQueryRaw .= " AND pa.options_id = '" . $productsAttributes[0]['option_id'] . "' AND pa.options_values_id = '" . $productsAttributes[0]['value_id'] . "'";
                }
                $stockQuery = xtc_db_query($stockQueryRaw);
            } else {
                $stockQuery = xtc_db_query("SELECT products_quantity FROM " . TABLE_PRODUCTS . " WHERE products_id = '" . xtc_get_prid($order->products[$i]['id']) . "'");
            }

            if (xtc_db_num_rows($stockQuery) > 0) {
                $stockValues = xtc_db_fetch_array($stockQuery);
                // do not decrement quantities if products_attributes_filename exists
                if ((DOWNLOAD_ENABLED != 'true') || (!$stockValues['products_attributes_filename'])) {
                    $stockLeft = $stockValues['products_quantity'] - $order->products[$i]['qty'];
                } else {
                    $stockLeft = $stockValues['products_quantity'];
                }
                xtc_db_query("UPDATE " . TABLE_PRODUCTS . " SET products_quantity = '" . $stockLeft . "' WHERE products_id = '" . xtc_get_prid($order->products[$i]['id']) . "'");
            }
        }

        // Update products_ordered (for bestsellers list)
        xtc_db_query("UPDATE " . TABLE_PRODUCTS . " SET products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " WHERE products_id = '" . xtc_get_prid($order->products[$i]['id']) . "'");

        $sqlDataArray = [
            'orders_id' => $insertId,
            'products_id' => xtc_get_prid($order->products[$i]['id']),
            'products_model' => $order->products[$i]['model'],
            'products_name' => $order->products[$i]['name'],
            'products_shipping_time'=>$order->products[$i]['shipping_time'],
            'products_price' => $order->products[$i]['price'],
            'final_price' => $order->products[$i]['final_price'],
            'products_tax' => $order->products[$i]['tax'],
            'products_discount_made' => $order->products[$i]['discount_allowed'],
            'products_quantity' => $order->products[$i]['qty'],
            'allow_tax' => $_SESSION['customers_status']['customers_status_show_price_tax']
        ];

        xtc_db_perform(TABLE_ORDERS_PRODUCTS, $sqlDataArray);
        $orderProductId = xtc_db_insert_id();

        // Aenderung Specials Quantity Anfang
        $specialsResult = xtc_db_query("SELECT products_id, specials_quantity FROM " . TABLE_SPECIALS . " WHERE products_id = '" . xtc_get_prid($order->products[$i]['id']) . "' ");
        if (xtc_db_num_rows($specialsResult)) {
            $spq = xtc_db_fetch_array($specialsResult);

            $new_sp_quantity = ($spq['specials_quantity'] - $order->products[$i]['qty']);

            if ($new_sp_quantity >= 1) {
                xtc_db_query("UPDATE " . TABLE_SPECIALS . " SET specials_quantity = '" . $new_sp_quantity . "' WHERE products_id = '" . xtc_get_prid($order->products[$i]['id']) . "' ");
            } else {
                xtc_db_query("UPDATE " . TABLE_SPECIALS . " SET status = '0', specials_quantity = '" . $new_sp_quantity . "' WHERE products_id = '" . xtc_get_prid($order->products[$i]['id']) . "' ");
            }
        }
        // Aenderung Ende

        $orderTotalModules->update_credit_account($i); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM
        //------insert customer choosen option to order--------
        $attributes_exist = '0';
        $productsOrderedAttributes = '';
        if (isset($order->products[$i]['attributes'])) {
            $attributes_exist = '1';
            for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j ++) {
                if (DOWNLOAD_ENABLED == 'true') {
                    $attributesQuery = "SELECT popt.products_options_name,
                                              poval.products_options_values_name,
                                              pa.options_values_price,
                                              pa.price_prefix,
                                              pad.products_attributes_maxdays,
                                              pad.products_attributes_maxcount,
                                              pad.products_attributes_filename
                                       FROM " . TABLE_PRODUCTS_OPTIONS . " popt,
                                            " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                            " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                       LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                       ON pa.products_attributes_id = pad.products_attributes_id
                                       WHERE pa.products_id = '" . $order->products[$i]['id'] . "'
                                       AND pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                       AND pa.options_id = popt.products_options_id
                                       AND pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                       AND pa.options_values_id = poval.products_options_values_id
                                       AND popt.language_id = " . (int) $_SESSION['languages_id'] . "
                                       AND poval.language_id = " . (int) $_SESSION['languages_id'];
                    $attributes = xtc_db_query($attributesQuery);
                } else {
                    $attributes = xtc_db_query("SELECT popt.products_options_name,
                                             poval.products_options_values_name,
                                             pa.options_values_price,
                                             pa.price_prefix
                                      FROM " . TABLE_PRODUCTS_OPTIONS . " popt,
                                           " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                           " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                      WHERE pa.products_id = '" . $order->products[$i]['id'] . "'
                                      AND pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                      AND pa.options_id = popt.products_options_id
                                      AND pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                      AND pa.options_values_id = poval.products_options_values_id
                                      AND popt.language_id = " . (int) $_SESSION['languages_id'] . "
                                      AND poval.language_id = " . (int) $_SESSION['languages_id']);
                }
                // update attribute stock
                xtc_db_query("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES."
                      SET attributes_stock = attributes_stock - '" . $order->products[$i]['qty'] . "'
                                  WHERE products_id = '" . $order->products[$i]['id'] . "'
                                  AND options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                  AND options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'");

                $attributesValues = xtc_db_fetch_array($attributes);

                $sqlDataArray = [
                    'orders_id' => $insertId,
                    'orders_products_id' => $orderProductId,
                    'products_options' => $attributesValues['products_options_name'],
                    'products_options_values' => $attributesValues['products_options_values_name'],
                    'options_values_price' => $attributesValues['options_values_price'],
                    'price_prefix' => $attributesValues['price_prefix']
                ];

                xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sqlDataArray);

                if ((DOWNLOAD_ENABLED == 'true') && isset ($attributesValues['products_attributes_filename']) && xtc_not_null($attributesValues['products_attributes_filename'])) {
                    $sqlDataArray = [
                        'orders_id' => $insertId,
                        'orders_products_id' => $orderProductId,
                        'orders_products_filename' => $attributesValues['products_attributes_filename'],
                        'download_maxdays' => $attributesValues['products_attributes_maxdays'],
                        'download_count' => $attributesValues['products_attributes_maxcount']
                    ];
                    xtc_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sqlDataArray);
                }
            }
        }
        //------insert customer choosen option eof ----
        $totalWeight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
        $totalTax += xtc_calculate_tax($totalProductsPrice, $productsTax) * $order->products[$i]['qty'];
        $totalCost += $totalProductsPrice;
    }

    if ($configuration->deleteCompletedOrders == 'true') {
        xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id=" . $customerId);
        xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id=" . $customerId);
        xtc_db_query("DELETE FROM " . TABLE_MCM_RECOVER_CART_SALES . " WHERE customers_id=" . $customerId);
    }

    $_SESSION['cart'] = $_SESSION['saved_cart'];
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, "oID=" . $insertId . "&action=edit"));
}

// Delete Entry Begin
if ($action == 'delete') {
    $customerId = (int) $_GET['customer_id'];
    xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id=" . $customerId);
    xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id=" . $customerId);
    xtc_db_query("DELETE FROM " . TABLE_MCM_RECOVER_CART_SALES . " WHERE customers_id=" . $customerId);

    xtc_redirect(xtc_href_link(FILENAME_RECOVER_CART_SALES, 'delete=1&customer_id='. $_GET['customer_id'] . '&tdate=' . $_GET['tdate']));
}

if ($getDelete) {
    $messageStack->add(MESSAGE_STACK_CUSTOMER_ID . $_GET['customer_id'] . MESSAGE_STACK_DELETE_SUCCESS, 'success');
}

// Delete Entry End
$tdate = $_POST['tdate'];
if ($tdate == '') {
    $tdate = $configuration->baseDays;
}
?>

<?php require DIR_WS_INCLUDES . 'head.php'; ?>

<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>


<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
    <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
            <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
                <!-- left_navigation //-->
                <?php require DIR_WS_INCLUDES . 'column_left.php'; ?>
                <!-- left_navigation_eof //-->
            </table>
        </td>
        <!-- body_text //-->

        <td width="100%" valign="top">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                
                <?php if (count($_POST['custid']) > 0 ) {  // Are we doing an e-mail to some customers? ?>
                    <tr>
                        <td class="pageHeading" align="left" colspan=2 width="50%"><?php echo HEADING_TITLE; ?> </td>
                        <td class="pageHeading" align="left" colspan=4 width="50%"><?php echo HEADING_EMAIL_SENT; ?> </td>
                    </tr>

                    <tr class="dataTableHeadingRow">
                        <td class="dataTableHeadingContent" align="left" colspan="1" width="15%" nowrap><?php echo TABLE_HEADING_CUSTOMER; ?></td>
                        <td class="dataTableHeadingContent" align="left" colspan="1" width="30%" nowrap>&nbsp;</td>
                        <td class="dataTableHeadingContent" align="left" colspan="1" width="25%" nowrap>&nbsp;</td>
                        <td class="dataTableHeadingContent" align="left" colspan="1" width="10%" nowrap>&nbsp;</td>
                        <td class="dataTableHeadingContent" align="left" colspan="1" width="10%" nowrap>&nbsp;</td>
                        <td class="dataTableHeadingContent" align="left" colspan="1" width="10%" nowrap>&nbsp;</td>
                    </tr>
                    
                    <tr>
                        &nbsp;<br>
                    </tr>

                    <tr class="dataTableHeadingRow">
                        <td class="dataTableHeadingContent" align="left"   colspan="1"  width="15%" nowrap><?php echo TABLE_HEADING_MODEL; ?></td>
                        <td class="dataTableHeadingContent" align="left"   colspan="2"  width="55%" nowrap><?php echo TABLE_HEADING_DESCRIPTION; ?></td>
                        <td class="dataTableHeadingContent" align="center" colspan="1"  width="10%" nowrap> <?php echo TABLE_HEADING_QUANTY; ?></td>
                        <td class="dataTableHeadingContent" align="right"  colspan="1"  width="10%" nowrap><?php echo TABLE_HEADING_PRICE; ?></td>
                        <td class="dataTableHeadingContent" align="right"  colspan="1"  width="10%" nowrap><?php echo TABLE_HEADING_TOTAL; ?></td>
                    </tr>

                    <?php foreach ($_POST['custid'] as $customerId) {
                        $quantity = [];
                        $productsData = [];
                        $quantityQuery = xtc_db_query("SELECT products_id pid, customers_basket_quantity qty FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id=" . $customerId);

                        while ($quantityResult = xtc_db_fetch_array($quantityQuery)) {
                            $quantity[(int) $quantityResult['pid']] += $quantityResult['qty'];
                        }

                        $query1 = xtc_db_query("SELECT cb.products_id pid,
                                    cb.customers_basket_quantity qty,
                                    cb.customers_basket_date_added bdate,
                                    cb.checkout_site site,
                                    cb.language,
                                    cus.customers_firstname fname,
                                    cus.customers_lastname lname,
                                    cus.customers_gender,
                                    cus.customers_email_address email,
                                    co.countries_iso_code_2 iso
                            FROM      " . TABLE_CUSTOMERS_BASKET . " cb,
                                    " . TABLE_CUSTOMERS . " cus,
                                    " . TABLE_ADDRESS_BOOK . " ab,
                                    " . TABLE_COUNTRIES . " co
                            WHERE     cb.customers_id = cus.customers_id
                            AND       cus.customers_id = '" . $customerId."'
                            AND       cus.customers_default_address_id = ab.address_book_id
                            AND       co.countries_id=ab.entry_country_id
                            ORDER BY  cb.customers_basket_date_added desc ");

                        $queryRowCount = xtc_db_num_rows($query1);
                        for ($i = 0; $i < $queryRowCount; $i++) {
                            $inrec = xtc_db_fetch_array($query1);
                            $attributePrice = 0;
                            // set new cline and curcus
        
                            if ($lastCustomerId != $customerId) {
                                if ($lastCustomerId != "") {
                                    $textTotal = $configuration->showBruttoPrice == 'true' ? TABLE_CART_TOTAL_BRUTTO : TABLE_CART_TOTAL;
                                    $currentLine .= "
                                        <tr>
                                            <td class='dataTableContent' align='right' colspan='6' nowrap><b>" . $textTotal . "</b>" . $currencies->format($totalPrice) . "</td>
                                        </tr>
                                        <tr>
                                            <td colspan='6' align='right'><a class=\"button\" href=" . xtc_href_link(FILENAME_RECOVER_CART_SALES, "action=delete&customer_id=" . $customerId . "&tdate=" . $tdate) . ">" . BUTTON_DELETE . "</a></td>
                                        </tr>\n";
                                    echo $currentLine;
                                }
                                
                                $currentLine = "<tr> <td class='dataTableContent' align='left' colspan='6' nowrap><a href='" . xtc_href_link(FILENAME_CUSTOMERS, 'search=' . $inrec['lname'], 'NONSSL') . "'>" . $inrec['fname'] . " " . $inrec['lname'] . "</a>" . $customer . "</td></tr>";
                                $totalPrice = 0;
                            }
                            $lastCustomerId = $customerId;

                            // get the shopping cart
                            $query2 = xtc_db_query("SELECT p.products_price price,
                                        p.products_model model,
                                        p.products_tax_class_id tax,
                                        p.products_image image,
                                        pd.products_name name
                                FROM    " . TABLE_PRODUCTS . " p,
                                        " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                WHERE   p.products_id = '" . $inrec['pid'] . "' and
                                        pd.products_id = p.products_id and
                                        pd.language_id = " . (int) $_SESSION['languages_id'] );

                            $inrec2 = xtc_db_fetch_array($query2);

                            $specialPrice = xtc_get_products_special_price_ow($inrec['pid'], $customerId, ($inrec['qty'] < $quantity[(int) $inrec['pid']] ? $quantity[(int) $inrec['pid']] : $inrec['qty']));
        
                            // BEGIN OF ATTRIBUTE DB CODE
                            $productAttributes = ''; // DO NOT DELETE
                            if ($configuration->showAttributes == 'true') {
                                $attributeQuery = xtc_db_query("SELECT cba.products_id pid,
                                                po.products_options_name poname,
                                                pov.products_options_values_name povname,
                                                pa.options_values_price price
                                            FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " cba,
                                                " . TABLE_PRODUCTS_OPTIONS . " po,
                                                " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov,
                                                " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                            WHERE cba.products_id = '" . $inrec['pid'] . "'
                                            AND cba.customers_id = " . $customerId . "
                                            AND po.products_options_id = cba.products_options_id
                                            AND pov.products_options_values_id = cba.products_options_value_id
                                            AND pa.products_id = " . (int) $inrec['pid'] . "
                                            AND pa.options_id = cba.products_options_id
                                            AND pa.options_values_id = cba.products_options_value_id
                                            AND po.language_id = " . (int) $_SESSION['languages_id'] . "
                                            AND pov.language_id = " . (int) $_SESSION['languages_id']);
                                $hasAttributes = false;

                                if (xtc_db_num_rows($attributeQuery)) {
                                    $hasAttributes = true;
                                    $productAttributes = '<br>';
                                    while ($attribrecs = xtc_db_fetch_array($attributeQuery)) {
                                        $productAttributes .= '<small><em> - ' . $attribrecs['poname'] . ' ' . $attribrecs['povname'] . '</em></small><br >';
                                        $attributePrice += $attribrecs['price'];
                                    }
                                }
                            }

                            if( $specialPrice == 0 ) {
                                $specialPrice = $inrec2['price'];
                            }
                            $specialPrice += $attributePrice;
        
                            if ($configuration->showBruttoPrice == 'true') {
                                $tax = xtc_get_tax_rate($inrec2['tax']);
                                $specialPrice = xtc_add_tax($specialPrice, $tax);
                            }

                            $totalPrice = $totalPrice + ($inrec['qty'] * $specialPrice);
                            $productPriceFormated  = $currencies->format($specialPrice);
                            $totalProductPriceFormated = $currencies->format($inrec['qty'] * $specialPrice);

                            $currentLine .= "<tr class='dataTableRow'>
                                        <td class='dataTableContent' align='left'   width='15%' nowrap>" . ($inrec2['model'] ? $inrec2['model'] : '&nbsp;') . "</td>
                                        <td class='dataTableContent' align='left'  colspan='2' width='55%'><a href='" . xtc_href_link(FILENAME_CATEGORIES, 'action=new_product&pID=' . $inrec['pid'], 'NONSSL') . "'>" . $inrec2['name'] . "</a></td>
                                        <td class='dataTableContent' align='center' width='10%' nowrap>" . $inrec['qty'] . "</td>
                                        <td class='dataTableContent' align='right'  width='10%' nowrap>" . $productPriceFormated . "</td>
                                        <td class='dataTableContent' align='right'  width='10%' nowrap>" . $totalProductPriceFormated . "</td>
                                    </tr>";

                            $productsData[] = [
                                'QUANTITY' => $inrec['qty'],
                                'NAME' => $inrec2['name'],
                                'LINK' => xtc_catalog_href_link('product_info.php', 'info=p'. $inrec['pid']),
                                'IMAGE' => HTTP_SERVER.DIR_WS_CATALOG_INFO_IMAGES . $inrec2['image']
                            ];
                        }

                        $currentLine .= "</td></tr>";

                        if ($inrec['language'] == null) {
                            switch($inrec['iso']) {
                                case 'DE':
                                case 'AT':
                                case 'CH':
                                    $inrec['language'] = 'german';
                                    break;
                                    /*
                                    case 'IT':
                                    $inrec['language'] = 'italian';
                                    break;

                                    case 'ES':
                                    case 'AR':
                                    case 'MX':
                                    $inrec['language'] = 'spanish';
                                    break;

                                    case 'FR':
                                    case 'BE':
                                    case 'LU':
                                    case 'LI':
                                    $inrec['language'] = 'french';
                                    break;
                                    */
                                default:
                                    $inrec['language'] = 'english';
                            }
                        }

                        $cquery = xtc_db_query("SELECT * FROM orders WHERE customers_id = '" . $customerId . "'" );

                        $smarty = new Smarty();
                        $smarty->assign('language', $inrec['language']);
                        $smarty->caching = false;
                        $smarty->template_dir = DIR_FS_CATALOG . 'templates';
                        $smarty->compile_dir = DIR_FS_CATALOG . 'templates_c';
                        $smarty->config_dir = DIR_FS_CATALOG . 'lang';
                        $smarty->assign('tpl_path', 'templates/' . CURRENT_TEMPLATE . '/');
                        $smarty->assign('logo_path', HTTP_SERVER . DIR_WS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/img/');
                        $smarty->assign('products_data', $productsData);
                        $smarty->assign('LOGIN', xtc_catalog_href_link('login.php', '', 'SSL'));

                        //$custname = $inrec['fname']." ".$inrec['lname'];
                        if ($configuration->emailFriendly == 'true') {
                            $smarty->assign('GENDER', $inrec['customers_gender']);
                            $smarty->assign('FIRSTNAME', $inrec['fname']);
                            $smarty->assign('LASTNAME', $inrec['lname']);
                        } else {
                            $smarty->assign('GENDER', false);
                        }

                        if (xtc_db_num_rows($cquery) < 1) {
                            $smarty->assign('NEW', true);
                        } else {
                            $smarty->assign('NEW', false);
                        }

                        $smarty->assign('STORE_LINK', xtc_catalog_href_link('', ''));
                        $smarty->assign('STORE_NAME', STORE_NAME);

                        $smarty->assign('MESSAGE', $_POST['message']);

                        $outEmailAddr = '"' . $custname . '" <' . $inrec['email'] . '>';
                        if (xtc_not_null($configuration->emailCopiesTo)) {
                            $outEmailAddr .= ', ' . $configuration->emailCopiesTo;
                        }

                        $smarty->caching = false;

                        $htmlMail = $smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/' . $inrec['language'] . '/cart_mail.html');
                        $txtMail = $smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/' . $inrec['language'] . '/cart_mail.txt');

                        if ($inrec['email'] != '') {
                            xtc_php_mail(EMAIL_SUPPORT_ADDRESS, EMAIL_SUPPORT_NAME, $inrec['email'] , $custname , $configuration->emailCopiesTo, EMAIL_SUPPORT_REPLY_ADDRESS, EMAIL_SUPPORT_REPLY_ADDRESS_NAME, '', '', EMAIL_TEXT_SUBJECT, $htmlMail, $txtMail);
                        }

                        // Debugging
                        /*
                            $fp = fopen('cart_mail.html', 'w');
                            fputs($fp, $htmlMail);
                            fclose($fp);
                            $fp = fopen('cart_mail.txt', 'w');
                            fputs($fp, $txtMail);
                            fclose($fp);
                        */

                        // See if a record for this customer already exists; if not create one and if so update it
                        $doneQuery = xtc_db_query("SELECT * FROM ". TABLE_MCM_RECOVER_CART_SALES ." WHERE customers_id = '" . $customerId . "'");
                        if (xtc_db_num_rows($doneQuery) == 0) {
                            xtc_db_query("INSERT into " . TABLE_MCM_RECOVER_CART_SALES . " (customers_id, date_added, date_modified ) values ('" . $customerId . "', '" . seadate('0') . "', '" . seadate('0') . "')");
                        } else {
                            xtc_db_query("update " . TABLE_MCM_RECOVER_CART_SALES . " set date_modified = '" . seadate('0') . "' WHERE customers_id = " . $customerId );
                        }
                        echo $currentLine;
                        $currentLine = "";
                        $textTotal = $configuration->showBruttoPrice == 'true' ? TABLE_CART_TOTAL_BRUTTO : TABLE_CART_TOTAL;
                    }

                    echo "<tr><td colspan=8 align='right' class='dataTableContent'><b>" . $textTotal . "</b>" . $currencies->format($totalPrice) . "</td> </tr>";
                    echo "<tr><td colspan=6 align='right'><a class=\"button\" href=" . xtc_href_link(FILENAME_RECOVER_CART_SALES, "action=delete&customer_id=" . $customerId . "&tdate=" . $tdate) . ">" . BUTTON_DELETE . "</a></td>  </tr>\n";
                    echo "<tr><td colspan=6 align=center><a href=".$PHP_SELF.">" . TEXT_RETURN . "</a></td></tr>";
                
                } else { // we are NOT doing an e-mail to some customers ?>

                    <!-- REPORT TABLE BEGIN //-->
                    <tr>
                        <td class="pageHeading" align="left" width="50%" colspan="4"><?php echo HEADING_TITLE; ?></td>
                        <td class="pageHeading" align="right" width="50%" colspan="4">
                            <?php echo xtc_draw_form('recover_cart_sales', 'recover_cart_sales.php', '', 'post', '') . PHP_EOL; ?>
                                <table align="right" width="100%">
                                    <tr class="dataTableContent" align="right">
                                        <td><?php echo DAYS_FIELD_PREFIX; ?><input type=text size=4 width=4 value=<?php echo $tdate; ?> name=tdate><?php echo DAYS_FIELD_POSTFIX; ?><input type=submit value="<?php echo DAYS_FIELD_BUTTON; ?>"></td>
                                    </tr>
                                </table>
                            </form>
                        </td>
                    </tr>
                    
                    <?php echo xtc_draw_form('recover_cart_sales', 'recover_cart_sales.php', '', 'post', '') . PHP_EOL; ?>
                        <tr class="dataTableHeadingRow">
                            <td class="dataTableHeadingContent" align="left" colspan="2" width="10%" nowrap><?php echo TABLE_HEADING_CONTACT; ?></td>
                            <td class="dataTableHeadingContent" align="left" colspan="1" width="15%" nowrap><?php echo TABLE_HEADING_DATE; ?></td>
                            <td class="dataTableHeadingContent" align="left" colspan="1" width="30%" nowrap><?php echo TABLE_HEADING_CUSTOMER; ?></td>
                            <td class="dataTableHeadingContent" align="left" colspan="1" width="20%" nowrap><?php echo TABLE_HEADING_EMAIL; ?></td>
                            <td class="dataTableHeadingContent" align="left" colspan="1" width="10%" nowrap><?php echo TABLE_HEADING_STOPPED; ?></td>
                            <td class="dataTableHeadingContent" align="left" colspan="2" width="15%" nowrap><?php echo TABLE_HEADING_PHONE; ?></td>
                        </tr>
                        
                        <tr>&nbsp;<br></tr>

                        <tr class="dataTableHeadingRow">
                            <td class="dataTableHeadingContent" align="left"   colspan="2"  width="10%" nowrap><?php echo TABLE_HEADING_OUT_DATE ?> </td>
                            <td class="dataTableHeadingContent" align="left"   colspan="1"  width="15%" nowrap><?php echo TABLE_HEADING_MODEL; ?></td>
                            <td class="dataTableHeadingContent" align="left"   colspan="2" width="55%" nowrap><?php echo TABLE_HEADING_DESCRIPTION; ?></td>
                            <td class="dataTableHeadingContent" align="center" colspan="1" width="5%" nowrap> <?php echo TABLE_HEADING_QUANTY; ?></td>
                            <td class="dataTableHeadingContent" align="right"  colspan="1"  width="5%" nowrap><?php echo TABLE_HEADING_PRICE; ?></td>
                            <td class="dataTableHeadingContent" align="right"  colspan="1" width="10%" nowrap><?php echo TABLE_HEADING_TOTAL; ?></td>
                        </tr>
                        
                        <?php
                            if ($customerSessionIds = getCustomerSessions()) {
                                $cust_sql = " AND customers_id not in ('" . implode(", ", $customerSessionIds) . "') ";
                            }
                            
                            $ndate = seadate($tdate);
                            $query1 = xtc_db_query("SELECT customers_id, MAX(customers_basket_date_added) as last FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_basket_date_added>='" . $ndate . "' " . $cust_sql . " GROUP BY customers_id ORDER BY last DESC, customers_id");

                            $results = 0;
                            $currentCustomerId = "";
                            $totalPrice = 0;
                            $totalPriceOfAllCarts = 0;
                            $firstLine = true;
                            $finalLine = false;
                            $skip = false;
                            $queryRowCount = xtc_db_num_rows($query1);

                            while ($query1Res = xtc_db_fetch_array($query1)) {
                                $quantity = [];
                                $quantityQuery = xtc_db_query("SELECT products_id pid, customers_basket_quantity qty FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id=" . $query1Res['customers_id']);
        
                                while ($quantityResult = xtc_db_fetch_array($quantityQuery)) {
                                    $quantity[(int) $quantityResult['pid']] += $quantityResult['qty'];
                                }

                                $query2 = xtc_db_query("SELECT cb.customers_id cid,
                                                            cb.products_id pid,
                                                            cb.customers_basket_quantity qty,
                                                            cb.customers_basket_date_added bdate,
                                                            cb.checkout_site site,
                                                            cus.customers_firstname fname,
                                                            cus.customers_lastname lname,
                                                            cus.customers_telephone phone,
                                                            cus.customers_email_address email
                                                    FROM  " . TABLE_CUSTOMERS_BASKET . " cb,
                                                            " . TABLE_CUSTOMERS . " cus
                                                    WHERE cb.customers_id = cus.customers_id
                                                    AND   cb.customers_id = " . $query1Res['customers_id'] . "
                                                    ORDER BY cb.customers_basket_date_added DESC");
    
                                while ($data = xtc_db_fetch_array($query2)) {
                                    $basketEntryOfCustomer = $data;
                                    //reset attributes price
                                    $attributePrice = 0;
                                    // If this is a new customer, create the appropriate HTML
            
                                    if ($currentCustomerId != $basketEntryOfCustomer['cid']) {
                                        // output line
                                        $finalLine = true;
                                        // set new cline and curcus
                                        $currentCustomerId = $basketEntryOfCustomer['cid'];
                                        if ($currentCustomerId != "") {
                                            $totalPrice = 0;

                                            // change the color on those we have contacted add customer tag to customers
                                            $backgroundColor = $configuration->uncontacedColor;
                                            $checked = 1;    // assume we'll send an email
                                            $new = 1;
                                            $skip = false;
                                            $sentdate = "";
                                            $beforeDate = $configuration->cartsMatchAllDates == 'true' ? '0' : $basketEntryOfCustomer['bdate'];
                                            $customerFullName = $basketEntryOfCustomer['fname'] . " " . $basketEntryOfCustomer['lname'];
                                            $status = "";

                                            $doneQuery = xtc_db_query("SELECT * FROM " . TABLE_MCM_RECOVER_CART_SALES . " WHERE customers_id = '" . $currentCustomerId . "'");
                                            $emailttl = seadate($configuration->emailTtl);

                                            if (xtc_db_num_rows($doneQuery) > 0) {
                                                $ttl = xtc_db_fetch_array($doneQuery);
                                                
                                                if ($ttl) {
                                                    if (xtc_not_null($ttl['date_modified'])) { // allow for older scarts that have no datemodified
                                                        $ttldate = $ttl['date_modified'];
                                                    } else {
                                                        $ttldate = $ttl['date_added'];
                                                    }

                                                    if ($emailttl <= $ttldate) {
                                                        $sentdate = $ttldate;
                                                        $backgroundColor = $configuration->contactedColor;
                                                        $checked = 0;
                                                        $new = 0;
                                                    }
                                                }
                                            }

                                            // See if the customer has purchased from us before
                                            // Customers are identified by either their customer ID or name or email address
                                            // If the customer has an order with items that match the current order, assume order completed, bail on this entry!
                                            $ccquery = xtc_db_query('
                                                SELECT orders_id, orders_status
                                                FROM ' . TABLE_ORDERS . '
                                                WHERE (customers_id = ' . (int) $currentCustomerId . '
                                                OR customers_email_address like "' . $basketEntryOfCustomer['email'] .'"
                                                OR customers_name like "' . $basketEntryOfCustomer['fname'] . ' ' . $basketEntryOfCustomer['lname'] . '")
                                                AND date_purchased >= "' . $beforeDate . '"' );

                                            if (xtc_db_num_rows($ccquery) > 0) {
                                                // We have a matching order; assume current customer but not for this order
                                                $customerFullNameFormated = '<font color=' . $configuration->curcustColor . '><b>' . $customerFullName . '</b></font>';

                                                // Now, look to see if one of the orders matches this current order's items
                                                while ($orec = xtc_db_fetch_array($ccquery)) {
                                                    $ccquery = xtc_db_query('SELECT products_id FROM ' . TABLE_ORDERS_PRODUCTS . ' WHERE orders_id = ' . (int) $orec['orders_id'] . ' AND products_id = ' . (int) $basketEntryOfCustomer['pid']);
                                                    if (xtc_db_num_rows($ccquery) > 0 ) {
                                                        if ($orec['orders_status'] > $configuration->pendingSalesStatus ) {
                                                            $checked = 0;
                                                        }

                                                        // OK, we have a matching order; see if we should just skip this or show the status
                                                        if ($configuration->skipMatchedCarts == 'true' && !$checked ) {
                                                            $skip = true;    // reset flag & break us out of the while loop!
                                                            break;
                                                        } else {
                                                            // It's rare for the same customer to order the same item twice, so we probably have a matching order, show it
                                                            $backgroundColor = $configuration->matchedOrderColor;
                                                            $ccquery = xtc_db_query("SELECT orders_status_name FROM " . TABLE_ORDERS_STATUS . " WHERE language_id = " . (int)$_SESSION['languages_id'] . " AND orders_status_id = " . (int)$orec['orders_status'] );

                                                            if( $srec = xtc_db_fetch_array( $ccquery ) ) {
                                                                $status = ' <a href="' . xtc_href_link(FILENAME_ORDERS, "oID=" . $orec['orders_id'] . "&action=edit") .  '">[' . $srec['orders_status_name'] . ']</a>';
                                                            } else {
                                                                $status = ' ['. TEXT_CURRENT_CUSTOMER . ']';
                                                            }
                                                        }
                                                    }
                                                }

                                                if ($skip) {
                                                    continue;    // got a matched cart, skip to next one
                                                }
                                            }
                    
                                            $sentInfo = TEXT_NOT_CONTACTED;

                                            if ($sentdate != '') {
                                                $sentInfo = cart_date_short($sentdate);
                                            }

                                            $site = $basketEntryOfCustomer['site'] == 'confirm' ? TEXT_CONFIRM : ($basketEntryOfCustomer['site'] == 'payment' ? TEXT_PAYMENT : ($basketEntryOfCustomer['site'] == 'shipping' ? TEXT_SHIPPING : TEXT_CART));

                                            $currentLine = "
                                            <tr bgcolor=" . $backgroundColor . ">
                                            <td class='dataTableContent' align='center' width='1%'>" . xtc_draw_checkbox_field('custid[]', $currentCustomerId, $configuration->autoCheck == 'true' ? $checked : 0) . "</td>
                                            <td class='dataTableContent' align='left' width='9%' nowrap><b>" . $sentInfo . "</b></td>
                                            <td class='dataTableContent' align='left' width='15%' nowrap> " . xtc_date_short($basketEntryOfCustomer['bdate']) . "</td>
                                            <td class='dataTableContent' align='left' width='30%' nowrap><a href='" . xtc_href_link(FILENAME_CUSTOMERS, 'search=' . $basketEntryOfCustomer['lname'], 'NONSSL') . "'>" . $customerFullNameFormated . "</a>".$status."</td>
                                            <td class='dataTableContent' align='left' width='20%' nowrap><a href='" . xtc_href_link('mail.php', 'selected_box=tools&customer=' . $basketEntryOfCustomer['email']) . "'>" . $basketEntryOfCustomer['email'] . "</a></td>
                                            <td class='dataTableContent' align='left' width='10%' nowrap>" . $site . "</td>
                                            <td class='dataTableContent' align='left' colspan='2' width='15%' nowrap>" . $basketEntryOfCustomer['phone'] . "</td>
                                            </tr>";
                                        }
                                    }

                                    // We only have something to do for the product if the quantity selected was not zero!
                                    if ($basketEntryOfCustomer['qty'] != 0) {
                                        // Get the product information (name, price, etc)
                                        $query3 = xtc_db_query("SELECT p.products_price price,
                                                                                    p.products_model model,
                                                                                    p.products_tax_class_id tax,
                                                                                    pd.products_name name
                                                                        FROM    " . TABLE_PRODUCTS . " p,
                                                                                    " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                                                        WHERE   p.products_id = '" . (int)$basketEntryOfCustomer['pid'] . "'
                                                                        AND     pd.products_id = p.products_id
                                                                        AND     pd.language_id = " . (int)$_SESSION['languages_id'] );
                                        $inrec2 = xtc_db_fetch_array($query3);

                                        // Check to see if the product is on special, and if so use that pricing
                                        $specialPrice = xtc_get_products_special_price_ow( $basketEntryOfCustomer['pid'], $basketEntryOfCustomer['cid'], ($basketEntryOfCustomer['qty'] < $quantity[(int) $basketEntryOfCustomer['pid']] ? $quantity[(int) $basketEntryOfCustomer['pid']] : $basketEntryOfCustomer['qty']));
                                        // BEGIN OF ATTRIBUTE DB CODE
                                        $productAttributes = ''; // DO NOT DELETE

                                        if ($configuration->showAttributes == 'true') {
                                            $attributeQuery = xtc_db_query("SELECT cba.products_id pid,
                                                                                                po.products_options_name poname,
                                                                                                pov.products_options_values_name povname,
                                                                                                pa.options_values_price price
                                                                                FROM    " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " cba,
                                                                                                " . TABLE_PRODUCTS_OPTIONS . " po,
                                                                                                " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov,
                                                                                                " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                                                WHERE   cba.products_id = '" . $basketEntryOfCustomer['pid'] . "'
                                                                                AND     cba.customers_id = " . $currentCustomerId . "
                                                                                AND     po.products_options_id = cba.products_options_id
                                                                                AND     pov.products_options_values_id = cba.products_options_value_id
                                                                                AND     pa.products_id = " . (int)$basketEntryOfCustomer['pid'] . "
                                                                                AND     pa.options_id = cba.products_options_id
                                                                                AND     pa.options_values_id = cba.products_options_value_id
                                                                                AND     po.language_id = " . (int)$_SESSION['languages_id'] . "
                                                                                AND     pov.language_id = " . (int)$_SESSION['languages_id']);
                                            $hasAttributes = false;

                                            if (xtc_db_num_rows($attributeQuery)) {
                                                $hasAttributes = true;
                                                $productAttributes = '<br>';
                                                while ($attribrecs = xtc_db_fetch_array($attributeQuery)) {
                                                    $productAttributes .= '<small><em> - ' . $attribrecs['poname'] . ' ' . $attribrecs['povname'] . '</em></small><br >';
                                                    $attributePrice += $attribrecs['price'];
                                                }
                                            }
                                        }

                                        if ($specialPrice == 0) {
                                            $specialPrice = $inrec2['price'];
                                        }
                                        $specialPrice += $attributePrice;
            
                                        if ($configuration->showBruttoPrice == 'true') {
                                            $tax = xtc_get_tax_rate($inrec2['tax']);
                                            $specialPrice = xtc_add_tax($specialPrice, $tax);
                                        }

                                        // END OF ATTRIBUTE DB CODE
                                        $totalPrice = $totalPrice + ($basketEntryOfCustomer['qty'] * $specialPrice);
                                        $productPriceFormated  = $currencies->format($specialPrice);
                                        $totalProductPriceFormated = $currencies->format($basketEntryOfCustomer['qty'] * $specialPrice);

                                        $currentLine .= "<tr class='dataTableRow'>
                                                <td class='dataTableContent' align='left' vAlign='top' colspan='2' width='12%' nowrap>" . ($basketEntryOfCustomer['bdate']<$ndate? " x":" &nbsp;") . "</td>
                                                <td class='dataTableContent' align='left' vAlign='top' width='13%' nowrap>" . ($inrec2['model']?$inrec2['model']:"&nbsp;") . "</td>
                                                <td class='dataTableContent' align='left' vAlign='top' colspan='2' width='55%'><a href='" . xtc_href_link(FILENAME_CATEGORIES, 'action=new_product&pID=' . $basketEntryOfCustomer['pid'], 'NONSSL') . "'><b>" . $inrec2['name'] . "</b></a>
                                                " . $productAttributes . "
                                                </td>
                                                <td class='dataTableContent' align='center' vAlign='top' width='5%' nowrap>" . $basketEntryOfCustomer['qty'] . "</td>
                                                <td class='dataTableContent' align='right'  vAlign='top' width='5%' nowrap>" . $productPriceFormated . "</td>
                                                <td class='dataTableContent' align='right'  vAlign='top' width='10%' nowrap>" . $totalProductPriceFormated . "</td>
                                            </tr>";
                                    }
                                }
        
                                if ($finalLine) {
                                    $totalPriceOfAllCarts += $totalPrice;
                                    $textTotal = $configuration->showBruttoPrice == 'true' ? TABLE_CART_TOTAL_BRUTTO : TABLE_CART_TOTAL;
                                    $currentLine .= "       </td>
                                                    <tr>
                                                    <td class='dataTableContent' align='right' colspan='8'><b>" . $textTotal . "</b>" . $currencies->format($totalPrice) . "</td>
                                                    </tr>
                                                    <tr>
                                                    <td colspan='6' align='right'><a class=\"button\" href=" . xtc_href_link(FILENAME_RECOVER_CART_SALES,"action=delete&customer_id=$currentCustomerId&tdate=$tdate") . ">" . BUTTON_DELETE  . "</a><a class=\"button\" href=" . xtc_href_link(FILENAME_RECOVER_CART_SALES,"action=complete&customer_id=$currentCustomerId&tdate=$tdate") . ">" . BUTTON_COMPLETE  . "</a></td>
                                                    </tr>\n";
                                    if (!$skip) {
                                        echo $currentLine;
                                    }

                                    $finalLine = false;
                                }
                            }

                            $totalPriceOfAllCartsFormated = $currencies->format($totalPriceOfAllCarts);
                            $textTotal = $configuration->showBruttoPrice == 'true' ? TABLE_GRAND_TOTAL_BRUTTO : TABLE_GRAND_TOTAL;
                            $currentLine = "<tr></tr><td class='dataTableContent' align='right' colspan='8'><hr align=right width=55><b>" . $textTotal . "</b>" . $totalPriceOfAllCartsFormated . "</td>
                                        </tr>";
                
                            echo $currentLine;
                            echo "<tr><td colspan=8><hr size=1 color=000080><b>" . PSMSG . "</b><br>" . xtc_draw_textarea_field('message', 'soft', '80', '5') . "<br>" . xtc_draw_selection_field('submit_button', 'submit', TEXT_SEND_EMAIL) . "</td></tr>";
                        ?>
                    </form>
                <?php } // end footer of both e-mail and report ?>
                <!-- REPORT TABLE END //-->

            </table>
        </td>
        <!-- body_text_eof //-->
    </tr>
</table>

<!-- body_eof //-->
<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>