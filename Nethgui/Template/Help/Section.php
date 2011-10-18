<h<?php echo $view['titleLevel']; ?>><?php echo T($view['title']) ?></h<?php echo $view['titleLevel']; ?>>
<p><?php echo T($view['description']) ?></p>
<?php if(count($view['fields']) == 0) return; ?>
<dl>
    <?php foreach ($view['fields'] as $name => $label): ?>
        <dt class="<?php echo $name ?>" ><?php echo T($label); ?></dt>
        <dd>Describe <tt><?php echo T($label); ?></tt> here..</dd>
    <?php endforeach; ?>
</dl>
