<h<?php echo $view['titleLevel']; ?>><?php echo T($view['title']) ?></h<?php echo $view['titleLevel']; ?>>
<p><?php echo T($view['description']) ?></p>
<?php if(count($view['fields']) == 0) return; ?>
<dl>
    <?php foreach ($view['fields'] as $field): ?>
        <dt class="<?php echo $field['helpId'] ?>" ><?php echo T($field['label']); ?></dt>
        <dd>Describe <tt><?php echo T($field['label']); ?></tt> here..</dd>
    <?php endforeach; ?>
</dl>
