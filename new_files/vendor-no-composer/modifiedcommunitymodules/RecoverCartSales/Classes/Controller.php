<?php

namespace ModifiedCommunityModules\RecoverCartSales\Classes;

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

    public function invokeIndex()
    {
        $this->show();
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
