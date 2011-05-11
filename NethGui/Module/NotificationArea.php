<?php
/**
 * NethGui
 *
 * @package Module
 */

/**
 * Carries notification messages to the User.
 *
 * @package Module
 */
class NethGui_Module_NotificationArea extends NethGui_Core_Module_Abstract implements NethGui_Core_ValidationReportInterface, NethGui_Core_NotificationCarrierInterface
{

    private $errors = array();
    private $dialogs = array(array(), array(), array());
    private $redirectOrders = array();

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

        $view['notifications'] = new ArrayObject();

        foreach ($this->dialogs as $type => $dialogTypeList) {
            foreach ($dialogTypeList as $subscriber) {
                
                list($module, $template) = $subscriber;
                
                $innerView = $view->spawnView($module, FALSE);
                $innerView['__type'] = $type;
                $innerView['__message'] = T("dialog_${type}_message_" . $module->getIdentifier());
                $view['notifications'][] = $innerView;

                if (is_null($template)) {
                    $innerView->setTemplate('NethGui_Template_NotificationMessage');
                } else {
                    $innerView->setTemplate($template);
                }

                if (method_exists($module, 'prepareDialogView')) {
                    $module->prepareDialogView($innerView, $type);
                } 
            }
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

    public function showDialog(NethGui_Core_ModuleInterface $module, $template = NULL, $type = self::NOTIFY_SUCCESS)
    {
        $this->dialogs[intval($type)][] = array($module, $template);
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

}