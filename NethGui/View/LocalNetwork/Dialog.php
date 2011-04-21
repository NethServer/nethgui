<div>
    <label for="<?php echo $id['network'] ?>"><?php echo T('Indirizzo di rete') ?></label>    
    <input type="text"
           <?php if($view['action'] == 'update') { echo  'readonly="readonly"'; }; ?>
           <?php if(!$view['enabled']) { echo  'disabled="disabled"'; }; ?>
           id="<?php echo $id['network'] ?>"
           name="<?php echo $name['network'] ?>"
           value="<?php echo $parameters['network'] ?>">

</div>
<div>
    <label for="<?php echo $id['Mask'] ?>"><?php echo T('Maschera di rete') ?></label>
    <input type="text"
           <?php if(!$view['enabled']) { echo  'disabled="disabled"'; }; ?>
           id="<?php echo $id['Mask'] ?>"
           name="<?php echo $name['Mask'] ?>"
           value="<?php echo $parameters['Mask'] ?>">
</div>
<div>
    <label for="<?php echo $id['Router'] ?>"><?php echo T('Indirizzo del router') ?></label>
    <input type="text"
           <?php if(!$view['enabled']) { echo  'disabled="disabled"'; }; ?>
           id="<?php echo $id['Router'] ?>"
           name="<?php echo $name['Router'] ?>"
           value="<?php echo $parameters['Router'] ?>">
</div>