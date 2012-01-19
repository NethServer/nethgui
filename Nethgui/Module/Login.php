<?php
namespace Nethgui\Module;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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

use Nethgui\System\PlatformInterface as Valid;

/**
 * Perform user authentication through the PAM module
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Login extends \Nethgui\Controller\AbstractController
{

    public function initialize()
    {
        parent::initialize();

        if ( ! $this->getPhpWrapper()->extension_loaded('pam')) {
            throw new \RuntimeException(sprintf('%s: the PAM PHP extension is not loaded', __CLASS__), 1326879560);
        }

        $this->declareParameter('username', Valid::NOTEMPTY);
        $this->declareParameter('password', Valid::NOTEMPTY);
        $this->declareParameter('hostname', FALSE, array('configuration', 'SystemName'));
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);

        $error = '';

        if ( ! $report->hasValidationErrors() && $this->getRequest()->isSubmitted()) {
            $authorized = $this->getPhpWrapper()->pam_auth($this->parameters['username'], $this->parameters['password'], $error, FALSE);
            if ( ! $authorized) {
                $report->addValidationErrorMessage($this, 'password', 'Permission denied');
            }
        }
    }

    public function process()
    {
        $request = $this->getRequest();
        if ($request->isSubmitted()) {
            $request->getUser()->setAuthenticated(TRUE);
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view->setTemplate('Nethgui\Template\Login');
        $view->getCommandList('/Main')
            ->setDecoratorParameter('disableHeader', TRUE)
            ->setDecoratorParameter('disableMenu', TRUE)
//            ->setDecoratorParameter('disableFooter', TRUE)
        ;
    }

    public function nextPath()
    {
        return '/';
    }

}