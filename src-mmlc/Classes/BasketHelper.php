<?php

namespace ModifiedCommunityModules\RecoverCartSales\Classes;

class BasketHelper
{
    private $productHelper = null;
    private $customerHelper = null;

    public function __construct($productHelper, $customerHelper)
    {
        $this->productHelper = $productHelper;
        $this->customerHelper = $customerHelper;
    }

    public function getCustomerBasketEntriesByCustomerId(int $customerId): array
    {
        $sql = "SELECT * FROM customers_basket WHERE customers_id = '$customerId' ORDER BY customers_basket_date_added DESC";
        $entries = [];
        $query = xtc_db_query($sql);
        while ($row = xtc_db_fetch_array($query)) {
            $entries[] = $row;
        }
        return $entries;
    }

    public function getCustomerIdsFromBasket(string $dateAfter, array $excludedCustomerIds): array
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
            $entries[] = $row['customers_id'];
        }
        return $entries;
    }

    /**
     * Bis jetzt wird nur der netto Preis berechent.
     */
    public function getBasketSum(array $customerBasketEntries): float
    {
        $sum = 0.0;
        foreach ($customerBasketEntries as $customerBasketEntry) {
            $customerId = (int) $customerBasketEntry['customers_id'];
            $customerStatus = $this->customerHelper->getCustomerStatus($customerId);

            $productId = (int) $customerBasketEntry['products_id'];
            $quantity = (int) $customerBasketEntry['customers_basket_quantity'];

            $product = $this->productHelper->getProduct($productId);

            $bestPrice = $this->productHelper->getBestProductPrice($product, $customerStatus, $quantity);

            $sum += $bestPrice * $quantity;
        }

        return $sum;
    }
}
