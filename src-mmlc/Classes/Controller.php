<?php

namespace ModifiedCommunityModules\RecoverCartSales\Classes;

use ModifiedCommunityModules\RecoverCartSales\Classes\Session;
use ModifiedCommunityModules\RecoverCartSales\Classes\ProductHelper;
use ModifiedCommunityModules\RecoverCartSales\Classes\CustomerHelper;
use ModifiedCommunityModules\RecoverCartSales\Classes\BasketHelper;
use RobinTheHood\ModifiedStdModule\Classes\Configuration;
use RobinTheHood\ModifiedUi\Classes\Admin\Page;
use RobinTheHood\ModifiedUi\Classes\Admin\HtmlView;

class Controller
{
    public const FILE_NAME = 'mcm_recover_cart_sales.php';
    public const SESSION_PREFIX = 'mcm_recover_cart_sales';
    public const TEMPLATE_PATH = '../vendor-mmlc/modifiedcommunitymodules/recover-cart-sales/Templates/';

    public $billPatterns = [];
    public $deliveryPatterns = [];
    public $billAndDeliveryPatterns = [];

    private $productHelper = null;
    private $customerHelper = null;
    private $basketHelper = null;

    public function __construct()
    {
        $this->productHelper = new ProductHelper();
        $this->customerHelper = new CustomerHelper();
        $this->basketHelper = new BasketHelper(
            $this->productHelper,
            $this->customerHelper
        );
    }

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
        $this->show();
    }

    private function getEntries()
    {
        $configuration = new Configuration('MODULE_MCM_RECOVER_CART_SALES');
        $customerIdsInSessions = [];
        if ($configuration->checkSessions == 'true') {
            $session = new Session();
            $customerIdsInSessions = $session->getCustomerIdsFromAllSessions();
        }

        $date = $this->dateBeforeDays(90);
        $customerIds = $this->basketHelper->getCustomerIdsFromBasket($date, $customerIdsInSessions);

        $entries = [];
        foreach ($customerIds as $customerId) {
            $customer = $this->customerHelper->getCustomerById($customerId);
            $customerBasketEntries = $this->basketHelper->getCustomerBasketEntriesByCustomerId($customerId);

            if (!$customer || !$customerBasketEntries) {
                continue;
            }

            $entries[] = [
                'customer' => $customer,
                'customerBasketEntries' => $customerBasketEntries,
                'customerBasketTotal' => $this->basketHelper->getBasketSum($customerBasketEntries) //TODO bis jetzt nur netto
            ];
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
        $htmlView->loadHtml(self::TEMPLATE_PATH . 'Index.tmpl.php', [
            'tableEntries' => $this->getEntries()
        ]);

        $page->addComponent($htmlView);
        $page->render();
    }
}
