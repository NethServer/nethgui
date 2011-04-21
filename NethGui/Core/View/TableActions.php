
<?php foreach($view as $action => $value): ?>
<a href="<?php echo $value ?>">
    <?php echo T($action) ?>
</a>
<?php endforeach; ?>