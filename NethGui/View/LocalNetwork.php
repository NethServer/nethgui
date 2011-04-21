<?php
 $view
?>
<form method="POST" action="<?php echo $view->buildUrl() ?>">
<?php echo $framework->renderView('NethGui_Core_View_TableController', $self, $module->getLanguageCatalog()); ?>
<input type="submit" value="Save" /> <a href="<?php echo $view->buildUrl('create'); ?>">Nuovo</a>
</form>