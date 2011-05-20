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
class NethGui_Module_NotificationArea extends NethGui_Core_Module_Standard implements NethGui_Core_ValidationReportInterface, NethGui_Core_NotificationCarrierInterface
{

    private $errors = array();
    private $redirectOrders = array();
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

    public function process(NethGui_Core_NotificationCarrierInterface $carrier)
    {
        parent::process($carrier);

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
            list($fieldName, $message, $module) = $error;

            $renderer = new NethGui_Renderer_Xhtml($view->spawnView($module));
            $controlId = $renderer->getFullId($fieldName);

            $view['validationErrors'][] = array($controlId, $renderer->translate($fieldName . '_label'), $renderer->translate($message));
        }

        // Transfer dialog data to view
        $view['dialogs'] = new ArrayObject();

        foreach ($this->user->getDialogBoxes() as $dialog) {
            $dialogView = $view->spawnView($dialog->getModule());
            $dialogView->copyFrom(
                array(
                    'dialogId' => $dialog->getId(),
                    'message' => $dialog->getMessage(),
                    'actions' => $this->makeActionViewsForDialog($dialog),
                    'type' => $dialog->getType(),
            ));
            $dialogView->setTemplate('NethGui_Template_NotificationAreaDialogBox');
            $view['dialogs'][] = $dialogView;
        }
    }
    
    private function makeActionViewsForDialog($dialog)
    {
        $views = new ArrayObject();

        foreach ($dialog->getActions() as $action) {
            $view = new NethGui_Core_View($dialog->getModule());
            $view['name'] = $action[0];
            $view['location'] = $action[1];
            $view['data'] = $action[2];

            $dismissView = $view->spawnView($this, 'dismissView');
            $dismissView['dismissDialog'] = $dialog->getId();
            $dismissView->setTemplate(array($this, 'renderDismissNotification'));

            $view->setTemplate(array($this, 'renderDialogAction'));
            $views[] = $view;
        }

        return $views;
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

    public function addRedirectOrder(NethGui_Core_ModuleInterface $module, $path = array())
    {
        $this->redirectOrders[] = array($module, $path);
    }

    public function getRedirectOrder()
    {
        if ( ! isset($this->redirectOrders[0])) {
            return NULL;
        }

        list($module, $path) = $this->redirectOrders[0];

        do {
            array_unshift($path, $module->getIdentifier());
            $module = $module->getParent();
        } while ( ! is_null($module));

        return NethGui_Framework::getInstance()->buildUrl($path, array());
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