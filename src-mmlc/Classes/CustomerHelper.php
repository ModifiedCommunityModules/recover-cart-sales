<?php

namespace ModifiedCommunityModules\RecoverCartSales\Classes;

class CustomerHelper
{
    public function getCustomerById(int $customerId): array
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

    /**
     * Liefert -1, wenn keine customerStatus(Id) gefunden werden konnte.
     */
    public function getCustomerStatus(int $customerId): int
    {
        $sql = "SELECT customers_status FROM customers WHERE customers_id = '$customerId'";
        $query = xtc_db_query($sql);
        $row = xtc_db_fetch_array($query);
        return $row['customers_status'] ?? -1;
    }
}