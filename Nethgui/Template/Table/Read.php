<?php
// skip tables on HELP templates
if ($view instanceof \Nethgui\Module\Help\Renderer) {
    return;
}

/* @var $view Nethgui\Renderer\Xhtml */

$view
    ->includeFile('Nethgui/Js/jquery.nethgui.datatable.js')
    ->rejectFlag($view::INSET_FORM)
;

echo $view->literal($view['TableActions']);
?><div class="DataTable <?php echo $view['tableClass'] ?>" ><table><thead><tr><?php
foreach ($view['columns'] as $columnInfo) {
    echo isset($columnInfo['formatter']) ? '<th class="' . $columnInfo['formatter'] . '">' : '<th>';
    echo htmlspecialchars($view->translate($columnInfo['name'] . '_label'));
    echo '</th>';
}
?></tr></thead><tbody><?php
                if (count($view['rows']) > 0) {
                    foreach ($view['rows'] as $rowId => $row) {
                        echo '<tr class="' . $row[0]['rowCssClass'] . '">';
                        foreach ($row as $colId => $value) {
                            if ($colId == 0) {
                                continue;
                            } else {
                                echo "<td>" . $view->literal($value) . "</td>";
                            }
                        }
                        echo '</tr>';
                    }
                } else {
                    echo '<tr class="empty">';
                    for ($i = 0; $i < count($view['columns']); $i ++ ) {
                        if ($i === 0) {
                            echo '<td><p>' . htmlspecialchars($T('Empty table')) . '</p></td>';
                        } else {
                            echo '<td></td>';
                        }
                    }
                    echo '</tr>';
                }
?></tbody></table></div>
