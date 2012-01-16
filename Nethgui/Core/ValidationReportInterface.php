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
 * Collect validation errors
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @see \Nethgui\Core\RequestHandlerInterface::validate()
 * @since 1.0
 * @api
 */
interface ValidationReportInterface
{
    /**
     * @param ModuleInterface $module
     * @param string $parameterName
     * @param string The error message template
     * @param array Optional - Arguments to the error message. ${0}, ${1}, ${2}
     */
    public function addValidationErrorMessage(\Nethgui\Module\ModuleInterface $module, $parameterName, $message, $args = array());

    /**
     * @param ModuleInterface $module
     * @param string $parameterName
     * @param ValidatorInterface $validator
     */
    public function addValidationError(\Nethgui\Module\ModuleInterface $module, $parameterName, \Nethgui\System\ValidatorInterface $validator);

    /**
     * Check if a validation error has been added.
     * @return boolean
     */
    public function hasValidationErrors();
}


