<h2>Gestione Remota</h2>
<p>
    <?php echo __FILE__ ?>
    <small>E' possibile consentire l'accesso a computer su reti remote al
    server-manager, inserendo le reti abilitate qui. Utilizzare una
    subnet mask di 255.255.255.255 per limitare l'accesso ad un host specifico.
    I computer abilitati potranno accedere al server-manager in HTTPS.</small>
</p>

<div>
    <label for="<?php echo $id['networkAddress'] ?>"><?php echo T('Indirizzo di rete') ?></label>
    <input type="text"
           id="<?php echo $id['networkAddress'] ?>"
           name="<?php echo $name['networkAddress'] ?>"
           value="<?php echo $parameters['networkAddress'] ?>">
</div>
<div>
    <label for="<?php echo $id['networkMask'] ?>"><?php echo T('Maschera di rete') ?></label>
    <input type="text"
           id="<?php echo $id['networkMask'] ?>"
           name="<?php echo $name['networkMask'] ?>"
           value="<?php echo $parameters['networkMask'] ?>">
</div>