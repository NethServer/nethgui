<?php
/**
 * NethGui
 *
 * @package Modules
 */

/**
 * TODO: describe class
 *
 * @package Modules
 * @subpackage RemoteAccess
 */
final class NethGui_Module_RemoteAccess_PptpModule extends NethGui_Core_Module_Standard {

    public function  getDescription()
    {
        return "E' possibile abilitare l'accesso PPTP al server. Questa funzionalità dovrebbe rimanere disabilitata impostando a 0 il valore, a meno che sia necessario l'accesso PPTP.";
    }

}
