<?php

namespace ModifiedCommunityModules\RecoverCartSales\Classes;

use ModifiedCommunityModules\RecoverCartSales\Classes\Session;
use RobinTheHood\ModifiedStdModule\Classes\Configuration;
use RobinTheHood\ModifiedUi\Classes\Admin\Page;
use RobinTheHood\ModifiedUi\Classes\Admin\HtmlView;

class Controller
{
    public const FILE_NAME = 'mcm_recover_cart_sales.php';
    public const SESSION_PREFIX = 'mcm_recover_cart_sales';
    public const TEMPLATE_PATH = '../vendor-no-composer/modifiedcommunitymodules/RecoverCartSales/Templates/';

    public $billPatterns = [];
    public $deliveryPatterns = [];
    public $billAndDeliveryPatterns = [];

    public function invoke()
    {
        $action = $_POST['action'] ?? '';

        if ($action == 'pdf') {
        } else {
            $this->invokeIndex();
        }
    }

    public function invokeIndex(): void
    {
        // if ($customerSessionIds = getCustomerSessions()) {
        //     $cust_sql = " AND customers_id not in ('" . implode(", ", $customerSessionIds) . "') ";
        // }
        
        $configuration = new Configuration('MODULE_MCM_RECOVER_CART_SALES');
        $customerIdsInSessions = [];
        if ($configuration->checkSessions == 'xtrue' ) {
            $session = new Session();
            $customerIdsInSessions = $session->getCustomerIdsFromAllSessions();
        }

        // var_dump($customerIdsInSessionsStr);
        // die();
        //$date = dateBeforeDays($tdate);
        $date = $this->dateBeforeDays(90);

        // $entries = $this->getCustomerIdsFromBasket($date, $customerIdsInSessions);
        // var_dump($entries);
        // die();
        // $sql = "SELECT customers_id, MAX(customers_basket_date_added) as added_latest
        //         FROM customers_basket
        //         WHERE customers_basket_date_added >= '$date'
        //             AND customers_id NOT IN ($customerIdsInSessionsStr)
        //         GROUP BY customers_id
        //         ORDER BY added_latest DESC, customers_id";


        // $query = xtc_db_query($sql);
        // while ($row = xtc_db_fetch_array($query)) {
        //     var_dump($row);
        // }
        // die();

        // $query1 = xtc_db_query("SELECT customers_id, MAX(customers_basket_date_added) as last FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_basket_date_added>='" . $ndate . "' " . $cust_sql . " GROUP BY customers_id ORDER BY last DESC, customers_id");


        

        $this->show();
    }

    /**
     * Bis jetzt wird nur der netto Preis berechent.
     */
    private function getBasketSum(array $customerBasketEntries): float
    {
        $sum = 0.0;
        foreach ($customerBasketEntries as $customerBasketEntry) {
            $customerId = (int) $customerBasketEntry['customers_id'];
            $customerStatus = $this->getCustomerStatus($customerId);

            $productId = (int) $customerBasketEntry['products_id'];
            $quantity = (int) $customerBasketEntry['customers_basket_quantity'];

            $product = $this->getProduct($productId);

            $bestPrice = $this->getBestProductPrice($product, $customerStatus, $quantity);

            $sum += $bestPrice * $quantity;
        }

        return $sum;
    }

    /**
     * Gibt den besten Preis zurück, dabei werden der normele Preis, der Kundengruppen-Preis
     * und die Sonderangebots-Preise verglichen und der niedriegste Preis geliefert.
     */
    private function getBestProductPrice(array $product, int $customerStatus, int $quantity): float
    {
        $prices = [];

        if (isset($product['products_price'])) {
            $prices[] = $product['products_price'];
        }

        if (!isset($product['products_id'])) {
            return 0.0;
        }

        $productId = $product['products_id'] ?? 0;

        $personalOfferPrice = $this->getPersonalOfferPrice($productId, $customerStatus, $quantity);
        if ($personalOfferPrice) {
            $prices[] = $personalOfferPrice;
        }
        
        $specialPrice = $this->getSpecialPrice($productId);
        if ($specialPrice) {
            $prices[] = $specialPrice;
        }
        
        if (!$prices) {
            return 0.0;
        }

        return min($prices);
    }


