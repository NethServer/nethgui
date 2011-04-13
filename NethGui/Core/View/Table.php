
<table border="1" class="<?php echo get_class($module->getParent()); ?>">
    <caption><?php echo get_class($module->getParent()); ?></caption>
    <tr>
        <?php
        foreach ($view['columns'] as $columnName) {
            echo '<th>' . T($columnName) . '</th>';
        }
        ?>
    </tr>
<?php foreach ($view['rows'] as $key => $row): ?>
            <tr>
<?php foreach ($row as $field => $value): ?>
                <td><?php echo $value ?></td>
<?php endforeach; ?>
            </tr>
<?php endforeach; ?>
</table>