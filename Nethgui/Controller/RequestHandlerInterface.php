<?php
namespace Nethgui\Controller;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface RequestHandlerInterface
{
    /**
     * Authorization required to send a mutation request
     */
    const ACTION_MUTATE = 'MUTATE';

    /**
     * Authorization required to send a query request
     */
    const ACTION_QUERY = 'QUERY';
   

    /**
     *
     * Put the request into the object internal state
     * 
     * @api
     * @param RequestInterface $request
     */
    public function bind(\Nethgui\Controller\RequestInterface $request);

    /**
     * Validate object state
     *
     * Errors must be sent to $report.
     *
     * @api
     * @return void
     */
    public function validate(\Nethgui\Controller\ValidationReportInterface $report);

    /**
     * Behaviour implementation
     *
     * Executed only if no validation errors has occurred.
     *
     * @api
     * @return void
     */
    public function process();


    /**
     * Get the path to the next module. Relative paths resolving is deferred
     * to implementors.
     *
     * Executed after process(), the user agent is directed to the designated
     * module.
     *
     * If boolean FALSE is returned then no action takes place.
     *
     * @api
     * @return mixed boolean FALSE, string path (absolute), array request
     */
    public function nextPath();
}
