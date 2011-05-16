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

        $view['validationErrors'] = new ArrayObject();

        foreach ($this->errors as $error) {
            list($fieldName, $message, $module) = $error;

            $renderer = new NethGui_Renderer_Xhtml($view->spawnView($module));
            $controlId = $renderer->getFullId($fieldName);

            $view['validationErrors'][] = array($controlId, T($fieldName . '_label'), T($message));
        }

        $view['dialogs'] = new ArrayObject();

        foreach ($this->user->getDialogBoxes() as $dialogId => $dialog) {
                       
            $dialogData = array(
                'dialogId' => $dialogId,
                'message' => $dialog->getMessage(),
                'actions' => $dialog->getActionViews($this),
                'type' => $dialog->getType(),
            );

            $view['dialogs'][] = $dialogData;
        }
    }

    public function addValidationError(NethGui_Core_ModuleInterface $module, $fieldId, $message)
    {
        $this->errors[] = array($fieldId, $message, $module);
    }

    public function hasValidationErrors()
    {
        return count($this->errors) > 0;
    }

    public function showDialog(NethGui_Core_ModuleInterface $module, $message, $actions = array(), $type = self::NOTIFY_SUCCESS)
    {
        $surrogate = new NethGui_Core_ModuleSurrogate($module);
        $dialog = new NethGui_Core_DialogBox($surrogate, $message, $this->sanitizeActions($actions), $type);
        $this->user->showDialogBox($dialog);
    }
    
    private function sanitizeActions($actions) {
        $sanitizedActions = array();
        
        foreach($actions as $action) {
            if(is_string($action)) {
                $action = array($action, '', array());
            }
            
            if(!isset($action[1])) {
                $action[1] = '';
            }
            
            if(!isset($action[2])) {
                $action[2] = array();
            }
            
            $sanitizedActions[] = $action;
        }
        
        return $sanitizedActions;
    }

    public function addRedirectOrder(NethGui_Core_ModuleInterface $module, $path = array())
    {
        $this->redirectOrders[] = array($module, $path);
    }

    public function getRedirectOrder()
    {
        if (isset($this->redirectOrders[0])) {
            return $this->redirectOrders[0];
        }

        return NULL;
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