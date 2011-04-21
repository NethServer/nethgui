


<?php foreach ($module->getChildren() as $childModule): ?>
<fieldset><?php $view[$childModule->getIdentifier()]['action'] = $view['action']; echo $view[$childModule->getIdentifier()]; ?></fieldset>
<?php endforeach; ?>

<?php

/*
 * On POST, action is UPDATE if page is in UPDATE state, otherwise action is CREATE.
 */
if($parameters['action'] == 'update') {
    echo form_hidden($name['action'], 'update');
} else {
    echo form_hidden($name['action'], 'create');
}
   
?>
       