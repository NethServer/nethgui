<h2>Gestione Remota</h2>
<p>
    E' possibile consentire l'accesso a computer su reti remote al
    server-manager, inserendo le reti abilitate qui. Utilizzare una
    subnet mask di 255.255.255.255 per limitare l'accesso ad un host specifico.
    I computer abilitati potranno accedere al server-manager in HTTPS.
</p>

<div>
    <label for="<?php echo $id['networkAddress'] ?>">Indirizzo di rete</label>
    <input type="text"
           id="<?php echo $id['networkAddress'] ?>"
           name="<?php echo $name['networkAddress'] ?>"
           value="<?php echo $parameter['networkAddress'] ?>">
</div>
<div>
    <label for="<?php echo $id['networkMask'] ?>">Maschera di rete</label>
    <input type="text"
           id="<?php echo $id['networkMask'] ?>"
           name="<?php echo $name['networkMask'] ?>"
           value="<?php echo $parameter['networkMask'] ?>">
</div>