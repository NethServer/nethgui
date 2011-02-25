<?php if(! $module instanceof ModuleInterface) die("Invalid Module instance."); ?>

<h2>Gestione Remota</h2>
<p>
    E' possibile consentire l'accesso a computer su reti remote al
    server-manager, inserendo le reti abilitate qui. Utilizzare una
    subnet mask di 255.255.255.255 per limitare l'accesso ad un host specifico.
    I computer abilitati potranno accedere al server-manager in HTTPS.
</p>

<div>
    <label>Indirizzo di rete</label><input type="text" value="">
</div>
<div>
    <label>Maschera di rete</label><input type="text" value="">
</div>