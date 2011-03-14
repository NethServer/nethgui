<h2>Impostazioni Secure Shell (ssh)</h2>
<p>
    <small>E' possibile impostare l'accesso ssh al server. L'impostazione
        pubblico dovrebbe essere abilitata solo da amministratori esperti per
        diagnosi e risoluzione problemi da remoto. Il valore raccomandato
        per questo parametro è "Nessun Accesso" a meno di specifiche ragioni
        contrarie.</small>
</p>

<div>
    <label for="<?php echo $id['accessMode'] ?>">Ammetti connessioni sshd da</label>
    <?php echo form_dropdown(
        $name['accessMode'],
        $parameter['accessModeOptions'],
        $parameter['accessMode'],
        "id='{$id['accessMode']}'") ?>

</div>

<div>
    <label for="<?php echo $id['allowRootAccess'] ?>">Consenti accesso per l'utente <tt>root</tt></label>
    <?php echo form_checkbox($name['allowRootAccess'], 1, $parameter['allowRootAccess'], "id='{$id['allowRootAccess']}'") ?>

</div>
<div>
    <label for="<?php echo $id['allowPassword'] ?>">Consenti l'uso delle password</label>
    <?php echo form_checkbox($name['allowPassword'], 1, $parameter['allowPassword'], "id='{$id['allowPassword']}'") ?>

</div>
<div>
    <label for="<?php echo $id['sshdPort'] ?>">Porta <tt>sshd</tt></label>
    <?php echo form_input(array(
        'name'=>$name['sshdPort'], 
        'size' => 4, 
        'maxlength' => 5, 
        'id' => $id['sshdPort'],
        'value' => $parameter['sshdPort']
        )) ?>    
</div>