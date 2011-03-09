<div class="<?php echo $module->getIdentifier() ?>" title="<?php echo $module->getTitle() ?>">
    <?php
        foreach($module->getChildren() as $childModule) {
            echo $framework->renderResponse($response->getInnerResponse($childModule));
        }
    ?>
</div>
