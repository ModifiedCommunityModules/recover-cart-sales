<?php

$site = '';

switch ($checkout_position[$current_page]) {
	case '1':
		$site = 'shipping';
		break;
	case '2':
		$site = 'payment';
		break;
	case '3':
		$site = 'confirm';
		break;
}

//BOF Offener Warenkorb Plus
xtc_checkout_site($site);
//EOF Offener Warenkorb Plus