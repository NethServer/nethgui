<div class="<?php echo $module->getIdentifier() ?>" title="<?php echo htmlspecialchars($module->getTitle()) ?>">
    <?php
        foreach($module->getChildren() as $childModule) {
            echo $view[$childModule->getIdentifier()]->render();
        }
    ?>
</div>
