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
 * Keeps persistent messages into User session.
 *
 */
class NotificationArea extends \Nethgui\Core\Module\Standard implements \Nethgui\Core\ValidationReportInterface, \Nethgui\Core\Module\DefaultUiStateInterface
{

    private $errors = array();

    /**
     *
     * @var \Nethgui\Client\UserInterface;
     */
    private $user;

    public function __construct(\Nethgui\Client\UserInterface $user)
    {
        parent::__construct(NULL);
        $this->user = $user;
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('dismissDialog', '/^[a-zA-Z0-9]+$/');
    }

    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        parent::bind($request);
        if ( ! $request->hasParameter('dismissDialog') && isset($_GET['dismissDialog'])) {
            $this->parameters['dismissDialog'] = $_GET['dismissDialog'];
        }
    }

    public function process()
    {
        parent::process();

        if ($this->parameters['dismissDialog'] != '') {
            $this->user->dismissDialogBox($this->parameters['dismissDialog']);
        }
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        $view['notifications'] = new \ArrayObject();

        if ($this->hasValidationErrors()) {
            $this->prepareValidationErrorNotification($view, $mode);
        }

        $this->prepareDialogBoxesNotification($view, $mode);
    }

    private function prepareDialogBoxesNotification(\Nethgui\Core\ViewInterface $view, $mode)
    {
        foreach ($this->user->getDialogBoxes() as $dialog) {
            // Spawn a view associated to the $dialog original module:
            $dialogView = $view->spawnView($dialog->getModule());
            $dialogView->setTemplate('Nethgui\Template\NotificationAreaDialogBox');
            $message = $dialog->getMessage();
            $dialogView->copyFrom(
                array(
                    'dialogId' => $dialog->getId(),
                    'type' => $dialog->getType(),
                    'message' => $dialogView->translate($message[0], $message[1]),
                    'transient' => $dialog->isTransient(),
                    'actions' => $this->makeActionViewsForDialog($dialog, $mode, $dialogView),
            ));
            $view['notifications'][] = $dialogView;
        }
    }

    private function prepareValidationErrorNotification(\Nethgui\Core\ViewInterface $view, $mode)
    {
        $validationView = $view->spawnView($this);
        $validationView->setTemplate('Nethgui\Template\ValidationError');

        if (count($this->errors) == 1) {
            $validationView['message'] = $view->translate('Incorrect value');
        } elseif (count($this->errors) > 1) {
            $validationView['message'] = $view->translate('Incorrect values');
        }

        $validationView['type'] = \Nethgui\Client\DialogBox::NOTIFY_ERROR;
        $validationView['dialogId'] = 'dlg' . substr(md5('Validation-' . microtime()), 0, 6);
        $validationView['transient'] = TRUE;
        $validationView['errors'] = new \ArrayObject();

        foreach ($this->errors as $index => $error) {
            list($module, $fieldName, $errorInfo) = $error;
            $eV = $validationView->spawnView($module);
            $eV->setTemplate(array($this, 'renderValidationError'));
            $eV['errorInfo'] = $this->prepareErrorMessage($eV, $errorInfo);
            $eV['fieldName'] = $fieldName;
            $eV['fieldId'] = $eV->getUniqueId($fieldName);
            $eV['fieldLabel'] = $eV->translate($fieldName . '_label');
            $validationView['errors'][] = $eV;
        }

        $view['notifications'][] = $validationView;
    }

    private function prepareErrorMessage(\Nethgui\Core\ViewInterface $eV, $errorInfo)
    {
        $parts = array();
        foreach($errorInfo as $error) {
            $parts[] = $eV->translate($error[0], $error[1]);
        }
        return implode(' ' . $eV->translate("valid_OR") . ' ', $parts);
    }

    public function renderValidationError(\Nethgui\Renderer\Xhtml $view)
    {
        return $view->button($view['fieldName'], \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_LINK)
                ->setAttribute('value', '#' . $view['fieldId'])
                ->setAttribute('title', str_replace("\n", " ", $view['errorInfo']));
    }

    private function makeActionViewsForDialog(\Nethgui\Client\DialogBox $dialog, $mode, \Nethgui\Core\ViewInterface $dialogView)
    {
        $actionViews = new \ArrayObject();

        foreach ($dialog->getActions() as $action) {
            $view = $dialogView->spawnView($dialog->getModule());

            if ($dialog->isTransient()) {
                $viewData = $action[2];
            } else {
                /*
                 * Merge the action data with the dismiss dialog commands:
                 * (note the starting `/` indicating an absolute path)
                 */
                $viewData = array_merge(
                    $action[2], array('/NotificationArea/dismissDialog' => $dialog->getId())
                );
            }

            if ($mode == self::VIEW_CLIENT) {
                $view['location'] = $view->getModuleUrl($action[1]);
                $view['name'] = $view->translate($action[0] . '_label');
                $view['data'] = $this->prepareDialogDataForClient($view, $viewData);
            } else {
                $view['location'] = $action[1];
                $view['name'] = $action[0];
                $view['data'] = $viewData;
                $view->setTemplate(array($this, 'renderDialogAction'));
                $view['transient'] = $dialog->isTransient();
            }

            $actionViews[] = $view;
        }

        return $actionViews;
    }

    private function prepareDialogDataForClient(\Nethgui\Core\ViewInterface $view, $data, $prefix='')
    {
        $output = array();

        foreach ($data as $key => $value) {
            if (empty($prefix)) {
                $innerPrefix = $key;
            } else {
                $innerPrefix = $prefix . '/' . $key;
            }

            if ($value instanceof \Traversable) {
                $value = iterator_to_array($value);
            }

            if (is_array($value)) {
                $output = array_merge($output, $this->prepareDialogDataForClient($value, $innerPrefix));
            } else {
                $controlName = implode('/', array_merge($view->getModulePath(), $innerPrefix));
                $output[$controlName] = strval($value);
            }
        }

        return $output;
    }

    public function renderDialogAction(\Nethgui\Renderer\Xhtml $view)
    {
        if ($view['transient'] && count($view['data']) == 0) {
            // render as link
            $widget = $view->button($view['name'], \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_LINK)->setAttribute('value', $view['location']);
        } else {
            // render as form
            $widget = $view->form()
                ->setAttribute('action', $view['location'])
                ->setAttribute('name', 'NotificationDialogAction_' . $view['name'])
                ->insert($view->hidden('data'))
                ->insert($view->button($view['name'], \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_SUBMIT));
        }

        return $widget;
    }

    public function addValidationErrorMessage(\Nethgui\Core\ModuleInterface $module, $parameterName, $message, $args = array())
    {
        $this->errors[] = array($module, $parameterName, array(array($message, $args)));
    }

    public function addValidationError(\Nethgui\Core\ModuleInterface $module, $parameterName, \Nethgui\Core\ValidatorInterface $validator)
    {
        $this->errors[] = array($module, $parameterName, $validator->getFailureInfo());
    }

    public function hasValidationErrors()
    {
        return count($this->errors) > 0;
    }

    public function dismissTransientDialogBoxes()
    {
        foreach ($this->user->getDialogBoxes() as $dialog) {
            if ($dialog->isTransient()) {
                $this->user->dismissDialogBox($dialog->getId());
            }
        }
    }

    public function getDefaultUiStyleFlags()
    {
        return self::STYLE_NOFORMWRAP;
    }

}
