<?php
/**
 * @package Core
 */

/**
 * Request handlers executes Module logics.
 * 
 * A request handler is delegated to
 * - receive input parameters (parameter binding),
 * - validate,
 * - perform process()-ing.
 *
 * @see Nethgui\Core\ModuleInterface
 * @see http://en.wikipedia.org/wiki/Template_method_pattern
 * @package Core
 */
interface Nethgui\Core\RequestHandlerInterface {

    /**
     * Put the request into the object internal state.
     * @param Nethgui\Core\RequestInterface $request
     */
    public function bind(Nethgui\Core\RequestInterface $request);

    /**
     * Validate object state. Errors are sent to $report.
     * @return void
     */
    public function validate(Nethgui\Core\ValidationReportInterface $report);

    /**
     * Module behaviour implementation. Executed only if no validation errors has occurred.
     *
     * @return void
     */
    public function process();
    
}
