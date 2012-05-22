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
 * Collect validation errors
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @see \Nethgui\Controller\RequestHandlerInterface::validate()
 * @since 1.0
 * @api
 */
interface ValidationReportInterface
{
    /**
     *
     * @api
     * @param ModuleInterface $module
     * @param string $parameterName
     * @param string $message The error message template
     * @param array $args Optional - Arguments to the error message
     */
    public function addValidationErrorMessage(\Nethgui\Module\ModuleInterface $module, $parameterName, $message, $args = array());

    /**
     *
     * @api
     * @param ModuleInterface $module
     * @param string $parameterName
     * @param ValidatorInterface $validator
     */
    public function addValidationError(\Nethgui\Module\ModuleInterface $module, $parameterName, \Nethgui\System\ValidatorInterface $validator);

    /**
     * Check if a validation error has been added.
     * @api
     * @return boolean
     */
    public function hasValidationErrors();
}


