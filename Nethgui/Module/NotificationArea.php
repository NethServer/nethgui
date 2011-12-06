<?php
namespace Nethgui\Module;

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

/**
 * Carries notification messages to the User.
 * 
 * Keeps persistent messages into User session through the NotificationManager object
 *
 */
class NotificationArea extends \Nethgui\Core\Module\Standard
{

    /**
     *
     * @var \Nethgui\Core\NotificationManagerInterface
     */
    private $notificationManager;

    public function setNotificationManager(\Nethgui\Core\NotificationManagerInterface $notificationManager)
    {
        $this->notificationManager = $notificationManager;
        return $this;
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('dismissDialogBox', '/^[a-zA-Z0-9]+$/');
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        unset($view['dismissDialogBox']);

        if ($this->parameters['dismissDialogBox']) {
            $view[] = $view->getCommandFactory()->createUiCommand('dismissDialogBox', array($this->parameters['dismissDialogBox']));
        }
        
    }

    public function getNotifications()
    {
        return $this->notificationManager->getNotifications();
    }

    public function getDefaultUiStyleFlags()
    {
        return self::STYLE_NOFORMWRAP;
    }

}
