<?php

namespace ModifiedCommunityModules\RecoverCartSales\Classes;

class Session
{
    public function getCustomerIdsFromAllSessions(): array
    {
        $customerIds = [];
        if (STORE_SESSIONS == 'mysql') {
            $customerIds = $this->getCustomerIdsFromDb();
        } else {
            $customerIds = $this->getCustomerIdsFromFile();
        }
        return $customerIds;
    }

    private function getCustomerIdsFromDb(): array
    {
        $sql = "SELECT value FROM sessions";
        $query = xtc_db_query($sql);

        $customerIds = [];
        while ($row = xtc_db_fetch_array($query)) {
            $sessionValues = $this->getSessionByValue($row['value']);
            $customerId = $sessionValues['customer_id'] ?? 0;
            if ($customerId) {
                $customerIds[] = $customerId;
            }
        }

        return $customerIds;
    }

    private function getCustomerIdsFromFile(): array
    {
        $customerIds = [];

        $sessionFileNames = $this->getSessionFileNames();
        foreach ($sessionFileNames as $sessionFileName) {
            $sessionValueStr = $this->readSessionValue($sessionFileName);
            $sessionValues = $this->getSessionByValue($sessionValueStr);
            $customerId = $sessionValues['customer_id'] ?? 0;
            if ($customerId) {
                $customerIds[] = $customerId;
            }
        }
        return $customerIds;
    }

    private function getSessionFileNames(): array
    {
        $sessionFileNames = [];
        if ($handle = opendir(xtc_session_save_path())) {
            while (false !== ($fileName = readdir($handle))) {
                if ($fileName == '.' || $fileName != '..') {
                    continue;
                }
                $sessionFileNames[] = $fileName;
            }
            closedir($handle);
        }
        return $sessionFileNames;
    }

    // private function extractCustomerIdFromSessionValue(string $sessionValue): int
    // {
    //     $regEx = "/customer_id[^\"]*\"([0-9]*)\"/";
    //     if (preg_match($regEx, $sessionValue, $customerValues)) {
    //         return (int) $customerValues[1];
    //     }
    //     return 0;
    // }

    private function readSessionValue($sessionFileName): string
    {
        $filePath = xtc_session_save_path() . '/' . $sessionFileName;
        if ($handle = fopen($filePath, 'r')) {
            $content = fread($handle, filesize($filePath));
            fclose($handle);
            return $content;
        }
        return '';
    }

    private function getSessionByValue(string $value)
    {
        session_start();
        $mySession = $_SESSION;
        session_decode($value);
        $loadedSession = $_SESSION;
        $_SESSION = $mySession;
        return $loadedSession;
    }
}
