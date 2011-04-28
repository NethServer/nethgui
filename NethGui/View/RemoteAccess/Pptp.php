<h2><?php echo T('PPTP Settings') ?></h2>

<div>
    <label for="<?php echo $id['status'] . '_disabled' ?>"><?php echo T('Disabled') ?></label>
    <?php echo form_radio($name['status'], 'disabled', ('disabled' == $parameters['status']), "id='{$id['status']}_disabled'") ?>
</div>


<div>
    <label for="<?php echo $id['status'] . '_enabled' ?>"><?php echo T('Enabled') ?></label>
    <?php echo form_radio($name['status'], 'enabled', ('enabled' == $parameters['status']), "id='{$id['status']}_enabled'") ?>

    <label for="<?php echo $id['client'] ?>"><?php echo T('Number of PPTP clients') ?></label>
    <?php echo form_input(array(
        'name'=>$name['client'],
        'size' => 4,
        'maxlength' => 5,
        'id' => $id['client'],
        'value' => $parameters['client']
        )) ?>
</div>

