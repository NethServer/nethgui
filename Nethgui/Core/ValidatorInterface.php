<?php
/**
 * @package Core
 */

/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @package Core
 */
interface Nethgui_Core_ValidatorInterface {

    /**
     * Evaluate if $value is accepted by this validator.
     * @param mixed $value
     * @return boolean
     */
    public function evaluate($value);

    /**
     * Failure reason
     * 
     * After an unsuccessful evaluate() call, validator object must return
     * an explanation of the problem.   
     * 
     * @see evaluate()
     * @return array An array of arrays of two elements: a template string and an array of arguments, to invoke strtr().
     */
    public function getFailureInfo();
}