<h<?php echo $view['titleLevel']; ?>><?php echo htmlspecialchars($view->translate($view['title'])) ?></h<?php echo $view['titleLevel']; ?>>
<p><?php echo htmlspecialchars($view->translate($view['description'])) ?></p>
<?php if(count($view['fields']) == 0) return; ?>
<dl>
    <?php foreach ($view['fields'] as $field): ?>
        <dt class="<?php echo $field['helpId'] ?>" ><?php echo htmlspecialchars($view->translate($field['label'])); ?></dt>
        <dd>Describe <tt><?php echo htmlspecialchars($view->translate($field['label'])); ?></tt> here..</dd>
    <?php endforeach; ?>
</dl>
