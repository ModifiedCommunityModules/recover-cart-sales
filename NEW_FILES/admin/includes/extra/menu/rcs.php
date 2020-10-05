<?php

	defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

	$add_contents[BOX_HEADING_STATISTICS][] = array( 
		'admin_access_name' => 'stats_recover_cart_sales',   	//Eintrag fuer Adminrechte
		'filename' 			=> FILENAME_STATS_RECOVER_CART_SALES,  //Dateiname der neuen Admindatei
		'boxname' 			=> BOX_REPORTS_RECOVER_CART_SALES,    //Anzeigename im Menue
		'parameter'			=> '',                  //zusaetzliche Parameter z.B. 'set=export'
		'ssl' 				=> ''                   //SSL oder NONSSL, kein Eintrag = NONSSL
	);

	$add_contents[BOX_HEADING_TOOLS][] = array( 
		'admin_access_name' => 'recover_cart_sales',   	//Eintrag fuer Adminrechte
		'filename' 			=> FILENAME_RECOVER_CART_SALES,  //Dateiname der neuen Admindatei
		'boxname' 			=> BOX_TOOLS_RECOVER_CART,    //Anzeigename im Menue
		'parameter'			=> '',                  //zusaetzliche Parameter z.B. 'set=export'
		'ssl' 				=> ''                   //SSL oder NONSSL, kein Eintrag = NONSSL
	);

	$add_contents[BOX_HEADING_CONFIGURATION2][] = array( 
		'admin_access_name' => 'recover_cart_sales',   	//Eintrag fuer Adminrechte
		'filename' 			=> FILENAME_CONFIGURATION.'?gID=33',  //Dateiname der neuen Admindatei
		'boxname' 			=> BOX_CONFIGURATION_33,    //Anzeigename im Menue
		'parameter'			=> '',                  //zusaetzliche Parameter z.B. 'set=export'
		'ssl' 				=> ''                   //SSL oder NONSSL, kein Eintrag = NONSSL
	);