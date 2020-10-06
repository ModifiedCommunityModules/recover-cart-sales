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

xtc_checkout_site($site);
