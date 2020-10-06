<?php
require_once DIR_FS_INC . 'xtc_checkout_site.inc.php';

if(strpos($_SERVER['REQUEST_URI'], 'login') !== false && isset($_SESSION['customer_id'])) {
    xtc_checkout_site('cart');
}