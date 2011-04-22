<form class="table-controller" method="post" action="<?php echo $view->buildUrl($view['action']) ?>">
    <?php echo $view[$view['action']]->render(); ?>    
</form>  