<div id="dlgValidation" class="Notification warning ui-state-error">
    <span class='NotificationIcon ui-icon ui-icon-info'></span><?php echo $view->textLabel('message') ?>:
    <?php
    foreach ($view['errors'] as $errorView) {
        echo ' ' . $view->literal($errorView);
    }
    ?>

</div>
