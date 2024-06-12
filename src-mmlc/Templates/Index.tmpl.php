<?php

use ModifiedCommunityModules\RecoverCartSales\Classes\Controller;

$messages = $vars['messages'];

?>

<style type="text/css">
    <?php //include_once Controller::TEMPLATE_PATH . 'style.css'; ?>
</style>

<script>
    <?php //include_once Controller::TEMPLATE_PATH . 'script.js'; ?>
</script>

<?php if ($messages['error']) { ?>
    <div class="message-error">
        Fehler: <?php echo $messages['error']; ?>
    </div>
<?php } ?>

<?php if ($messages['success']) { ?>
    <div class="message-success">
        <?php echo $messages['success']; ?>
    </div>
<?php } ?>

<?php //require_once 'IndexFilter.tmpl.php'; ?>

<?php echo xtc_draw_form('orders', Controller::FILE_NAME, '', 'post'); ?>
    <input type="hidden" id="mcmAction" name="mcmAction" value="">

    <table class="tableCenter">
        <tr>
            <td class="boxCenterLeft">
                <?php require_once 'IndexTable.tmpl.php'; ?>
                <?php //require_once 'IndexPagination.tmpl.php'; ?>
            </td>

            <td class="boxRight">
                <table class="contentTable">
                    <tbody>
                        <tr class="infoBoxHeading">
                            <td class="infoBoxHeading">
                                <div class="infoBoxHeadingTitle">
                                    <b>Aktion für ausgewählte Warenkörbe</b>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="contentTable">
                    <tbody>
                        <?php //require_once 'Actions/CreateBill.tmpl.php'; ?>
                        <?php //require_once 'Actions/CreateProductListing.tmpl.php'; ?>
                        <?php //require_once 'Actions/ChangeStatus.tmpl.php'; ?>
                        <?php //require_once 'Actions/AssignInvoiceNumber.tmpl.php'; ?>
                        <?php //require_once 'Actions/LoadHookPoints.tmpl.php'; ?>
                        <?php //require_once 'Actions/Description.tmpl.php'; ?>
                    </tbody>
                </table>
            </td>

        </tr>
    </table>
</form>
