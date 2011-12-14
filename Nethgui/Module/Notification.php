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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Notification extends \Nethgui\Core\Module\Standard implements \Nethgui\Core\CommandReceiverInterface
{
    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->notifications = new \ArrayObject();
    }

    public function setSession(\Nethgui\Core\SessionInterface $session)
    {
        $key = get_class($this);

        if ($session->hasElement($key)) {
            $this->notifications = $session->retrieve($key);
        } else {
            $session->store($key, $this->notifications);
        }

        // Update notification state after retrieving objects from session:
        foreach (new \ArrayIterator($this->notifications) as $index => $notification) {
            if ( ! $notification instanceof \Nethgui\Client\AbstractNotification) {
                throw new \UnexpectedValueException(sprintf('%s: notifications must be instances of class \Nethgui\Client\AbstractNotification', get_class($this)), 1323168952);
            }

            // Transient notifications are dismissed:
            if ($notification->isTransient()) {
                $notification->dismiss();
            }

            // Dismissed notifications are dropped:
            if ($notification->isDismissed()) {
                unset($this->notifications[$index]);
            }
        }
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('dismiss', '/^[a-zA-Z0-9]+$/');
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        if ($view->getTargetFormat() === $view::TARGET_XHTML) {
            $view->setTemplate(array($this, 'render'));
        } else {
            $view->setTemplate(FALSE);
        }

        // Sends a dismiss command to itself:
        if ($this->parameters['dismiss']) {
            unset($view['dismiss']);
            $view->getCommandList()->dismissNotification($this->parameters['dismiss']);
        }

        $view['notifications'] = $this->notifications;
    }

    public function render(\Nethgui\Renderer\Xhtml $renderer)
    {
        $panel = $renderer->panel()->setAttribute('name', 'Pane')->setAttribute('receiver', '');

        foreach ($renderer['notifications'] as $offset => $notification) {
            $panel->insert($renderer->literal($offset));
        }

        return (String) $panel;
    }

    public function getDefaultUiStyleFlags()
    {
        return self::STYLE_NOFORMWRAP;
    }

    public function executeCommand(\Nethgui\Core\ViewInterface $origin, $selector, $name, $arguments)
    {
        if ($name === 'showNotification') {
            $this->showNotification($arguments[0]);
        } elseif ($name === 'showMessage') {
            $this->showNotification(new \Nethgui\Client\DialogBox($origin->getModule(), $arguments[0]));
        } elseif ($name === 'dismissNotification') {
            $this->dismissNotification($arguments[0]);
        }
    }

    protected function dismissNotification($notificationId)
    {
        if (isset($this->notifications[$notificationId])) {
            $this->notifications[$notificationId]->dismiss();
        }
    }

    protected function showNotification(\Nethgui\Client\AbstractNotification $notification)
    {
        $id = $notification->getIdentifier();
        $this->notifications[$id] = $notification;
    }

}
