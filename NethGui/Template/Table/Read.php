<div class="DataTable <?php echo $view['tableClass']?>" id="<?php echo $view->getUniqueId() ?>" ><table>
    <thead><tr>
        <?php
            foreach ($view['columns'] as $columnInfo) {
                echo isset($columnInfo['formatter']) ? '<th class="' . $columnInfo['formatter'] . '">' : '<th>';
                echo  T($columnInfo['name'] . '_label');
                echo '</th>';
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
