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
     * Binds NethGui_Core_Request parameters to object internal state.
     * @param NethGui_Core_RequestInterface $request
     */
    public function bind(NethGui_Core_RequestInterface $request);

    /**
     * Validate object state. Errors are sent to $report.
     * @return void
     */
    public function validate(NethGui_Core_ValidationReportInterface $report);

    /**
     * Performs object logics.
     *
     * The return value directs further actions. If it returns an integer, the
     * HTTP status code is forced to that value. If it returns an array, the
     * first value is considered the HTTP status code, and the second
     * an HTTP Location.
     *
     * @return void|int|array
     */
    public function process();
    
}
