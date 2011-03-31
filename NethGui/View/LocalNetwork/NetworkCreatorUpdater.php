<div>
    <label for="<?php echo $id['network'] ?>"><?php echo T('Indirizzo di rete') ?></label>
    <input type="text"
           id="<?php echo $id['network'] ?>"
           name="<?php echo $name['network'] ?>"
           value="<?php echo $parameters['network'] ?>">
</div>
<div>
    <label for="<?php echo $id['mask'] ?>"><?php echo T('Maschera di rete') ?></label>
    <input type="text"
           id="<?php echo $id['mask'] ?>"
           name="<?php echo $name['mask'] ?>"
           value="<?php echo $parameters['mask'] ?>">
</div>
<div>
    <label for="<?php echo $id['router'] ?>"><?php echo T('Indirizzo del router') ?></label>
    <input type="text"
           id="<?php echo $id['router'] ?>"
           name="<?php echo $name['router'] ?>"
           value="<?php echo $parameters['router'] ?>">
</div>