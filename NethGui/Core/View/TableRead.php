<table class="table-read <?php echo get_class($module); ?>">
    <caption><?php echo get_class($module); ?></caption>
    <thead><tr>
        <?php
            foreach ($view['columns'] as $columnName) {
                echo '<th>' . T($columnName) . '</th>';
            }
        ?>
    </tr></thead>
    <tbody><?php foreach ($view['rows'] as $row): ?>
    <tr>
        <?php foreach ($row as $value): ?>
             <td><?php echo $value ?></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach ?></tbody>
</table>
<ul class="actions"><?php foreach ($view['tableActions'] as $tableAction): ?><li><?php echo anchor($view->buildUrl('../' . $tableAction), $tableAction) ?></li><?php endforeach; ?></ul>