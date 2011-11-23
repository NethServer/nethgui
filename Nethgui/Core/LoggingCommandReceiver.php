<?php

namespace Nethgui\Core;

class LoggingCommandReceiver implements CommandReceiverInterface
{

    public function executeCommand($name, $arguments)
    {
        $log = new Nethgui\Log\Syslog();
        $log->info('TODO: executeCommand ' . $name . '(' . strtr(print_r($arguments, 1), "\n", " ") . ')');
    }

}
