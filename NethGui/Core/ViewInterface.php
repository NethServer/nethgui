<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * Each module has a view attacched to it during prepareView operation.
 *
 * @package NethGuiFramework
 */
interface NethGui_Core_ViewInterface extends ArrayAccess
{

    /**
     * Set the template to be applied to this object.
     * @param mixed
     */
    public function setTemplate($template);

    /**
     * Specifies the data for the View.     
     * @param array $data
     */
    public function copyFrom($data);

    /**
     * @param string $parameterName
     * @param mixed $value
     */
    public function setParameterValue($parameterName, $value);

    /**
     * @param string $parameterName
     * @return mixed
     */
    public function getParameterValue($parameterName);

    /**
     * Returns the View associated with $module.
     *
     * @return NethGui_Core_ViewInterface
     */
    public function getInnerView(NethGui_Core_ModuleInterface $module);

    /**
     * Renders a string representation of the view.
     * @return string
     */
    public function render();
   
}

?>
