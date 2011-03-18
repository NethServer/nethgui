<h2>PPTP</h2>

<div>
    <label for="<?php echo $id['client'] ?>">Numero di client</label>
    <?php echo form_input(array(
        'name'=>$name['client'],
        'size' => 4,
        'maxlength' => 5,
        'id' => $id['client'],
        'value' => $parameters['client']
        )) ?>
</div>

