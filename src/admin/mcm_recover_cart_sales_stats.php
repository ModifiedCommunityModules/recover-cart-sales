<?php

/*
$Id: stats_recover_cart_sales.php,v 1.6 2006/02/20 06:10:35 Anotherone Exp $
Recover Cart Sales Report v2.12 for xt:Commerce

Copyright (c) 2006 Andre Estel www.estelco.de

Recover Cart Sales contribution: JM Ivler 11/20/03
(c) Ivler / Ideas From the Deep / osCommerce

Released under the GNU General Public License

Modifed by Aalst (recover_cart_sales.php,v 1.2 .. 1.36)
aalst@aalst.com

Modified by Lane Roathe (recover_cart_sales.php,v 1.4d .. v2.11)
lane@ifd.com    www.osc-modsquad.com / www.ifd.com
*/

/**
 * @phpcs:disable PSR1.Files.SideEffects
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */

use currencies as Currencies;
use RobinTheHood\ModifiedStdModule\Classes\Configuration;

require_once 'includes/application_top.php';

if (rth_is_module_disabled('MODULE_MCM_RECOVER_CART_SALES')) {
    return;
}

require_once DIR_WS_CLASSES . 'currencies.php';
require_once DIR_FS_DOCUMENT_ROOT . '/vendor-no-composer/autoload.php';

$currencies = new Currencies();
$configuration = new Configuration('MODULE_MCM_RECOVER_CART_SALES');

function xtc_date_order_stat($rawDate)
{
    if ($rawDate == '') {
        return false;
    }

    $year = substr($rawDate, 2, 2);
    $month = (int) substr($rawDate, 4, 2);
    $day = (int) substr($rawDate, 6, 2);

    return date(DATE_FORMAT, mktime(0, 0, 0, $month, $day, $year));
}

function seadate($day)
{
    $ts = date("U");
    $rawTime = strtotime("-" . $day . " days", $ts);
    $ndate = date("Ymd", $rawTime);

    return $ndate;
}

require DIR_WS_INCLUDES . 'head.php';
?>

<!-- header //-->

<?php require DIR_WS_INCLUDES . 'header.php' ?>

