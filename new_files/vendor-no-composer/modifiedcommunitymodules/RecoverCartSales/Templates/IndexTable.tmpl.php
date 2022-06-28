<?php

$tableEntries = $vars['tableEntries'] ?? [];
$controller = $vars['controller'] ?? null;

var_dump($tableEntries);
?>

<table class="tableBoxCenter collapse">
    <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" align="center">
            <input type="checkbox" onclick="mcmToggleSelection(this);">
        </td>

        <td class="dataTableHeadingContent" align="left">
            Datum
        </td>

        <td class="dataTableHeadingContent" align="left">
            Kundenname
        </td>

        <td class="dataTableHeadingContent" align="left">
            E-Mail
        </td>

        <td class="dataTableHeadingContent" align="left">
            Telefon
        </td>

        <td class="dataTableHeadingContent" align="left">
            Kauf abgebrochen auf Seite
        </td>

        <td class="dataTableHeadingContent" align="right">
            Artikelanzahl
        </td>

        <td class="dataTableHeadingContent" align="right">
            Summe (brutto)
        </td>

        <td class="dataTableHeadingContent" align="right">
            <?php echo 'Aktion'; ?>
        </td>
    </tr>

    <?php foreach ($tableEntries as $tableEntry) {
        $customer = $tableEntry['customer'];
        $basketEntries = $tableEntry['customerBasketEntries'];
        $customerName = $customer['customers_firstname'] . ' ' . $customer['customers_lastname'];
        // if ($customer['customersCompany']) {
        //     $customerName .= ' - ' . $customer['customers_company'];
        // }
        ?>
        <tr class="dataTableRow">
            <td class="dataTableContent" align="center">
                <?php
                $mcmSelected = '';
                if (is_array($_POST['selectedCustomerIds'])) {
                    if (in_array($customer['customers_id'], $_POST['selectedCustomerIds'])) {
                        $mcmSelected = 'checked';
                    }
                }
                ?>
                <input class="selectCheckbox" name="selectedCustomerIds[]" type="checkbox" value="<?php echo $customer['customers_id'] ?>" <?php echo $mcmSelected; ?> >
            </td>
            <td class="dataTableContent"><?= 'DATUM' ?></td>
            <td class="dataTableContent" align="left"><?= $customerName ?></td>
            <td class="dataTableContent" align="left"><?= $customer['customers_email_address'] ?></td>
            <td class="dataTableContent" align="left"><?= $customer['customers_telephone'] ?></td>
            <td class="dataTableContent" align="left"><?= 'TODOO (Kasse > Adresse)' ?></td>
            <td class="dataTableContent" align="right"><?= count($basketEntries); ?></td>
            <td class="dataTableContent" align="right"><?= $tableEntry['customerBasketTotal'] ?></td>

            <td class="dataTableContent" align="right">
                <a href="/admin/orders.php?oID=<?php echo $tableEntry['index'] ?>&action=edit">
                    <img src="images/icons/icon_edit.gif" alt="Bearbeiten" title="Bearbeiten" style="border:0;">
                </a>
            </td>
        </tr>
    <?php } ?>
</table>

<?php if (!$tableEntries) { ?>
    <style>
        .no-entry {
            text-align: center;
            margin: 20px;
            color: #888888;
        }
    </style>
    <div class="no-entry">Kein offener Warenkorb-Eintrag vorhanden.</div>
<?php } ?>
