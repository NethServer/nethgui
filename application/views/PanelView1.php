<?php if(! $module instanceof ModuleInterface) die("Invalid Panel instance."); ?>

<h1>Sample View: <code><?php echo basename(__FILE__) ?></code></h1>

<fieldset><legend>User informations</legend>

    <div>
        <label for="<?php echo $module->getIdAttribute('fn'); ?>">First name</label>:
        <input type="text" id="<?php echo $module->getIdAttribute('fn') ?>" name="<?php echo $module->getNameAttribute('fn') ?>" />
    </div>

    <div>
        <label for="<?php echo $module->getIdAttribute('ln'); ?>">Last name</label>:
        <input type="text" id="<?php echo $module->getIdAttribute('ln') ?>" name="<?php echo $module->getNameAttribute('ln') ?>" />
    </div>

    <button type="submit" name="sendDataBtn">Send data</button>
</fieldset>



