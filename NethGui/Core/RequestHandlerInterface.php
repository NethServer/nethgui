<?php

/**
 * Request handlers executes Module logics.
 *
 */
interface NethGui_Core_RequestHandlerInterface {

    /**
     * After initialization an handler must be ready to receive bind(), validate()
     * process() messages.
     */
    public function initialize();

    /**
     * Prevents double initialization.
     * @return bool FALSE, if not yet initialized, TRUE otherwise.
     */
    public function isInitialized();

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
     * Performs object logics, preparing view data.
     * @return string view name
     */
    public function process(NethGui_Core_ResponseInterface $response);
    
}
