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
