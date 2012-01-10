<h<?php echo $view['titleLevel']; ?>><?php echo htmlspecialchars($view['title']) ?></h<?php echo $view['titleLevel']; ?>>
<p><?php echo htmlspecialchars($view['description']) ?></p>
<?php if(count($view['fields']) == 0) return; ?>
<dl>
    <?php foreach ($view['fields'] as $field): ?>
        <dt class="<?php echo $field['helpId'] ?>" ><?php echo htmlspecialchars($field['label']); ?></dt>
        <dd><?php echo $T('Describe <tt>${0}</tt> here..', array($field['label'])) ?></dd>
    <?php endforeach; ?>
</dl>
