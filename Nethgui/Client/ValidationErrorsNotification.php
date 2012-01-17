<?php
namespace Nethgui\Client;

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
 * Keep track of validation errors from modules
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class ValidationErrorsNotification extends AbstractNotification implements \Nethgui\Controller\ValidationReportInterface
{

    /**
     * Validation error counter
     *
     * @var integer
     */
    private $errors;

    public function __construct()
    {
        $this->errors = array();
        parent::__construct(parent::NOTIFY_ERROR, 'ValidationErrors', TRUE);
    }

    public function addValidationErrorMessage(\Nethgui\Module\ModuleInterface $module, $parameterName, $message, $args = array())
    {
        $this->errors[] = array(
            'module' => new ModuleSurrogate($module),
            'parameter' => $parameterName,
            'message' => $message,
            'args' => $args
        );
    }

    public function addValidationError(\Nethgui\Module\ModuleInterface $module, $parameterName, \Nethgui\System\ValidatorInterface $validator)
    {
        foreach ($validator->getFailureInfo() as $failureInfo) {
            if ( ! isset($failureInfo[1])) {
                $failureInfo[1] = array();
            }
            $this->addValidationErrorMessage($module, $parameterName, $failureInfo[0], $failureInfo[1]);
        }
    }

    public function serialize()
    {
        $p = parent::serialize();
        return serialize(array($p, $this->errors));
    }

    public function unserialize($serialized)
    {
        list($p, $this->errors) = unserialize($serialized);
        parent::unserialize($p);
    }

    public function hasValidationErrors()
    {
        return count($this->errors) > 0;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['title'] = count($this->errors) > 1 ? $view->translate('Incorrect values') : $view->translate('Incorrect value');
        $errors = array();
        foreach ($this->errors as $error) {
            $innerView = $view->spawnView($error['module']);
            $errors[] = array(
                'label' => $innerView->translate($error['parameter'] . '_label'),
                'message' => $innerView->translate($error['message'], $error['args']),
                'widgetId' => $innerView->getUniqueId($error['parameter'])
            );
        }
        $view['errors'] = $errors;
    }

    public function renderXhtml(\Nethgui\Renderer\Xhtml $renderer)
    {
        $panel = parent::renderXhtml($renderer);

        $title = $renderer->textLabel('title')
            ->setAttribute('icon-before', 'ui-icon-alert')
            ->setAttribute('template', '${0}: ');

        $elementList = $renderer->panel()->setAttribute('tag', 'dl');

        foreach ($renderer['errors'] as $error) {


            $content = strtr('<dt><a href="%ID">%TITLE</a></dt><dd>%DESC</dd>', array(
                '%ID' => htmlspecialchars('#' . $error['widgetId']),
                '%TITLE' => htmlspecialchars($error['label']),
                '%DESC' => htmlspecialchars($error['message']),
                ));

            $elementList->insert($renderer->literal($content));
        }

        return $panel
                ->insert($title)
                ->insert($elementList);
    }

}

