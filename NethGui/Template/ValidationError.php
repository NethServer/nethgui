<div id="dlgValidation" class="Notification warning ui-state-error">
    <span class='NotificationIcon ui-icon ui-icon-info'></span><?php echo $view->text('message') ?>:
    <?php
    foreach ($view['errors'] as $errorView) {
        echo ' ' . $errorView;
    }
    ?>

</div>