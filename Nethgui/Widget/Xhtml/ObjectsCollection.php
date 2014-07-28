<?php
namespace Nethgui\Widget\Xhtml;

/*
 * Copyright (C) 2013 Nethesis S.r.l.
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
 * Renders a collection applying a given template to each element
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class ObjectsCollection extends \Nethgui\Widget\XhtmlWidget
{

    protected function getJsWidgetTypes()
    {
        return array('Nethgui:objectscollection');
    }

    private function normalizePlaceholders($placeholders)
    {
        $sanitized = array();
        foreach ($placeholders as $p => $f) {
            if (is_numeric($p)) {
                $p = $f;
            }
            $sanitized[$p] = $f;
        }
        return $sanitized;
    }

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $tag = $this->getAttribute('tag', 'div');
        $cssClass = trim('ObjectsCollection ' . $this->getAttribute('class', ''));
        $key = $this->getAttribute('key', FALSE);
        $template = $this->getAttribute('template', FALSE);
        $ifEmpty = $this->getAttribute('ifEmpty', FALSE);
        $placeholders = $this->normalizePlaceholders($this->getAttribute('placeholders', array()));
        $renderer = new ElementRenderer($this->view, $name, '${key}', $template);
        $emptyRenderer = new ElementRenderer($this->view, $name, '${key}', $ifEmpty);

        $content = '';
        $values = $this->view[$name];

        if (empty($values)) {
            $content = $emptyRenderer->render();
        } else {
            foreach ($values as $defaultKey => $data) {
                $placeHolderValues = array();
                foreach ($placeholders as $p => $f) {
                    $placeHolderValues[sprintf('${%s}', $p)] = $data[$f];
                }
                $vR = new ElementRenderer($this->view, $name, $key ? $data[$key] : $defaultKey, $template);
                $content .= strtr((String) $vR->copyFrom($data)->render(), $placeHolderValues);
            }
        }

        return $this->openTag($tag, array(
                'class' => $cssClass . ' ' . $this->getClientEventTarget(),
                'id' => $this->view->getUniqueId($name),
                'data-state' => json_encode(array('rendered' => ! empty($values), 'key' => $key, 'template' => $renderer->render(), 'ifEmpty' => $emptyRenderer->render(), 'placeholders' => $placeholders)),
            )) . $content . $this->closeTag($tag);
    }

}

/**
 * Renders an element by prefixing the given $key to its identifiers
 *
 * @internal Not to be used outside of this context!
 */
class ElementRenderer extends \Nethgui\Renderer\Xhtml
{

    public function copyFrom($data)
    {
        $this->view->copyFrom($data);
        return $this;
    }

    public function __construct(\Nethgui\Renderer\Xhtml $renderer, $name, $key, $template)
    {
        parent::__construct($renderer->view, $renderer->getTemplateResolver(), $renderer->getDefaultFlags());
        // Replace the inner view with a new instance:
        $module = $this->createModule($name, $key, $renderer->getModule()->getAttributesProvider());
        $this->httpResponse = $renderer->httpResponse;
        $this->staticFiles = $renderer->staticFiles;
        $this->view = $renderer->view->spawnView($module)->setTemplate($template);
    }

    public function getClientEventTarget($name)
    {
        return $name;
    }

    private function createModule($name, $id, \Nethgui\Module\ModuleAttributesInterface $ap)
    {
        $n = new ElementModule($name, $ap);
        $m = new ElementModule($id, $ap);
        $n->setParent($this->view->getModule());
        return $m->setParent($n);
    }

}

/**
 * Collaborates with ElementRenderer and ObjectsCollection
 *
 * @internal Not to be used outside of this context!
 */
class ElementModule implements \Nethgui\Module\ModuleInterface
{
    private $identifier, $parent;

    public function __construct($id, \Nethgui\Module\ModuleAttributesInterface $attributesProvider)
    {
        $this->identifier = $id;
        $this->attributesProvider = $attributesProvider;
    }

    public function getAttributesProvider()
    {
        return $this->attributesProvider;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function initialize()
    {
        // NOOP
    }

    public function isInitialized()
    {
        return TRUE;
    }

    public function setParent(\Nethgui\Module\ModuleInterface $parentModule)
    {
        $this->parent = $parentModule;
        return $this;
    }

}