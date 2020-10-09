<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!defined('MODULE_MCM_RECOVER_CART_SALES_STATUS') || MODULE_MCM_RECOVER_CART_SALES_STATUS != 'true') {
    return;
}

$add_contents[BOX_HEADING_STATISTICS][] = [ 
    'admin_access_name' => 'mcm_recover_cart_sales_stats',          // Eintrag fuer Adminrechte
    'filename'          => 'mcm_recover_cart_sales_stats.php',   // Dateiname der neuen Admindatei
    'boxname'           => BOX_REPORTS_RECOVER_CART_SALES,          // Anzeigename im Menue
    'parameter'         => '',                                      // zusaetzliche Parameter z.B. 'set=export'
    'ssl'               => ''                                       // SSL oder NONSSL, kein Eintrag = NONSSL
];

$add_contents[BOX_HEADING_TOOLS][] = [
    'admin_access_name' => 'mcm_recover_cart_sales',                // Eintrag fuer Adminrechte
    'filename'          => 'mcm_recover_cart_sales.php',         // Dateiname der neuen Admindatei
    'boxname'           => BOX_TOOLS_RECOVER_CART,                  // Anzeigename im Menue
    'parameter'         => '',                                      // zusaetzliche Parameter z.B. 'set=export'
    'ssl'               => ''                                       // SSL oder NONSSL, kein Eintrag = NONSSL
];

$add_contents[BOX_HEADING_CONFIGURATION2][] = [
    'admin_access_name' => 'mcm_recover_cart_sales',                // Eintrag fuer Adminrechte
    'filename'          => FILENAME_CONFIGURATION .'?gID=33',       // Dateiname der neuen Admindatei
    'boxname'           => BOX_CONFIGURATION_33,                    // Anzeigename im Menue
    'parameter'         => '',                                      // zusaetzliche Parameter z.B. 'set=export'
    'ssl'               => ''                                       // SSL oder NONSSL, kein Eintrag = NONSSL
];