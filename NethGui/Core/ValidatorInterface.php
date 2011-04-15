<?php
/**
 * @package Core
 */

/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @package Core
 */
interface NethGui_Core_ValidatorInterface {

    /**
     * Evaluate if $value is accepted by this validator.
     * @param mixed $value
     * @return boolean
     */
    public function evaluate($value);

    /**
     * After an unsuccessful evaluate() call, validator object must return
     * a string message explaining failure reason.
     * 
     * @see evaluate()
     * @param string Optional - Evaluated parameter name
     * @return string
     */
    public function getMessage();
}