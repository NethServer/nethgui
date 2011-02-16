<h1>HelloView!</h1>

<?php
echo
form_open(current_url()) .
 form_fieldset('User informations') .
 form_button(array(
    'name' => 'apply',
    'content' =>
    'Send data',
    'type' => 'submit',
    'value' => 1)) .
 form_fieldset_close() .
 form_close()
?>

<pre><?php print_r($_POST) ?></pre>
