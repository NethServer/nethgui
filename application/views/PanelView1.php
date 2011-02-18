<?php if(! $panel instanceof StandardPanel) die("Invalid Panel instance."); ?>

<h1>Sample View: <code><?php echo basename(__FILE__) ?></code></h1>

<fieldset><legend>User informations</legend>

    <div>
        <label for="<?php echo $panel->getIdAttribute('fn'); ?>">First name</label>:
        <input type="text" id="<?php echo $panel->getIdAttribute('fn') ?>" name="<?php echo $panel->getNameAttribute('fn') ?>" />
    </div>

    <div>
        <label for="<?php echo $panel->getIdAttribute('ln'); ?>">First name</label>:
        <input type="text" id="<?php echo $panel->getIdAttribute('ln') ?>" name="<?php echo $panel->getNameAttribute('ln') ?>" />
    </div>

    <button type="submit" name="sendDataBtn">Send data</button>
</fieldset>

<?php if(isset($inputParameters) && !empty($inputParameters))
    echo '<pre>' . print_r($inputParameters, 1) . '</pre>';
    ?>


