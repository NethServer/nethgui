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
 * Enanches the abstract renderer with the wiget factory interface
 *
 * Fragments of the view string representation can be generated through the widget objects
 * returned by the factory interface.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
class Xhtml extends \Nethgui\Renderer\TemplateRenderer implements \Nethgui\Renderer\WidgetFactoryInterface
{
    /**
     *
     * @var integer
     */
    private $inheritFlags = 0;
    private $requireFlags = 0;
    private $rejectFlags = 0;

    /**
     *
     * @var \Nethgui\Module\Resource;
     */
    private $resource;

    public function __construct(\Nethgui\View\ViewInterface $view, $templateResolver, $inheritFlags)
    {
        parent::__construct($view, $templateResolver, 'text/html', 'UTF-8');
        $this->inheritFlags = $inheritFlags & NETHGUI_INHERITABLE_FLAGS;
    }

    /**
     * 
     * @api
     * @param \Nethgui\View\ViewInterface $view
     * @return \Nethgui\Renderer\Xhtml
     */
    public function spawnRenderer(\Nethgui\View\ViewInterface $view)
    {
        $renderer = new self($view, $this->getTemplateResolver(), $this->getDefaultFlags());
        $renderer->setResourceModule($this->resource);
        return $renderer;
    }

    protected function createWidget($widgetType, $attributes = array())
    {
        $className = 'Nethgui\Widget\Xhtml\\' . ucfirst($widgetType);

        $o = new $className($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($aname, $avalue);
        }

        return $o;
    }

    public function setResourceModule(\Nethgui\Module\Resource $resource) {
        if($this->resource !== NULL) {
            $this->getLog()->warning("Resource handler already set. Expected NULL.");
        }
        $this->resource = $resource;
        return $this;
    }

    /**
     * Append a Javascript code fragment to the global .js temporary file
     * 
     * @api
     * @param string $jsCode Raw Javascript code
     * @return Xhtml
     */
    public function includeJavascript($jsCode)
    {
        if($this->resource === NULL) {
            $this->getLog()->warning("NULL Resource handler");
            return;
        }
        $this->resource->appendCode($jsCode, 'js');
        return $this;
    }

    /**
     * Append a CSS code fragment to the global .css temporary file
     *
     * @api
     * @param string $cssCode Raw Css code
     * @return Xhtml
     */
    public function includeCss($cssCode)
    {
        if($this->resource === NULL) {
            $this->getLog()->warning("NULL Resource handler");
            return;
        }
        $this->resource->appendCode($cssCode, 'css');
        return $this;
    }

    /**
     * Append the given file to a global temporary file with the same extension.
     *
     * The file path is relative to the <namespace>/Resource/ directory. The
     * <namespace> is assumed to be the same of the module.
     * 
     * @api
     * @param string $fileName
     * @return Xhtml
     */
    public function includeFile($fileName)
    {
        if($this->resource === NULL) {
            $this->getLog()->warning("NULL Resource handler");
            return;
        }
        $filePath = call_user_func($this->getTemplateResolver(), $fileName);
        $this->resource->includeFile($filePath);
        return $this;
    }

