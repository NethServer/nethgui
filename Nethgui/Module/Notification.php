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
class Notification extends \Nethgui\Module\AbstractModule implements \Nethgui\Component\DependencyConsumer
{
    /**
     *
     * @var \Nethgui\Model\UserNotifications
     */
    private $notifications;

    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->validationErrors = new \ArrayObject();
    }

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $attributes = new SystemModuleAttributesProvider();
        $attributes->initializeFromModule($this);
        return $attributes;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if(count($this->validationErrors) > 0) {

            $e = array('fields' => array());
            foreach($this->validationErrors as $fieldError) {
                $tmpView = $view->spawnView($fieldError['module']);
                $e['fields'][] = array(
                    'label' => $tmpView->translate($fieldError['parameter'] . '_label'),
                    'name' => $tmpView->getClientEventTarget($fieldError['parameter']),
                    'parameter' => $tmpView->getUniqueId($fieldError['parameter']),
                    'reason' => $tmpView->translate($fieldError['message'], $fieldError['args'])
                );
            }
            $this->notifications->validationError($e);
        }
        $view['notifications'] = \iterator_to_array($this->notifications);
    }

    public function setUserNotifications(\Nethgui\Model\UserNotifications $n)
    {
        $this->notifications = $n;
        return $this;
    }

    public function setValidationErrors(\Traversable $errors)
    {
        $this->validationErrors = $errors;
        return $this;
    }

    public function getTemplates()
    {
        return $this->notifications->getTemplates();
    }

    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setUserNotifications'),
            'ValidationErrors' => array($this, 'setValidationErrors')
        );
    }

}