    /**
     * Liefert -1, wenn keine customerStatus(Id) gefunden werden konnte.
     */
    private function getCustomerStatus(int $customerId): int
    {
        $sql = "SELECT customers_status FROM customers WHERE customers_id = '$customerId'";
        $query = xtc_db_query($sql);
        $row = xtc_db_fetch_array($query);
        return $row['customers_status'] ?? -1;
    }

    /**
     * TODO: SpecialPrices können ablaufen, oder Limitiert sein, dass muss noch berücksichtig werden.
     * Liefert 0, wenn kein Preis gefunden wurde.
     */
    private function getSpecialPrice(int $productId): float
    {
        $sql = "SELECT * FROM specials WHERE products_id = '$productId' AND status = 1";
        $query = xtc_db_query($sql);
        $row = xtc_db_fetch_array($query);
        return $row['specials_new_products_price'] ?? 0.0;
    }

    /**
     * Liefert 0, wenn kein Preis gefunden werden konnte.
     */
    private function getPersonalOfferPrice(int $productId, int $customerStatus, int $quantity): float
    {
        $tableName = 'personal_offers_by_customers_status_' . $customerStatus;

        $sql = "SELECT *
                FROM $tableName
                WHERE products_id = '$productId'
                    AND quantity <= '$quantity'
                ORDER BY quantity DESC
                LIMIT 1";
        
        $query = xtc_db_query($sql);
        $row = xtc_db_fetch_array($query);
        return $row['personal_offer'] ?? 0.0;
    }

    private function getProduct(int $productId): array
    {
        $sql = "SELECT * FROM products p WHERE p.products_id = '$productId'";
        $query = xtc_db_query($sql);
        $product = xtc_db_fetch_array($query);
        if ($product) {
            return $product;
        }
        return [];
    }

    private function getCustomerById(int $customerId): array
    {
        $sql = "SELECT * FROM customers WHERE customers_id = '$customerId' LIMIT 1";
        $entries = [];
        $query = xtc_db_query($sql);
        $row = xtc_db_fetch_array($query);
        if ($row) {
            return $row;
        }
        return [];
    }

    private function getCustomerBasketEntriesByCustomerId(int $customerId): array
    {  
        $sql = "SELECT * FROM customers_basket WHERE customers_id = '$customerId' ORDER BY customers_basket_date_added DESC";
        $entries = [];
        $query = xtc_db_query($sql);
        while ($row = xtc_db_fetch_array($query)) {
            $entries[] = $row;
        }
        return $entries;
    }

    private function getCustomerIdsFromBasket(string $dateAfter, array $excludedCustomerIds): array
    {
        $excludedCustomerIdsStr = '0';
        if ($excludedCustomerIds) {
            $excludedCustomerIdsStr = implode(', ', $excludedCustomerIds);
        }

        $sql = "SELECT customers_id, MAX(customers_basket_date_added) as added_latest
                FROM customers_basket
                WHERE customers_basket_date_added >= '$dateAfter'
                    AND customers_id NOT IN ($excludedCustomerIdsStr)
                GROUP BY customers_id
                ORDER BY added_latest DESC, customers_id";

        $query = xtc_db_query($sql);

        $entries = [];
        while ($row = xtc_db_fetch_array($query)) {
            $entries[] = $row;
        }
        return $entries;
    }

    /**
     * TODO: Ausgabe im DateTime Format
     */
    private function dateBeforeDays(int $days): string
    {
        $time = strtotime("-$days days");
        $date = date("Ymd", $time);
        return $date;
    }

    public function show($messages = [])
    {
        $page = new Page();
        $page->setHeading('Offene Warenkörbe Plus');
        $page->setSubHeading('Hilfsprogramme');

        $htmlView = new HtmlView();
        $htmlView->loadHtml(self::TEMPLATE_PATH . 'Index.tmpl.php', []);

        $page->addComponent($htmlView);
        $page->render();
    }
}
