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
interface NethGui_Core_ViewInterface extends ArrayAccess, IteratorAggregate
{

    /**
     * Set the template to be applied to this object.
     *
     * If a string is given, it identifies a php script
     * (ie. NethGui_View_MyTemplate).
     *
     * If a callback function is givent, it is invoked with an array
     * representing the view state as argument and is expected to return
     * a string representing the view.
     *
     * @see render();
     * @param string|callback $template The template converting the view state to a string
     */
    public function setTemplate($template);

    /**
     * Assign data to the View state.
     * @param $data
     */
    public function copyFrom($data);

    /**
     * Returns the View associated with $module.
     *
     * @return NethGui_Core_ViewInterface
     */
    public function getInnerView(NethGui_Core_ModuleInterface $module);

    /**
     * Renders a string representation of the view.
     * @see setTemplate();
     * @return string
     */
    public function render();
    
  
}

?>
