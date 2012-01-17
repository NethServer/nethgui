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
class Common extends \Nethgui\Module\Standard implements \Nethgui\Utility\PhpConsumerInterface
{

    /**
     *
     * @var \Nethgui\Module\ModuleInterface
     */
    private $module;

    /**
     *
     * @return \Nethgui\Module\ModuleSetInterface $moduleSet
     * @return Menu
     */
    public function getModuleSet()
    {
        return $this->getParent()->getModuleSet();
    }

    /**
     *
     * @var \Nethgui\Utility\PhpWrapper
     */
    protected $globalFunctions;

    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->globalFunctions = new \Nethgui\Utility\PhpWrapper();
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);

        $fileName = \Nethgui\array_head($request->getPath());

        if (preg_match('/[a-z][a-z0-9]+(.html)/i', $fileName) == 0) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1322148405);
        }

        // Now assuming a trailing ".html" suffix.
        $this->module = $this->getModuleSet()->getModule(substr($fileName, 0, -5));

        if (is_null($this->module)) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1322148406);
        }

        $this->module->setPlatform($this->getPlatform());
        if ( ! $this->module->isInitialized()) {
            $this->module->initialize();
        }
    }

    public function setFileNameResolver($fileNameResolver)
    {
        $this->fileNameResolver = $fileNameResolver;
        return $this;
    }

    /**
     * @return \Nethgui\Module\ModuleInterface
     */
    protected function getTargetModule()
    {
        return $this->module;
    }

    protected function getHelpDocumentPath(\Nethgui\Module\ModuleInterface $module)
    {
        $parts = explode('\\', get_class($module));

        $ns = \Nethgui\array_head($parts);
        $lang = $this->getRequest()->getUser()->getLanguageCode();
        $fileName = implode('_', $parts) . '.html';

        return call_user_func($this->fileNameResolver, "${ns}\\Help\\${lang}\\${fileName}");
    }

    protected function getFileNameResolver()
    {
        return $this->fileNameResolver;
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->globalFunctions = $object;
    }

    public function renderFileContent(\Nethgui\Renderer\AbstractRenderer $renderer)
    {
        return $this->globalFunctions->file_get_contents($this->getCachePath($this->fileName));
    }

}

