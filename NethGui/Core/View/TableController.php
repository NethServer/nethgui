<form class="table-controller" method="post" action="<?php echo $view->buildUrl($view['arguments']) ?>">
    <?php echo $view['currentAction']->render(); ?>
</form>  