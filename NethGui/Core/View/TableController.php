
<table class="table-controller <?php echo get_class($module); ?>">
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
<fieldset><?php $view[$childModule->getIdentifier()]['action'] = $view['action']; echo $view[$childModule->getIdentifier()]; ?></fieldset>
<?php endforeach; ?>

<?php
echo form_hidden($name['action'], $parameters['action']);
?>
       