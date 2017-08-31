<?php
// skip tables on HELP templates
if ($view instanceof \Nethgui\Module\Help\Renderer) {
    return;
}

/* @var $view Nethgui\Renderer\Xhtml */

$view
    ->includeFile('Nethgui/Js/jquery.nethgui.datatable.js')
    ->useFile('js/percent.js')
    ->useFile('js/file-size.js')
    ->useFile('js/ip-address.js')
    ->rejectFlag($view::INSET_FORM)
;

echo $view->literal($view['TableActions']);
?><div class="DataTable <?php echo $view['tableClass'] ?>" ><table><thead><tr><?php
foreach ($view['columns'] as $columnInfo) {
    $thLabel = htmlspecialchars($view->translate($columnInfo['name'] . '_label'));
    $configAttr = sprintf(' data-options="%s"', htmlspecialchars(json_encode($columnInfo)));
    echo sprintf('<th%s>%s</th>', $configAttr, $thLabel);
}
?></tr></thead><tbody><?php
                if (count($view['rows']) > 0) {
                    foreach ($view['rows'] as $rowId => $row) {
                        echo '<tr class="' . $row[0]['rowCssClass'] . '">';
                        foreach ($row as $colId => $value) {
                            if ($colId == 0) {
                                continue;
                            } elseif ($view['columns'][$colId - 1]['formatter'] === 'default') {
                                echo "<td>" . $view->literal($value)->setAttribute('hsc', TRUE) . "</td>";
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
