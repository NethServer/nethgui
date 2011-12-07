<?php
namespace Nethgui\Renderer;

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
 * Renders through a template script or callback method
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class TemplateRenderer extends AbstractRenderer implements \Nethgui\Core\GlobalFunctionConsumerInterface
{

    /**
     *
     * @var callable
     */
    private $templateResolver;

    /**
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    protected $globalFunctionWrapper;

    public function __construct(\Nethgui\Core\ViewInterface $view, $templateResolver)
    {
        if ( ! is_callable($templateResolver)) {
            throw new \InvalidArgumentException(sprintf('%s: $templateResolver must be a valid callback function.', get_class($this)), 1322238847);
        }
        parent::__construct($view);
        $this->templateResolver = $templateResolver;
        $this->globalFunctionWrapper = new \Nethgui\Core\GlobalFunctionWrapper();
    }

    public function getTemplateResolver()
    {
        return $this->templateResolver;
    }

    protected function render()
    {
        return $this->renderView($this->getTemplate(), array('view' => $this));
    }

    /**
     * Renders a view passing $viewState as view parameters.
     *
     * If specified, this function sets the default language catalog used
     * by T() translation function.
     *
     * @param string|callable $view Full view name that follows class naming convention or function callback
     * @param array $viewState Array of view parameters.
     * @param string|array $languageCatalog Name of language strings catalog.
     * @return string
     */
    private function renderView($viewName, $viewState)
    {
        if ($viewName === FALSE) {
            return '';
        }

        if (is_callable($viewName)) {
            // Rendered by callback function
            $viewOutput = (string) call_user_func_array($viewName, $viewState);
        } else {
            $absoluteViewPath = call_user_func($this->getTemplateResolver(), $viewName);

            if ( ! $absoluteViewPath) {
                $this->getLog()->warning("Unable to load `{$viewName}`.");
                return '';
            }

            // Rendered by PHP script
            ob_start();
            $this->globalFunctionWrapper->phpInclude($absoluteViewPath, $viewState);
            $viewOutput = ob_get_contents();
            ob_end_clean();
        }

        return $viewOutput;
    }

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
    }

}