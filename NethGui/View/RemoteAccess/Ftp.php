<h2>Impostazioni FTP</h2>

<div>
    <fieldset>
        <legend>Accesso FTP</legend>
        <?php foreach ($parameter['allowFtpOptions'] as $value => $label) : ?><div>
            <?php echo form_radio($name['allowFtp'], $value, $value == $parameter['allowFtp'], "id='{$id['allowFtp']}_{$value}'") ?>
            <label for="<?php echo $id['allowFtp'] . '_' . $value ?>"><?php echo htmlspecialchars($label) ?></label>
        </div><?php endforeach; ?>
    </fieldset>
</div>
<div>
    <label for="<?php echo $id['ftpPassword'] ?>">Consenti l'uso delle password</label>
    <?php echo form_checkbox($name['ftpPassword'], 1, $parameter['ftpPassword'], "id='{$id['ftpPassword']}'") ?>

</div>
