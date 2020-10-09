<?php
// modified loads this file in the hole admin area

// buttons
define('BUTTON_COMPLETE', 'Bestellung abschließen');

// configuration
define('RCS_BASE_DAYS_TITLE', 'Zeitraum');
define('RCS_BASE_DAYS_DESC', 'Anzahl der vergangenen Tage für nicht abgeschlossene Warenkörbe.');
define('RCS_REPORT_DAYS_TITLE', 'Verkaufsbericht Zeitraum');
define('RCS_REPORT_DAYS_DESC', 'Anzahl der Tage, die berücksichtigt werden sollen. Je mehr, desto länger dauert die Abfrage!');
define('RCS_EMAIL_TTL_TITLE', 'Lebensdauer Email');
define('RCS_EMAIL_TTL_DESC','Anzahl der Tage, die die E-Mail als gesendet markiert wird');
define('RCS_EMAIL_FRIENDLY_TITLE', 'Persönliche E-Mails');
define('RCS_EMAIL_FRIENDLY_DESC', 'Wenn <b>true</b> wird der Name des Kunden in der Anrede verwendet. Wenn <b>false</b> wird eine allgemeine Anrede verwendet.');
define('RCS_EMAIL_COPIES_TO_TITLE', 'E-Mail Kopien an');
define('RCS_EMAIL_COPIES_TO_DESC', 'Wenn Kopien der Emails an die Kunden versendet werden sollen, bitte Empfänger hier eintragen.');
define('RCS_SHOW_ATTRIBUTES_TITLE', 'Attribute anzeigen');
define('RCS_SHOW_ATTRIBUTES_DESC', 'Kontrolliert die Anzeige von Attributen.<br>Einige Shops nutzen Produktattribute.<br>Auf <b>true</b> setzen, wenn die Attribute angezeigt werden sollen, ansonsten auf <b>false</b>.');
define('RCS_CHECK_SESSIONS_TITLE', 'Ignoriere Kunden mit Sitzung');
define('RCS_CHECK_SESSIONS_DESC', 'Wenn Kunden mit aktiver Sitzung ignoriert werden sollen (z.B. weil sie noch einkaufen), wählen sie <b>true</b>.<br>Wenn auf <b>false</b> gesetzt, werden die Sitzungsdaten ignoriert (schneller).');
define('RCS_CURCUST_COLOR_TITLE', 'Farbe aktiver Kunde');
define('RCS_CURCUST_COLOR_DESC', 'Farbe, die aktive Kunden markiert<br>Ein &quot;aktiver Kunde&quot; hat bereits Artikel im Shop bestellt.');
define('RCS_UNCONTACTED_COLOR_TITLE', 'Farbe "noch nicht kontaktiert"');
define('RCS_UNCONTACTED_COLOR_DESC', 'Hintergrundfarbe für noch nicht kontaktierte Kunden.<br>Ein nicht kontaktierter Kunde wurde noch <i>nicht</i> mit diesem Tool angeschrieben.');
define('RCS_CONTACTED_COLOR_TITLE', 'Farbe kontaktiert');
define('RCS_CONTACTED_COLOR_DESC', 'Hintergrundfarbe für kontaktierte Kunden.<br>Ein kontaktierter Kunde wurde bereits mit diesem Tool <i>informiert</i>.');
define('RCS_MATCHED_ORDER_COLOR_TITLE', 'Farbe alternative Bestellung gefunden');
define('RCS_MATCHED_ORDER_COLOR_DESC', 'Hintergrundfarbe für gefundene alternative Bestellungen.<br>Diese wird verwendet, wenn sich ein oder mehrere Artikel im offenen Warenkorb befinden und die E-Mail-Adresse oder die Kundennummer mit einer anderen Bestellung übereinstimmt (siehe nächster Punkt).');
define('RCS_SKIP_MATCHED_CARTS_TITLE', 'Überspringe alternative Warenkörbe');
define('RCS_SKIP_MATCHED_CARTS_DESC', 'Prüfen, ob der Kunde den Warenkorb alternativ abgeschlossen hat (z.B. über Gastzugang statt per Anmeldung).');
define('RCS_AUTO_CHECK_TITLE', '"sichere" Warenkörbe automatisch markieren');
define('RCS_AUTO_CHECK_DESC', 'Um Einträge, die relativ sicher sind (z.B. noch nicht existierende Kunden, noch nicht angemailt, etc.) zu markieren, setzen Sie <b>true</b>.<br>Wenn auf <b>false</b> gesetzt, werden keine Einträge vorausgewählt.');
define('RCS_CARTS_MATCH_ALL_DATES_TITLE', 'Verwende Bestellungen jeden Datums');
define('RCS_CARTS_MATCH_ALL_DATES_DESC', 'Wenn <b>true</b> wird jede Bestellung des Kunden für die alternativen Abschlüsse herangezogen.<br>Wenn <b>false</b> werden nur Bestellungen im Zeitraum nach dem ablegen des letzten Artikels im Warenkorb gesucht.');
define('RCS_PENDING_SALE_STATUS_TITLE', 'Mindestbestellstatus');
define('RCS_PENDING_SALE_STATUS_DESC', 'Höchster Status, den eine Bestellung haben kann, um immer noch als offen zu gelten. Alle Werte darüber werden als Kauf gewertet');
define('RCS_REPORT_EVEN_STYLE_TITLE', 'Style ungerade Reihe');
define('RCS_REPORT_EVEN_STYLE_DESC', 'Style für die ungeraden Reihen im Bericht. Typische Optionen sind <i>dataTableRow</i> und <i>attributes-even</i>.');
define('RCS_REPORT_ODD_STYLE_TITLE', 'Style gerade Reihe');
define('RCS_REPORT_ODD_STYLE_DESC', 'Style für die geraden Reihen im Bericht. Typische Optionen sind NULL (bzw. kein Eintrag) und <i>attributes-odd</i>.');
define('RCS_SHOW_BRUTTO_PRICE_TITLE', 'Brutto-Anzeige');
define('RCS_SHOW_BRUTTO_PRICE_DESC', 'Sollen die Preise Brutto (true) oder Netto (false) angezeigt werden?');
define('DEFAULT_RCS_PAYMENT_TITLE', 'Standard-Zahlweise');
define('DEFAULT_RCS_PAYMENT_DESC', 'Modulname der Zahlweise für das abschlie&szlig;en der Bestellung (z.B. moneyorder).');
define('DEFAULT_RCS_SHIPPING_TITLE', 'Standard-Versandart');
define('DEFAULT_RCS_SHIPPING_DESC', 'Modulname der Versandart für das abschlie&szlig;en der Bestellung (z.B. dp_dp).');
define('RCS_DELETE_COMPLETED_ORDERS_TITLE', 'Bestellte Warenkörbe löschen');
define('RCS_DELETE_COMPLETED_ORDERS_DESC', 'Soll der Warenkorb im Zuge des Bestellabschlusses automatisch gelöscht werden?');

// miscellaneous
define('BOX_CONFIGURATION_33', 'Offene Warenkörbe');
define('BOX_REPORTS_RECOVER_CART_SALES', 'Wiederhergestellte Warenkörbe');
define('BOX_TOOLS_RECOVER_CART', 'Offene Warenkörbe');
define('TAX_ADD_TAX','inkl. ');
define('TAX_NO_TAX','zzgl. ');