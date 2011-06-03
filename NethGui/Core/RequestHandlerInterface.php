<?php
/**
 * @package Core
 */

/**
 * Request handlers executes Module logics.
 * @package Core
 */
interface NethGui_Core_RequestHandlerInterface {

    /**
     * Put the request into the object internal state.
     * @param NethGui_Core_RequestInterface $request
     */
    public function bind(NethGui_Core_RequestInterface $request);

    /**
     * Validate object state. Errors are sent to $report.
     * @return void
     */
    public function validate(NethGui_Core_ValidationReportInterface $report);

    /**
     * Module behaviour implementation.
     *
     * @return void
     */
    public function process();
    
}
