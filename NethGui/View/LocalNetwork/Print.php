<?php if($view['enabled']): ?>
<div>
    <label for="<?php echo $id['network'] ?>"><?php echo T('Indirizzo di rete') ?></label>    
    <span id="<?php echo $id['network'] ?>"><?php echo $parameters['network'] ?></span>
</div>
<div>
    <label for="<?php echo $id['Mask'] ?>"><?php echo T('Maschera di rete') ?></label>
    <span id="<?php echo $id['Mask'] ?>"><?php echo $parameters['Mask'] ?></span>
 
</div>
<div>
    <label for="<?php echo $id['Router'] ?>"><?php echo T('Indirizzo del router') ?></label>
    <span id="<?php echo $id['Router'] ?>"><?php echo $parameters['Router'] ?></span>
</div>
<?php else : ?>
<em>print disabled</em>
<?php endif; ?>