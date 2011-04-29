<form class="table-controller" method="post" action="<?php echo $view->buildUrl($view['__arguments']) ?>">
    <?php echo $view[$view['__action']]->render(); ?>
</form>  