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
class Notification extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{
    /**
     *
     * @var \Nethgui\Model\UserNotifications
     */
    private $notifications;

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $attributes = new SystemModuleAttributesProvider();
        $attributes->initializeFromModule($this);
        return $attributes;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ($view->getTargetFormat() === $view::TARGET_XHTML && count($this->notifications) > 0) {
            $view->setTemplate(array($this, 'render'));
        } else {
            $view->setTemplate(FALSE);
        }
    }

    public function render(\Nethgui\Renderer\Xhtml $renderer)
    {
        $renderer->includeFile('Nethgui/Js/jquery.nethgui.notification.js');

        $panel = $renderer->panel()->setAttribute('tag', 'pre')
            ->insert($renderer->literal("Notifications:\n\n" . print_r(\iterator_to_array($this->notifications), 1)));

        return (String) $panel;
    }

    public function setModel(\Nethgui\Model\UserNotifications $model)
    {
        $this->notifications = $model;
        return $this;
    }

    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setModel')
        );
    }

}