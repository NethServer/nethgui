<h1>Sample View: <code><?php echo basename(__FILE__) ?></code></h1>

<?php echo form_fieldset('User informations') ?>

<div>
 <?php echo form_label('Name', 'user_name') . form_input('user_name') ?>
</div>
<div>
 <?php echo form_label('Surname', 'user_surname') . form_input('user_surname') ?>
</div>

<?php echo form_button(array(
    'name' => 'apply',
    'content' => 'Send data',
    'type' => 'submit',
    'value' => 1)) .
 form_fieldset_close();
?>

Has PDP:<pre><?php echo $pdpName ?></pre>

Panel:<pre><?php print_r($panel) ?></pre>
