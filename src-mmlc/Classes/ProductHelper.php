<?php

namespace ModifiedCommunityModules\RecoverCartSales\Classes;

class ProductHelper
{
    public function getProduct(int $productId): array
    {
        $sql = "SELECT * FROM products p WHERE p.products_id = '$productId'";
        $query = xtc_db_query($sql);
        $product = xtc_db_fetch_array($query);
        if ($product) {
            return $product;
        }
        return [];
    }

    /**
     * Gibt den besten Preis zurück, dabei werden der normele Preis, der Kundengruppen-Preis
     * und die Sonderangebots-Preise verglichen und der niedriegste Preis geliefert.
     */
    public function getBestProductPrice(array $product, int $customerStatus, int $quantity): float
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
}
