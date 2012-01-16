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
 * Enanches the abstract renderer with the wiget factory interface.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
class TemplateRenderer extends AbstractRenderer implements \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Module\ModuleAttributesInterface
{

    /**
     *
     * @var callable
     */
    private $templateResolver;

    /**
     * @var \Nethgui\Utility\PhpWrapper
     */
    protected $phpWrapper;

    /**
     *
     * @param \Nethgui\View\ViewInterface $view
     * @param callable $templateResolver
     * @param string $contentType
     * @param string $charset
     */
    public function __construct(\Nethgui\View\ViewInterface $view, $templateResolver, $contentType, $charset)
    {
        if ( ! is_callable($templateResolver)) {
            throw new \InvalidArgumentException(sprintf('%s: $templateResolver must be a valid callback function.', get_class($this)), 1322238847);
        }
        parent::__construct($view);
        $this->templateResolver = $templateResolver;
        $this->phpWrapper = new \Nethgui\Utility\PhpWrapper();
        $this->contentType = $contentType;
        $this->charset = $charset;
    }

    /**
     *
     * @param \Nethgui\View\ViewInterface $view
     * @return \Nethgui\Renderer\Xhtml
     */
    public function spawnRenderer(\Nethgui\View\ViewInterface $view)
    {
        return new self($view, $this->getTemplateResolver(), $this->getContentType(), $this->getCharset());
    }

    protected function getTemplateResolver()
    {
        return $this->templateResolver;
    }

    public function render()
    {
        return $this->renderView($this->getTemplate(), array('view' => $this, 'T' => $this->getTranslateClosure()));
    }

    private function getTranslateClosure()
    {
        $view = $this->view;
        $T = function($string, $args = array()) use ($view) {
                return $view->translate($string, $args);
            };

        return $T;
    }

    /**
     * Renders a view passing $viewState as view parameters.
     *
     * @param string|callable $view Full view name that follows class naming convention or function callback
     * @param array $viewState Array of view parameters.
     * @return string
     */
    protected function renderView($template, $viewState)
    {
        if ($template === FALSE) {
            $viewOutput = '';
        } elseif (is_callable($template)) {
            // Rendered by callback function
            $viewOutput = (string) call_user_func_array($template, $viewState);
        } elseif (is_string($template)) {
            $absoluteViewPath = call_user_func($this->getTemplateResolver(), $template);

            if ( ! $absoluteViewPath) {
                $this->getLog()->warning("Unable to load `{$template}`.");
                return '';
            }

            // Rendered by PHP script
            ob_start();
            $this->phpWrapper->phpInclude($absoluteViewPath, $viewState);
            $viewOutput = ob_get_contents();
            ob_end_clean();
        } else {
            throw new \UnexpectedValueException(sprintf("%s: wrong template type `%s`.", get_class($this), is_object($template) ? get_class($template) : gettype($template)), 1324479415);
        }

        return $viewOutput;
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
    }

    public function getCategory()
    {
        return $this->translate($this->getModule()->getAttributesProvider()->getCategory());
    }

    public function getDescription()
    {
        return $this->translate($this->getModule()->getAttributesProvider()->getDescription());
    }

    public function getLanguageCatalog()
    {
        return $this->getModule()->getAttributesProvider()->getLanguageCatalog();
    }

    public function getMenuPosition()
    {
        return $this->getModule()->getAttributesProvider()->getMenuPosition();
    }

    public function getTags()
    {
        return $this->translate($this->getModule()->getAttributesProvider()->getTags());
    }

    public function getTitle()
    {
        return $this->translate($this->getModule()->getAttributesProvider()->getTitle());
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

}