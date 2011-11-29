<?php
namespace Nethgui\Module\Help;

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
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Common extends \Nethgui\Core\Module\Standard implements \Nethgui\Core\GlobalFunctionConsumerInterface
{

    /**
     *
     * @var \Nethgui\Core\ModuleInterface
     */
    protected $module;

    /**
     *
     * @var \Nethgui\Core\ModuleSetInterface
     */
    public $moduleSet;

    /**
     *
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    protected $globalFunctions;

    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->globalFunctions = new \Nethgui\Core\GlobalFunctionWrapper();        
    }

    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        parent::bind($request);

        $arguments = $request->getArguments();

        if (empty($arguments) || preg_match('/[a-z][a-z0-9]+(.html)/i', $arguments[0]) == 0) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1322148405);
        }

        // Now assuming a trailing ".html" suffix.
        $this->module = $this->moduleSet->findModule(substr($arguments[0], 0, -5));

        if (is_null($this->module)) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1322148406);
        }
        $this->module->initialize();
        $this->module->bind($request->getParameterAsInnerRequest('', array_slice($arguments, 1)));
    }

    protected function getHelpDocumentPath(\Nethgui\Core\ModuleInterface $module)
    {
        $fileName = strtr(get_class($module), '\\', '_') . '.html';
        $appPath = realpath(NETHGUI_ROOTDIR . '/' . NETHGUI_APPLICATION);
        $lang = $this->getRequest()->getUser()->getLanguageCode();

        return "${appPath}/Help/${lang}/${fileName}";
    }

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctions = $object;
    }

}

?>
