<?php

namespace Nethgui\Module;

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
 * This is the root of the module composition
 *
 * - Enforce basic module authorization
 * - Prepare the decoration view
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Main extends \Nethgui\Controller\ListComposite
{
    /**
     * @var \Nethgui\Module\ModuleSetInterface
     */
    private $moduleSet;
    private $moduleId;

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $attributes = new SystemModuleAttributesProvider();
        $attributes->initializeFromModule($this);
        return $attributes;
    }

    public function __construct(\Nethgui\Module\ModuleSetInterface $modules, $defaultModule = FALSE)
    {
        parent::__construct(FALSE);
        $this->moduleSet = $modules;
        $this->defaultModule = $defaultModule;
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        $idList = array_filter($request->getParameterNames(), function($p) use ($request) {
            return is_array($request->getParameter($p));
        });

        $this->moduleId = \Nethgui\array_head($request->getPath());
        if ( ! $this->moduleId) {
            return;
        }

        try {
            foreach ($idList as $moduleIdentifier) {
                $moduleInstance = $this->moduleSet->getModule($moduleIdentifier);
                $this->addChild($moduleInstance);
            }
        } catch (\Nethgui\Exception\AuthorizationException $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1324379722, $ex);
        }

        $this->authorize($request);

        parent::bind($request);
    }

    private function authorize(\Nethgui\Controller\RequestInterface $request)
    {
        foreach ($this->getChildren() as $child) {
            if ($request->isMutation()) {
                $auth = $this->getPolicyDecisionPoint()->authorize($request->getUser(), $child, self::ACTION_MUTATE);
            } else {
                $auth = $this->getPolicyDecisionPoint()->authorize($request->getUser(), $child, self::ACTION_QUERY);
            }

            if ($auth->isDenied()) {
                throw $auth->asException(1327499272);
            }
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ($this->moduleId) {
            $view['moduleView'] = $view[$this->moduleId];
            unset($view[$this->moduleId]);
        }
    }

    public function nextPath()
    {
        if ( ! $this->moduleId) {
            if ($this->defaultModule !== FALSE) {
                return $this->defaultModule;
            }
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1324379721);
        }
        foreach ($this->getChildren() as $child) {
            if ($child instanceof \Nethgui\Controller\RequestHandlerInterface && $child->getIdentifier() === $this->moduleId) {
                return $child->nextPath();
            }
        }
        return FALSE;
    }

}