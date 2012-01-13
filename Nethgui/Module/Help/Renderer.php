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
 *  @author Davide Principi <davide.principi@nethesis.it>
 */
class Renderer extends \Nethgui\Renderer\Xhtml
{

    public $nestingLevel = 1;
    private $describeWidgets = array();

    protected function createWidget($widgetName, $attributes = array())
    {
        $o = new Widget($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($aname, $avalue);
        }

        $o->setAttribute('do', $widgetName);

        if (in_array($widgetName, array('textInput', 'button', 'checkBox', 'radioButton', 'fieldsetSwitch'))) {
            $this->describeWidgets[] = $o;
        }

        return $o;
    }

    public function spawnRenderer(\Nethgui\View\ViewInterface $view)
    {
        return new self($view, $this->getTemplateResolver(), $this->getDefaultFlags());
    }

    public function render()
    {
        $fields = array();

        $content = parent::render();

        foreach ($this->describeWidgets as $widget) {
            if ( ! $widget->hasAttribute('do')) {
                continue;
            }

            $name = $widget->getAttribute('name');
            $value = $widget->getAttribute('value');
            $whatToDo = $widget->getAttribute('do');

            if (in_array($whatToDo, array('textInput', 'button'))) {
                $label = $widget->getAttribute('label', $this->translate($name . '_label'));
            } elseif (in_array($whatToDo, array('checkBox', 'radioButton', 'fieldsetSwitch'))) {
                $label = $widget->getAttribute('label', $this->translate($name . '_' . $value . '_label'));
            }

            $id = $this->view->getUniqueId($name);

            $fields[] = array(
                'name' => $name,
                'id' => $id,
                'helpId' => $widget->getAttribute('helpId', $name),
                'label' => $label,
            );
        }

        $view = $this->view->spawnView($this->view->getModule());
        $view->setTemplate('Nethgui\Template\Help\Section');
        $view['title'] = $this->getTitle();
        $view['description'] = $this->getDescription();
        $view['fields'] = $fields;
        $view['titleLevel'] = $this->nestingLevel;
        $headingRenderer = parent::spawnRenderer($view)->render();

        return (String) $headingRenderer . $content;
    }

}
