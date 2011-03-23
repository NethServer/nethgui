<h2>Impostazioni FTP</h2>

<div>
    <fieldset>
        <legend>Accesso FTP</legend>
        <?php foreach ($parameters['allowFtpOptions'] as $value => $label) : ?><div>
            <?php echo form_radio($name['allowFtp'], $value, $value == $parameters['allowFtp'], "id='{$id['allowFtp']}_{$value}'") ?>
            <label for="<?php echo $id['allowFtp'] . '_' . $value ?>"><?php echo htmlspecialchars($label) ?></label>
        </div><?php endforeach; ?>
    </fieldset>
</div>

<div>
    <?php echo form_checkbox($name['ftpPassword'], 1, $parameters['ftpPassword'], "id='{$id['ftpPassword']}'") ?>
    <label for="<?php echo $id['ftpPassword'] ?>">Consenti l'uso delle password</label>
</div>
