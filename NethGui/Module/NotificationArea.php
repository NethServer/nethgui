<?php
/**
 * NethGui
 *
 * @package Module
 */

/**
 * Displays messages to the User.
 *
 * @package Module
 */
class NethGui_Module_NotificationArea extends NethGui_Core_Module_Abstract implements NethGui_Core_ValidationReportInterface
{

    private $errors = array();

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view->setTemplate(array($this, 'render'));

        $view['validationErrors'] = new ArrayObject();

        foreach ($this->errors as $error) {
            list($fieldId, $message, $module) = $error;

            $view['validationErrors'][] = array($module->getIdentifier() . '.' . $fieldId, $message);
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

    public function render(NethGui_Renderer_Abstract $view)
    {
        $area = $view->panel('');

        if ( count($view['validationErrors']) > 0) {
            $panel = $area->panel('errors')
                ->append('<ul>', FALSE);

            foreach ($view['validationErrors'] as $error) {
                $panel->append('<li>' . htmlspecialchars(implode(' MUST BE ', $error)) . '</li>', FALSE);
            }

            $panel->append('</ul>', FALSE);
        }

        return $view;
    }

}