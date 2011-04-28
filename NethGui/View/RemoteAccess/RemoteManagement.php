<h2><?php echo T('Remote management') ?></h2>

<div>
    <label for="<?php echo $id['networkAddress'] ?>"><?php echo T('Network address') ?></label>
    <input type="text"
           id="<?php echo $id['networkAddress'] ?>"
           name="<?php echo $name['networkAddress'] ?>"
           value="<?php echo $parameters['networkAddress'] ?>">
</div>
<div>
    <label for="<?php echo $id['networkMask'] ?>"><?php echo T('Network mask') ?></label>
    <input type="text"
           id="<?php echo $id['networkMask'] ?>"
           name="<?php echo $name['networkMask'] ?>"
           value="<?php echo $parameters['networkMask'] ?>">
</div>