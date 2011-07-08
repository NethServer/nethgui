<?php
/**
 * NethGui
 *
 * @package Module
 */

/**
 * Carries notification messages to the User.
 * 
 * Keeps persistent messages into User session.
 *
 * @package Module
 */
class NethGui_Module_NotificationArea extends NethGui_Core_Module_Standard implements NethGui_Core_ValidationReportInterface
{

    private $errors = array();
    /**
     *
     * @var NethGui_Core_UserInterface;
     */
    private $user;

    public function __construct(NethGui_Core_UserInterface $user)
    {
        parent::__construct(NULL);
        $this->user = $user;
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('dismissDialog', '/^[a-zA-Z0-9]+$/');
    }

    public function process()
    {
        parent::process();

        if ($this->parameters['dismissDialog'] != '') {
            $this->user->dismissDialogBox($this->parameters['dismissDialog']);
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        // Transfer validation errors to view
        $view['validationErrors'] = new ArrayObject();

        foreach ($this->errors as $error) {
            list($fieldName, $errorInfo, $module) = $error;

            $errorView = $view->spawnView($module);
            $errorView->setTemplate('NethGui_Template_ValidationError');
            $errorView['errorInfo'] = array($errorView->translate($errorInfo[0]), $errorInfo[1]);
            $errorView['fieldName'] = $fieldName;
            $errorView['fieldId'] = $errorView->getUniqueId($fieldName);
            $errorView['fieldLabel'] = $errorView->translate($fieldName . '_label');

            $view['validationErrors'][] = $errorView;
        }


        if (count($this->errors) == 1) {
            $view['validationLabel'] = $view->translate('Incorrect value');
        } elseif (count($this->errors) > 1) {
            $view['validationLabel'] = $view->translate('Incorrect values');
        }


        $dialogBoxList = $this->user->getDialogBoxes();

        // Transfer dialog data to view
        $view['dialogs'] = new ArrayObject();

        foreach ($dialogBoxList as $dialog) {
            $dialogView = $view->spawnView($dialog->getModule());
            $dialogView->copyFrom(
                array(
                    'dialogId' => $dialog->getId(),
                    'message' => $dialogView->translate($dialog->getMessage()),
                    'actions' => $this->makeActionViewsForDialog($dialog, $mode),
                    'type' => $dialog->getType(),
            ));
            $dialogView->setTemplate('NethGui_Template_NotificationAreaDialogBox');
            $view['dialogs'][] = $dialogView;
        }
    }

    private function makeActionViewsForDialog($dialog, $mode)
    {
        $actionViews = new ArrayObject();

        foreach ($dialog->getActions() as $action) {
            $view = new NethGui_Core_View($dialog->getModule());
            $view['name'] = $action[0];
            if ($mode == self::VIEW_CLIENT) {
                // Translate the `location` in a URL for FORM action attribute
                $path = $view->getModulePath();
                $path[] = $action[1];
                $view['location'] = NethGui_Framework::getInstance()->buildUrl($path);
            } else {
                $view['location'] = $action[1];
            }
            $view['data'] = $action[2];

            $dismissView = $view->spawnView($this, 'dismissView');
            $dismissView['dismissDialog'] = $dialog->getId();
            $dismissView->setTemplate(array($this, 'renderDismissNotification'));

            $view->setTemplate(array($this, 'renderDialogAction'));
            $actionViews[] = $view;
        }

        return $actionViews;
    }

    public function renderDialogAction(NethGui_Renderer_Abstract $view)
    {
        $form = $view->form($view['location'], 0, 'NotificationDialog_Action_' . $view['name']);
        $form->inset('dismissView');
        $form->hidden('data');
        $form->button($view['name'], NethGui_Renderer_Abstract::BUTTON_SUBMIT);
        return $view;
    }

    public function renderDismissNotification(NethGui_Renderer_Abstract $view)
    {
        $view->hidden('dismissDialog');
        return $view;
    }

    public function addValidationError(NethGui_Core_ModuleInterface $module, $fieldId, $message)
    {
        $this->errors[] = array($fieldId, $message, $module);
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

}