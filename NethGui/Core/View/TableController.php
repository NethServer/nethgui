
<table border="1" class="<?php echo get_class($module); ?>">
    <caption><?php echo get_class($module); ?></caption>
    <tr>
        <?php
            foreach ($view['columns'] as $columnName) {
                echo '<th>' . T($columnName) . '</th>';
            }
        ?>
    </tr>
    <?php foreach ($view['rows'] as $row): ?>
    <tr>
        <?php foreach ($row as $value): ?>
             <td><?php echo $value ?></td>
        <?php endforeach; ?>             
    </tr>
    <?php endforeach; ?>
</table>

<?php foreach ($module->getChildren() as $childModule): ?>
<fieldset><?php echo $view[$childModule->getIdentifier()]->render() ?></fieldset>
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
       