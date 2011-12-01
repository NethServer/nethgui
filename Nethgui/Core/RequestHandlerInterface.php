<?php
namespace Nethgui\Core;

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
