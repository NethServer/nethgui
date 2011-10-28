<?php echo $view->inset('TableActions') ?>
<div class="DataTable <?php echo $view['tableClass']?>" ><?php if(count($view['rows']) > 0) : ?><table>
    <thead><tr>
        <?php
            foreach ($view['columns'] as $columnInfo) {
                echo isset($columnInfo['formatter']) ? '<th class="' . $columnInfo['formatter'] . '">' : '<th>';
                echo  T($columnInfo['name'] . '_label');
                echo '</th>';
            }
        ?>
    </tr></thead>
    <tbody><?php foreach ($view['rows'] as $rowId => $row): ?>
    <tr>
        <?php foreach ($row as $colId => $value): ?>
             <td><?php echo $view->literal($value) ?></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?></tbody>
</table><?php else: echo '<p>' . T('Empty table') . '</p>'; endif ?></div>
