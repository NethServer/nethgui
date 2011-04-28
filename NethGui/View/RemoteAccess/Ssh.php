<h2><?php echo T('Secure shell') ?></h2>

<div>
    <label for="<?php echo $id['accessMode'] ?>"><?php echo T('Allow ssh connections from') ?></label>
    <?php echo form_dropdown(
        $name['accessMode'],
        $parameters['accessModeOptions'],
        $parameters['accessMode'],
        "id='{$id['accessMode']}'") ?>
    <label for="<?php echo $id['sshdPort'] ?>"><?php echo T('Sshd port') ?></label>
    <?php echo form_input(array(
        'name'=>$name['sshdPort'],
        'size' => 4,
        'maxlength' => 5,
        'id' => $id['sshdPort'],
        'value' => $parameters['sshdPort']
        )) ?>   
</div>

<div>
    <?php echo form_checkbox($name['allowRootAccess'], 1, $parameters['allowRootAccess'], "id='{$id['allowRootAccess']}'") ?>
    <label for="<?php echo $id['allowRootAccess'] ?>"><?php echo T('Allow root access') ?></label>
</div>
<div>
    <?php echo form_checkbox($name['allowPassword'], 1, $parameters['allowPassword'], "id='{$id['allowPassword']}'") ?>
    <label for="<?php echo $id['allowPassword'] ?>"><?php echo T('Allow passwords') ?></label>
</div>
