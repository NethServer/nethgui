<h2>Impostazioni FTP</h2>

<div>

        <?php foreach ($parameters['serviceStatusOptions'] as $value => $label) : ?><div>
            <?php echo form_radio($name['serviceStatus'], $value, $value == $parameters['serviceStatus'], "id='{$id['serviceStatus']}_{$value}'") ?>
            <label for="<?php echo $id['serviceStatus'] . '_' . $value ?>"><?php echo htmlspecialchars($label) ?></label>

            <?php if ($value == 'anyNetwork'): ?>
            <?php echo form_checkbox($name['acceptPasswordFromAnyNetwork'], 1, (boolean) $parameters['acceptPasswordFromAnyNetwork'], "id='{$id['acceptPasswordFromAnyNetwork']}'") ?>
                <label for="<?php echo $id['acceptPasswordFromAnyNetwork'] ?>">Utilizza autenticazione con password</label>
            <?php endif; ?>
            </div><?php endforeach; ?>

</div>


