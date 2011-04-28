<h2><?php echo T('FTP settings') ?></h2>

<div>

        <?php foreach ($parameters['serviceStatusOptions'] as $value => $label) : ?><div>
            <?php echo form_radio($name['serviceStatus'], $value, $value == $parameters['serviceStatus'], "id='{$id['serviceStatus']}_{$value}'") ?>
            <label for="<?php echo $id['serviceStatus'] . '_' . $value ?>"><?php echo T($label) ?></label>

            <?php if ($value == 'anyNetwork'): ?>
            <?php echo form_checkbox($name['acceptPasswordFromAnyNetwork'], 1, (boolean) $parameters['acceptPasswordFromAnyNetwork'], "id='{$id['acceptPasswordFromAnyNetwork']}'") ?>
                <label for="<?php echo $id['acceptPasswordFromAnyNetwork'] ?>"><?php echo T('Use password authentication') ?></label>
            <?php endif; ?>
            </div><?php endforeach; ?>

</div>