    /**
     * Transfer the given translation strings to the javascript environment.
     *
     * @see $.Nethgui.Translator in jquery.nethgui.base.js
     * 
     * @param array|Traversable $keys
     * @return Xhtml
     */
    public function includeTranslations($keys)
    {
        $out = array();

        foreach ($keys as $key) {
            $out[$key] = $this->view->translate($key);
        }

        $this->includeJavascript('
// Translations from ' . $this->view->getClientEventTarget('') . '
(function ($) {
 $.Nethgui.Translator.extendCatalog(' . json_encode($out) . ');
} ( jQuery ));
');

        return $this;
    }

    /**
     * $flag bits are ORed on the widget that include this view.
     * 
     * @api
     * @param integer $flags
     * @return \Nethgui\Renderer\Xhtml 
     */
    public function requireFlag($flags)
    {
        $this->requireFlags |= $flags;
        return $this;
    }

    /**
     * $flag bits are masked on the widget that include this view.
     * 
     * @see requireFlag()
     * @api
     * @param integer $flags
     * @return \Nethgui\Renderer\Xhtml 
     */
    public function rejectFlag($flags)
    {
        $this->rejectFlags |= $flags;
        return $this;
    }

    /**
     * Calculate flags for view inclusion
     * @see \Nethgui\Widget\Xhtml\Inset
     * @param integer $widgetFlags
     */
    public function calculateIncludeFlags($widgetFlags)
    {
        return ($widgetFlags | $this->requireFlags) & (~ $this->rejectFlags);
    }

    /**
     * Link an external file into the final XHTML document
     *
     * The XHTML tag generated depends on the file extension. Javascript (.js)
     * needs the SCRIPT tag, External stylesheets (.css) a LINK tag.
     * 
     * @api
     * @param string $fileName
     * @return Xhtml
     */
    public function useFile($fileName)
    {
        $this->resource->useFile($this->getPathUrl() . $fileName);
        return $this;
    }

    public function getDefaultFlags()
    {
        return $this->inheritFlags;
    }

    public function setDefaultFlags($flags)
    {
        $this->inheritFlags = $flags;
        return $this;
    }

    public function elementList($flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function buttonList($flags = 0)
    {
        $flags |= $this->inheritFlags;
        $widget = $this->createWidget("elementList", array('flags' => $flags));

        if ($flags & self::BUTTONSET) {
            $widget->setAttribute('class', 'Buttonset')
                ->setAttribute('wrap', 'div/. ');
        } else {
            $widget->setAttribute('class', 'Buttonlist')->setAttribute('wrap', 'div/. ');
        }

        // Automatically add standard submit/reset/cancel buttons:
        if ($flags & self::BUTTON_SUBMIT) {
            $widget->insert($this->button('Submit', self::BUTTON_SUBMIT));
        }
        if ($flags & self::BUTTON_RESET) {
            $widget->insert($this->button('Reset', self::BUTTON_RESET));
        }
        if ($flags & self::BUTTON_CANCEL) {
            $widget->insert($this->button('Cancel', self::BUTTON_CANCEL));
        }
        if ($flags & self::BUTTON_HELP) {
            $widget->insert($this->button('Help', self::BUTTON_HELP));
        }

        return $widget;
    }

    public function button($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function checkBox($name, $value, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'value' => $value, 'flags' => $flags));
    }

    public function fieldsetSwitch($name, $value, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'value' => $value, 'flags' => $flags));
    }

    public function form($flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function hidden($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function inset($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function panel($flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function radioButton($name, $value, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'value' => $value, 'flags' => $flags));
    }

    public function selector($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags, 'icon-before' => 'ui-icon-triangle-1-s'));
    }

    public function tabs($flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('flags' => $flags));
    }

    public function textInput($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function textLabel($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function fieldset($name = NULL, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        $widget = $this->createWidget(__FUNCTION__, array('flags' => $flags, 'icon-before' => 'ui-icon-triangle-1-s'));
        if ( ! is_null($name)) {
            $widget->setAttribute('name', $name);
        }
        return $widget;
    }

    public function header($name = NULL, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        $widget = $this->createWidget('textLabel', array('flags' => $flags, 'class' => 'header ui-widget-header ui-corner-all ui-helper-clearfix', 'tag' => 'h2'));
        if ( ! is_null($name)) {
            $widget->setAttribute('name', $name);
        }
        return $widget;
    }

    public function literal($data, $flags = 0)
    {
        return $this->createWidget(__FUNCTION__, array('data' => $data, 'flags' => $flags));
    }

    public function columns()
    {
        return $this->createWidget(__FUNCTION__, array());
    }

    public function progressBar($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function textArea($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function collectionEditor($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function console($name, $flags = 0)
    {
        $flags |= self::STATE_READONLY;
        $flags |= self::LABEL_NONE;
        return $this->textArea($name, $flags)->setAttribute('appendOnly', TRUE)->setAttribute('class', 'console');
    }

    public function dateInput($name, $flags = 0)
    {
        /*
         * Set to "be" (Big Endian) date format. Supported also "le" and "me".
         * see http://en.wikipedia.org/wiki/Calendar_date
         */
        return $this->textInput('date')->setAttribute('class', 'Date be');
    }

    public function objectPicker($name = NULL, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags, 'icon-before' => 'ui-icon-triangle-1-s'));
    }

    public function slider($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function textList($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

    public function objectsCollection($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags));
    }

}
