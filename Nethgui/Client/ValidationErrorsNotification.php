<?php
namespace Nethgui\Client;

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
 * Keep track of validation errors from modules
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class ValidationErrorsNotification extends AbstractNotification implements \Nethgui\Core\ValidationReportInterface
{

    /**
     * Validation error counter
     *
     * @var integer
     */
    private $errors;

    /**
     *
     * @var \Nethgui\Core\TranslatorInterface
     */
    private $translator;

    public function __construct(\Nethgui\Core\TranslatorInterface $translator)
    {
        $this->errors = array();
        $this->translator = $translator;
        parent::__construct(parent::NOTIFY_ERROR, 'ValidationErrors', TRUE);
    }

    public function addValidationErrorMessage(\Nethgui\Core\ModuleInterface $module, $parameterName, $message, $args = array())
    {
        $this->errors[] = array(
            $module,
            $parameterName,
            $this->translator->translate($module, $message, $args),
            self::NOTIFY_ERROR
        );
    }

    public function addValidationError(\Nethgui\Core\ModuleInterface $module, $parameterName, \Nethgui\Core\ValidatorInterface $validator)
    {
        foreach ($validator->getFailureInfo() as $failureInfo) {
            if ( ! isset($failureInfo[1])) {
                $failureInfo[1] = array();
            }
            $this->addValidationErrorMessage($module, $parameterName, $failureInfo[0], $failureInfo[1]);
        }
    }

    public function serialize()
    {
        $p = parent::serialize();
        return serialize(array($p, $this->errors));
    }

    public function unserialize($serialized)
    {
        list($p, $this->errors) = unserialize($serialized);
        parent::unserialize($p);
    }

    public function hasValidationErrors()
    {
        return count($this->errors) > 0;
    }

    public function asArray()
    {
        return array(
            'p' => parent::asArray(),
            'e' => $this->errors
        );
    }

}

