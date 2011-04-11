
<table border="1">
    <caption><?php echo get_class($module->getParent()); ?></caption>
    <?php foreach ($view['data'] as $key => $row): ?>
        <tr><td><?php echo $key; ?></td>
        <?php foreach ($row as $field => $value): ?>
            <td><?php echo $value ?></td>
        <?php endforeach; ?>
        </tr>    
    <?php endforeach; ?>
</table>