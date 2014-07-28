<?php

namespace Nethgui\Model;

/*
 * Copyright (C) 2014  Nethesis S.r.l.
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
 * This model keeps the request validation state. A request handler module can
 * push a validation error it had found here.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.6
 */
class ValidationErrors extends \ArrayObject implements \Nethgui\Controller\ValidationReportInterface
{

    public function addValidationErrorMessage(\Nethgui\Module\ModuleInterface $module, $parameterName, $message, $args = array())
    {
        $this[] = array(
            'module' => $module,
            'parameter' => $parameterName,
            'message' => $message,
            'args' => $args
        );
        return $this;
    }

    public function addValidationError(\Nethgui\Module\ModuleInterface $module, $parameterName, \Nethgui\System\ValidatorInterface $validator)
    {
        $failureInfoList = $validator->getFailureInfo();
        if (empty($failureInfoList)) {
            throw new \LogicException(sprintf('%s: the validator does not have any failure information', __CLASS__), 1403709455);
        }

        foreach ($failureInfoList as $failureInfo) {
            if ( ! isset($failureInfo[1])) {
                throw new \LogicException(sprintf('%s: invalid failure info struct', __CLASS__), 1403709456);
            }
            $this->addValidationErrorMessage($module, $parameterName, $failureInfo[0], $failureInfo[1]);
        }
        return $this;
    }

    public function hasValidationErrors()
    {
        return count($this) > 0;
    }

}