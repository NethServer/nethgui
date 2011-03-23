<h2>PPTP</h2>


<div>
    <label for="<?php echo $id['status'] . '_disabled' ?>"><?php echo T('Disabilitato') ?></label>
    <?php echo form_radio($name['status'], 'disabled', ('disabled' == $parameters['status']), "id='{$id['status']}_disabled'") ?>
</div>


<div>
    <label for="<?php echo $id['status'] . '_enabled' ?>"><?php echo T('Abilitato') ?></label>
    <?php echo form_radio($name['status'], 'enabled', ('enabled' == $parameters['status']), "id='{$id['status']}_enabled'") ?>

    <label for="<?php echo $id['client'] ?>">Numero di client</label>
    <?php echo form_input(array(
        'name'=>$name['client'],
        'size' => 4,
        'maxlength' => 5,
        'id' => $id['client'],
        'value' => $parameters['client']
        )) ?>
</div>