<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
    <tr>
        <td class="columnLeft2" width="<?= BOX_WIDTH ?>" valign="top">
            <table border="0" width="<?= BOX_WIDTH ?>" cellspacing="1" cellpadding="1" class="columnLeft">
                <!-- left_navigation //-->
                <?php require DIR_WS_INCLUDES . 'column_left.php' ?>
                <!-- left_navigation_eof //-->
            </table>
        </td>

        <!-- body_text //-->
        <td width="100%" valign="top">
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
                Working...
                <tr>
                    <td colspan="6">
                        <!-- new header -->
                        <table border="0" width="100%" cellspacing="0" cellpadding="2">
                            <tr>
                                <td class="pageHeading" align="left"><?= HEADING_TITLE ?></td>
                                <td class="pageHeading" align="right">
                                    <?php
                                    $tdate = $_POST['tdate'] ?? '';
                                    if ($tdate == '') {
                                        $tdate = $configuration->reportDays;
                                    }
                                    $ndate = seadate($tdate);
                                    ?>

                                    <?= xtc_draw_form('mcm_recover_cart_sales_stats', 'mcm_recover_cart_sales_stats.php', '', 'post', '') ?>
                                        <table align="right" width="100%">
                                            <tr class="dataTableContent" align="right">
                                                <td nowrap>
                                                    <?= DAYS_FIELD_PREFIX ?>
                                                    <input type="text" size="4" width="4" value="<?= $tdate ?>" name="tdate">
                                                    <?= DAYS_FIELD_POSTFIX ?><input type="submit" value="<?= DAYS_FIELD_BUTTON ?>">
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php
                // Init vars
                $customerCount = 0;
                $totalRecovered = 0;
                $customerList = '';

                // Query database for abandoned carts within our timeframe
                $conquery = xtc_db_query("SELECT * FROM " . TABLE_MCM_RECOVER_CART_SALES . " WHERE date_added >= '" . $ndate . "' ORDER BY date_added DESC");
                $recoverdCount = xtc_db_num_rows($conquery);

                // Loop though each one and process it
                for ($i = 0; $i < $recoverdCount; $i++) {
                    $row = xtc_db_fetch_array($conquery);
                    $customerId = $row['customers_id'];

                    // we have to get the customer data in order to better locate matching orders
                    $query1 = xtc_db_query("SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address FROM " . TABLE_CUSTOMERS . " c WHERE c.customers_id ='" . $customerId . "'");
                    $customerRecord = xtc_db_fetch_array($query1);

                    // Query DB for the FIRST order that matches this customer ID and came after the abandoned cart
                    $ordersQueryRaw = "SELECT o.orders_id, o.customers_id, o.date_purchased, s.orders_status_name, ot.text as order_total, ot.value FROM " . TABLE_ORDERS . " o LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s WHERE (o.customers_id = " . (int) $customerId . ' OR o.customers_email_address like "' . $customerRecord['customers_email_address'] . '" OR o.customers_name like "' . $customerRecord['customers_firstname'] . ' ' . $customerRecord['customers_lastname'] . '") AND o.orders_status >= ' . (int) $configuration->pendingSalesStatus . ' AND s.orders_status_id = o.orders_status AND o.date_purchased >= "' . $row['date_added'] . '" AND ot.class = "ot_total"';

                    $ordersQuery = xtc_db_query($ordersQueryRaw);
                    $orders = xtc_db_fetch_array($ordersQuery);

                    // If we got a match, create the table entry to display the information
                    if ($orders) {
                        $customerCount++;
                        $totalRecovered += $orders['value'];
                        $customerCount % 2 ? $class = $configuration->reportEvenStyle : $class = $configuration->reportOddStyle;
                        $customerList .= '<tr class="' . $class . '">' .
                            '<td class="datatablecontent" align="right">' . $row['id'] . '</td>' .
                            '<td>&nbsp;</td>' .
                            '<td class="datatablecontent" align="center">' . xtc_date_order_stat($row['date_added']) . '</td>' .
                            '<td>&nbsp;</td>' .
                            '<td class="datatablecontent"><a href="' . xtc_href_link(FILENAME_CUSTOMERS, 'search=' . $customerRecord['customers_lastname'], 'NONSSL') . '">' . $customerRecord['customers_firstname'] . ' ' . $customerRecord['customers_lastname'] . '</a></td>' .
                            '<td class="datatablecontent">' . xtc_date_short($orders['date_purchased']) . '</td>' .
                            '<td class="datatablecontent" align="center">' . $orders['orders_status_name'] . '</td>' .
                            '<td class="datatablecontent" align="right">' . strip_tags($orders['order_total']) . '</td>' .
                            '<td>&nbsp;</td>' .
                            '</tr>';
                    }
                }

                $currentLine =  '<tr><td height="15" colspan="8"> </td></tr>' .
                    '<tr>' .
                    '<td align="right" colspan="3" class="main"><b>' . TOTAL_RECORDS . '</b></td>' .
                    '<td>&nbsp;</td>' .
                    '<td align="left" colspan="5" class="main">' . $recoverdCount . '</td>' .
                    '</tr>' .
                    '<tr>' .
                    '<td align="right" colspan="3" class="main"><b>' . TOTAL_SALES . '</b></td>' .
                    '<td>&nbsp;</td>' .
                    '<td align="left" colspan="5" class="main">' . $customerCount . TOTAL_SALES_EXPLANATION . '</td>' .
                    '</tr>' .
                    '<tr><td height="12" colspan="6"> </td></tr>';
                echo $currentLine;
                ?>

                <tr class="dataTableHeadingRow">    <!-- Header -->
                    <td width="7%" class="dataTableHeadingContent" align="right"><?= TABLE_HEADING_SCART_ID ?></td>
                    <td width="1%" class="dataTableHeadingContent">&nbsp;</td>
                    <td width="10%" class="dataTableHeadingContent" align="center"><?= TABLE_HEADING_SCART_DATE ?></td>
                    <td width="1%" class="dataTableHeadingContent">&nbsp;</td>
                    <td width="50%" class="dataTableHeadingContent"><?= TABLE_HEADING_CUSTOMER ?></td>
                    <td width="10%" class="dataTableHeadingContent"><?= TABLE_HEADING_ORDER_DATE ?></td>
                    <td width="10%" class="dataTableHeadingContent" align="center"><?= TABLE_HEADING_ORDER_STATUS ?></td>
                    <td width="10%" class="dataTableHeadingContent" align="right"><?= TABLE_HEADING_ORDER_AMOUNT ?></td>
                    <td width="1%" class="dataTableHeadingContent">&nbsp;</td>
                </tr>
                
                <?= $customerList;    // BODY: <tr> sections with recovered cart data ?>
            
                <tr>
                    <td colspan="9" valign="bottom"><hr width="100%" size="1" color="#800000" noshade></td>
                </tr>
            
                <tr class="main">
                    <td align="right" valign="center" colspan="4" class="main"><b><?= TOTAL_RECOVERED ?>&nbsp;</b></font></td>
                    <td align="left" colspan="3" class="main"><b><?= $recoverdCount ? xtc_round(($customerCount / $recoverdCount) * 100, 2) : 0 ?>%</b></font></td>
                    <td class="main" align="right"><b><?= $currencies->format(xtc_round($totalRecovered, 2)) ?></b></font></td>
                    <td class="main">&nbsp;</td>
                </tr>
                Done!
            </table>
            <!-- body_text_eof //-->
        </td>
    </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php' ?>
<!-- footer_eof //-->

<br>
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php' ?>
