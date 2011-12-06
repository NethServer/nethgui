<?php
namespace Nethgui\Core;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

class NotificationManager implements ValidationReportInterface, NotificationManagerInterface, CommandReceiverInterface
{

    /**
     * Validation error counter
     * 
     * @var integer
     */
    private $errors;

    /**
     *
     * @var \ArrayObject
     */
    private $notifications;

    public function __construct(\Nethgui\Core\SessionInterface $session)
    {
        $this->errors = 0;

        $key = get_class($this);

        if ( ! $session->hasElement($key)) {
            $session->store($key, new \ArrayObject());
        }

        $this->notifications = $session->retrieve($key);
        
        foreach (new \ArrayIterator($this->notifications) as $index => $dialogBox) {
            if ( ! $dialogBox instanceof \Nethgui\Client\DialogBox) {
                throw new \UnexpectedValueException(sprintf('%s: notifications must be instances of class \Nethgui\Client\DialogBox', get_class($this)), 1323168952);
            }
            
            if ($dialogBox->isTransient()) {
                $dialogBox->dismiss();
            }
            
            if ($dialogBox->isDismissed()) {
                unset($this->notifications[$index]);
            }
        }
    }

    public function addValidationErrorMessage(\Nethgui\Core\ModuleInterface $module, $parameterName, $message, $args = array())
    {
        $this->errors ++;
        $this->notifications[] = new \Nethgui\Client\DialogBox($module, array($parameterName . ': ' . $message, $args), array(), CommandFactoryInterface::NOTIFY_ERROR);
    }

    public function addValidationError(\Nethgui\Core\ModuleInterface $module, $parameterName, \Nethgui\Core\ValidatorInterface $validator)
    {
        foreach ($validator->getFailureInfo() as $failureInfo) {
            if ( ! isset($failureInfo[1])) {
                $failureInfo[1] = array();
            }
            $this->addValidationErrorMessage($module, $parameterName, $failureInfo[0], $failureInfo[1]);
        }
    }

    public function hasValidationErrors()
    {
        return $this->errors > 0;
    }

    /**
     * Invoked during XHTML rendering
     *
     * @param type $name
     * @param type $arguments
     */
    public function executeCommand($name, $arguments)
    {
        if ($name === 'showDialogBox') {
            $this->showDialogBox($arguments[0]);
        } else if ($name === 'dismissDialogBox') {
            $this->dismissDialogBox($arguments[0]);
        }
    }

    protected function showDialogBox(\Nethgui\Client\DialogBox $dialogBox)
    {
        $this->notifications[$dialogBox->getId()] = $dialogBox;
    }

    protected function dismissDialogBox($dialogId)
    {
        if (isset($this->notifications[$dialogId])) {
            $this->notifications[$dialogId]->dismiss();
        }
    }

    public function getNotifications()
    {
        return $this->notifications;
    }

}
