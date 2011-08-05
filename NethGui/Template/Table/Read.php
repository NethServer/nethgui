<div class="table-read DataTable <?php echo $view['tableClass']?>" id="<?php echo $view->getUniqueId() ?>" ><table>
    <thead><tr>
        <?php
            foreach ($view['columns'] as $columnName) {
                echo '<th>' . T($columnName . '_label') . '</th>';
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
</table></div>
