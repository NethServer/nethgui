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
<div><?php echo anchor($view->buildUrl('..', 'create'), T('Create')); ?></div>
