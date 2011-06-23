<div id="<?echo $view->getFullId()?>" class="crud-read">
<table>
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
</table>
<ul class="actions">
    <?php
        if ($view['__action'] == 'index') {
            $flags = NethGui_Renderer_Abstract::STATE_DISABLED;
        } else {
            $flags = 0;
        }

        $flags |= NethGui_Renderer_Abstract::BUTTON_LINK;

    ?>

    <?php foreach ($view['tableActions'] as $tableAction): ?>
    <li><?php echo $view->button($tableAction, $flags, '../' . $tableAction) ?></li>
    <?php endforeach; ?>
</ul>
</div>