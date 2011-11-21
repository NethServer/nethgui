<?php

class Nethgui_Core_LoggingCommandReceiver implements Nethgui_Core_CommandReceiverInterface
{

    public function executeCommand($name, $arguments)
    {
        $log = new Nethgui_Log_Syslog();
        $log->info('TODO: executeCommand ' . $name . '(' . strtr(print_r($arguments, 1), "\n", " ") . ')');
    }

}