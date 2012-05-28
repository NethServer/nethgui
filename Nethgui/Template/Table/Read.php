<?php
    // skip tables on HELP templates
    if($view instanceof \Nethgui\Module\Help\Renderer) {
        return;
    }

    $view
        ->includeFile('Nethgui/Js/jquery.nethgui.datatable.js')
        ->rejectFlag($view::INSET_FORM)
    ;

//    if(strlen($view['tableTitle']) > 0) {
//        echo $view->header()->setAttribute('template', $view['tableTitle']);
//    }
    
    echo $view->literal($view['TableActions'])

?><div class="DataTable <?php echo $view['tableClass']?>" ><table>
    <thead><tr>
        <?php
            foreach ($view['columns'] as $columnInfo) {
                echo isset($columnInfo['formatter']) ? '<th class="' . $columnInfo['formatter'] . '">' : '<th>';
                echo htmlspecialchars($view->translate($columnInfo['name'] . '_label'));
                echo '</th>';
            }
        ?>
    </tr></thead>
    <tbody><?php if(count($view['rows']) > 0) : 
        foreach ($view['rows'] as $rowId => $row): ?>
    <tr class='<?php echo $row[0]['rowCssClass'];?>' >
        <?php foreach ($row as $colId => $value): 
              if($colId == 0) continue;  ?>
             <td><?php echo $view->literal($value) ?></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; else: echo '<tr class="empty">'; 
    
    for($i = 0; $i < count($view['columns']); $i ++) {
        if($i === 0) {
            echo '<td><p>' . htmlspecialchars($view->translate('Empty table')) . '</p></td>';
        } else {
            echo '<td></td>';
        }     
    }
    
    echo '</tr>'; endif ?></tbody>
</table></div>
