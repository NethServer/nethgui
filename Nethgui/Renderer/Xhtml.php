<?php
/**
 * @package Renderer
 * @ignore
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Enanches the abstract renderer with the wiget factory interface
 *
 * Fragments of the view string representation can be generated through the widget objects
 * returned by the factory interface.
 *
 * @package Renderer
 * @ignore
 */
class Nethgui_Renderer_Xhtml extends Nethgui_Renderer_Abstract implements Nethgui_Renderer_WidgetFactoryInterface
{

    /**
     *
     * @var integer
     */
    protected $inheritFlags = 0;

    /**
     *
     * @param Nethgui_Core_ViewInterface $view
     * @param int $inheritFlags Default flags applied to all widgets created by this renderer
     */
    public function __construct(Nethgui_Core_ViewInterface $view, $inheritFlags = 0)
    {
        parent::__construct($view);
        $this->inheritFlags = $inheritFlags & NETHGUI_INHERITABLE_FLAGS;
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

    protected function createWidget($widgetName, $attributes = array())
    {
        $className = 'Nethgui_Widget_Xhtml_' . ucfirst($widgetName);

        $o = new $className($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($aname, $avalue);
        }

        return $o;
    }

    protected function render()
    {
        $module = $this->getModule();
        $languageCatalog = NULL;
        if ($module instanceof Nethgui_Core_LanguageCatalogProvider) {
            $languageCatalog = $module->getLanguageCatalog();
        }
        $state = array('view' => $this);
        return $this->renderView($this->getTemplate(), $state, $languageCatalog);
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
    public function renderView($viewName, $viewState, $languageCatalog = NULL)
    {
        if ($viewName === FALSE) {
            return '';
        }

        if ( ! is_null($languageCatalog) && ! empty($languageCatalog)) {
            if (is_array($languageCatalog)) {
                $languageCatalog = array_reverse($languageCatalog);
            }

            $this->languageCatalogStack[] = $languageCatalog;
        }

        if (is_callable($viewName)) {
            // Callback
            $viewOutput = (string) call_user_func_array($viewName, $viewState);
        } else {
            $viewPath = str_replace('_', '/', $viewName);

            $absoluteViewPath = realpath(NETHGUI_FILE . '../' . $viewPath . '.php');

            if ( ! $absoluteViewPath) {
                $this->logMessage("Unable to load `{$viewName}`.", 'warning');
                return '';
            }

            // PHP script
            $viewOutput = $this->runTemplateScript($viewPath, $viewState, true);
        }

        if ( ! is_null($languageCatalog) && ! empty($languageCatalog)) {
            array_pop($this->languageCatalogStack);
        }

        return $viewOutput;
    }

    private function runTemplateScript($scriptPath, &$vars)
    {
        extract($vars);
        ob_start();
        include($scriptPath);
        return ob_get_flush();
    }

    public function elementList($flags = 0)
    {
        $flags |= $this->inheritFlags;
        $widget = $this->createWidget(__FUNCTION__, array('flags' => $flags));

        if ($flags & self::BUTTONSET) {
            $widget->setAttribute('class', 'Buttonset')
                ->setAttribute('wrap', 'div/');
        }

        // Automatically add standard submit/reset/cancel buttons:
        if ($flags & (self::BUTTON_SUBMIT | self::BUTTON_RESET | self::BUTTON_CANCEL | self::BUTTON_HELP)) {
            if ( ! $widget->hasAttribute('class')) {
                $widget->setAttribute('class', 'Buttonlist')
                    ->setAttribute('wrap', 'div/');
            }

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
        }

        return $widget;
    }

    public function buttonList($flags = 0)
    {
        $flags |= $this->inheritFlags;
        $widget = $this->createWidget("elementList", array('flags' => $flags));

        $widget->setAttribute('class', 'Buttonlist')->setAttribute('wrap', 'div/');

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

    public function dialog($name, $flags = 0)
    {
        $flags |= $this->inheritFlags;

        $className = 'dialog';

        if ($flags & Nethgui_Renderer_WidgetFactoryInterface::DIALOG_SUCCESS) {
            $className .= ' success';
        } elseif ($flags & Nethgui_Renderer_WidgetFactoryInterface::DIALOG_WARNING) {
            $className .= ' warning';
        } elseif ($flags & Nethgui_Renderer_WidgetFactoryInterface::DIALOG_ERROR) {
            $className .= ' error';
        }

        if ($flags & Nethgui_Renderer_WidgetFactoryInterface::DIALOG_MODAL) {
            $className .= ' modal';
        }

        if ($flags & Nethgui_Renderer_WidgetFactoryInterface::STATE_DISABLED) {
            $className .= ' disabled';
        }

        /*
         * Create a panel wrapped around the inset
         */

        $panel = $this->panel($flags)
            ->setAttribute('class', $className)
            ->setAttribute('name', $name);
        $inset = $this->createWidget('inset', array('name' => $name, 'flags' => $flags));

        $panel->insert($inset);

        return $panel;
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

    public function console($name, $flags = 0)
    {
        $flags |= self::STATE_READONLY;
        $flags |= self::LABEL_NONE;
        return $this->textArea($name, $flags)->setAttribute('appendOnly', TRUE)->setAttribute('class', 'console');
    }

    public function dateInput($name, $flags = 0)
    {
        $dateFormat = substr(strtolower(Nethgui_Framework::getInstance()->getDateFormat()), 0, 2);

        if ($dateFormat == 'dd') {
            $encodedFormat = 'le';
        } elseif ($dateFormat == 'mm') {
            $encodedFormat = 'me';
        } else {
            $encodedFormat = 'be';
        }

        return $this->textInput('date')->setAttribute('class', 'Date ' . $encodedFormat);
    }

    public function objectPicker($name = NULL, $flags = 0)
    {
        $flags |= $this->inheritFlags;
        return $this->createWidget(__FUNCTION__, array('name' => $name, 'flags' => $flags, 'icon-before' => 'ui-icon-triangle-1-s'));
    }

}
