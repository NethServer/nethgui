<div>
    <label for="<?php echo $id['network'] ?>"><?php echo T('Network address') ?></label>
    <input type="text"
           <?php if($view['action'] == 'update') { echo  'readonly="readonly"'; }; ?>
           <?php if(!isset($parameters['network'])) { echo  'disabled="disabled"'; }; ?>
           id="<?php echo $id['network'] ?>"
           name="<?php echo $name['network'] ?>"
           value="<?php echo isset($parameters['network']) ? $parameters['network'] : '' ?>">
</div>
<div>
    <label for="<?php echo $id['Mask'] ?>"><?php echo T('Network mask') ?></label>
    <input type="text"
           <?php if(!$view['enabled']) { echo  'disabled="disabled"'; }; ?>
           id="<?php echo $id['Mask'] ?>"
           name="<?php echo $name['Mask'] ?>"
           value="<?php echo $parameters['Mask'] ?>">
</div>
<div>
    <label for="<?php echo $id['Router'] ?>"><?php echo T('Router address') ?></label>
    <input type="text"
           <?php if(!$view['enabled']) { echo  'disabled="disabled"'; }; ?>
           id="<?php echo $id['Router'] ?>"
           name="<?php echo $name['Router'] ?>"
           value="<?php echo $parameters['Router'] ?>">
</div>

<div>
    <?php echo form_submit($view['action'], $view['action']); ?>
    <?php echo anchor($view->buildUrl('..'), 'Cancel'); ?>

</div>