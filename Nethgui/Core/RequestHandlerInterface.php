<?php
/**
 * @package Core
 */

namespace Nethgui\Core;

/**
 * Request handlers executes Module logics.
 * 
 * A request handler is delegated to
 * - receive input parameters (parameter binding),
 * - validate,
 * - perform process()-ing.
 *
 * @see ModuleInterface
 * @see http://en.wikipedia.org/wiki/Template_method_pattern
 * @package Core
 */
interface RequestHandlerInterface {

    /**
     * Put the request into the object internal state.
     * @param RequestInterface $request
     */
    public function bind(RequestInterface $request);

    /**
     * Validate object state. Errors are sent to $report.
     * @return void
     */
    public function validate(ValidationReportInterface $report);

    /**
     * Module behaviour implementation. Executed only if no validation errors has occurred.
     *
     * @return void
     */
    public function process();
    
}
