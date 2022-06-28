<?php

$tableEntries = $vars['tableEntries'] ?? [];
$controller = $vars['controller'] ?? null;

?>

<table class="tableBoxCenter collapse">
        <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent">
            <input type="checkbox" onclick="mcmToggleSelection(this);">
        </td>

        <td class="dataTableHeadingContent">
            A
        </td>

        <td class="dataTableHeadingContent" align="right">
            A
        </td>

        <td class="dataTableHeadingContent" align="right" style="width:120px">
            A
        </td>

        <td class="dataTableHeadingContent" align="right">
            B
        </td>

        <td class="dataTableHeadingContent" align="center">
            C
        </td>

        <td class="dataTableHeadingContent" align="center">
            D
        </td>

        <td class="dataTableHeadingContent" align="right">
            E
        </td>

        <td class="dataTableHeadingContent" align="right">
            F
        </td>

        <td class="dataTableHeadingContent" align="right">
            <?php echo 'Aktion'; ?>
        </td>
    </tr>

    <?php foreach ($tableEntries as $orderData) {
        $name = $tableEntry['customerName'];
        if ($tableEntry['customersCompany']) {
            $name .= ' - ' . $tableEntry['customersCompany'];
        }
        ?>
        <tr class="dataTableRow">
            <td class="dataTableContent">
                <?php
                $mcmSelected = '';
                if (is_array($_POST['orderIds'])) {
                    if (in_array($tableEntry['id'], $_POST['orderIds'])) {
                        $mcmSelected = 'checked';
                    }
                }
                ?>
                <input class="selectCheckbox" name="orderIds[]" type="checkbox" value="<?php echo $tableEntry['index'] ?>" <?php echo $mcmSelected; ?> >
            </td>
            <td class="dataTableContent"><?php echo $name ?></td>
            <td class="dataTableContent" align="right">
            <?php echo $tableEntry['index'] ?>
            </td>
            <td class="dataTableContent" align="right"><?php echo $tableEntry['index'] ?></td>
            <td class="dataTableContent" align="right"><?php echo $tableEntry['index'] ?></td>
            <td class="dataTableContent" align="center"><?php echo $tableEntry['index'] ?></td>
            <td class="dataTableContent" align="center"><?php echo $tableEntry['index'] ?></td>
            <td class="dataTableContent" align="right"><?php echo $tableEntry['index'] ?></td>
            <td class="dataTableContent" align="right"><?php echo $tableEntry['index'] ?></td>

            <td class="dataTableContent" align="right">
                <a href="/admin/orders.php?oID=<?php echo $tableEntry['index'] ?>&action=edit">
                    <img src="images/icons/icon_edit.gif" alt="Bearbeiten" title="Bearbeiten" style="border:0;">
                </a>
            </td>
        </tr>
    <?php } ?>
</table>