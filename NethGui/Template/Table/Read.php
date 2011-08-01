<div id="<?echo $view['tableId'] ?>" class="crud-read <?php echo $view['tableClass']?>">
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
<?php
    
    $flags = ($view['__action'] == 'index') ? NethGui_Renderer_Abstract::STATE_DISABLED : 0;

    $elementList = $view->elementList($flags);

    foreach($view['tableActions'] as $buttonArgs) {
        $button = $view
            ->button($buttonArgs[0], $buttonArgs[1])
            ->setAttribute('value', $buttonArgs[2])
        ;
        $elementList->insert($button);        
    }
    
    echo $elementList;        

?>
</div>