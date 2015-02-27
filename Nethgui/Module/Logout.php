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

/**
 * Logs out the currently authenticated user
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Logout extends \Nethgui\Controller\AbstractController implements \Nethgui\Utility\SessionConsumerInterface
{
    /**
     *
     * @var \Nethgui\Utility\SessionInterface
     */
    private $session;

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $attributes = new SystemModuleAttributesProvider();
        $attributes->initializeFromModule($this);
        return $attributes;
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('action', '/^logout$/');
        $this->declareParameter('nextPath', '/.*/');
    }

    public function process()
    {
        $request = $this->getRequest();
        if ($request->isMutation() && $this->parameters['action'] === 'logout') {
            $this->getLog()->notice(sprintf("%s: user %s logged out", __CLASS__, $request->getUser()->getCredential('username')));
            $this->session->logout();            
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['nextPath'] = '/' . implode('/', $this->getRequest()->getOriginalPath());
    }

    public function nextPath()
    {
        return $this->parameters['nextPath'] ? $this->parameters['nextPath'] : FALSE;
    }

    public function setSession(\Nethgui\Utility\SessionInterface $session)
    {
        $this->session = $session;
        return $this;
    }

}