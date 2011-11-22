<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

/**
 *
 * Attributes:
 * - name
 * - value
 * - flags
 * - label
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui_Widget_Xhtml_Button extends Nethgui_Widget_Xhtml implements Nethgui_Core_CommandReceiverInterface
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $name . '_label');
        $content = '';

        $attributes = array();
        $cssClass = 'Button';

        if ($flags & (Nethgui_Renderer_WidgetFactoryInterface::BUTTON_LINK | Nethgui_Renderer_WidgetFactoryInterface::BUTTON_CANCEL | Nethgui_Renderer_WidgetFactoryInterface::BUTTON_HELP)) {

            $value = $this->getAttribute('value', isset($this->view[$name]) ? $this->view[$name] : NULL);

            if (is_null($value)) {
                if ($flags & Nethgui_Renderer_WidgetFactoryInterface::BUTTON_LINK) {
                    $value = $name;
                } elseif ($flags & Nethgui_Renderer_WidgetFactoryInterface::BUTTON_CANCEL) {
                    $value = '..';
                } elseif ($flags & Nethgui_Renderer_WidgetFactoryInterface::BUTTON_HELP) {
                    $value = Nethgui\array_head($this->view->getModulePath());
                }
            }

            if ($flags & Nethgui_Renderer_WidgetFactoryInterface::BUTTON_CANCEL) {
                $cssClass .= ' cancel';
            } elseif ($flags & Nethgui_Renderer_WidgetFactoryInterface::BUTTON_HELP) {
                $value = '/Help/Read/' . urlencode($value) . '.html#HelpArea';
                $cssClass .= ' Help';
            } else {
                $cssClass .= ' link ' . $this->getClientEventTarget();
            }

            $attributes['href'] = $this->prepareHrefAttribute($value);
            $attributes['class'] = $this->appendReceiverName($cssClass);
            $attributes['title'] = $this->getAttribute('title', FALSE);

            $content .= $this->openTag('a', $attributes);
            $content .= $this->view->translate($label);
            $content .= $this->closeTag('a');
        } else {
            if ($flags & Nethgui_Renderer_WidgetFactoryInterface::BUTTON_SUBMIT) {
                $attributes['type'] = 'submit';
                $cssClass .= ' submit';
                $attributes['id'] = FALSE;
                $attributes['name'] = FALSE;
            } elseif ($flags & Nethgui_Renderer_WidgetFactoryInterface::BUTTON_RESET) {
                $attributes['type'] = 'reset';
                $cssClass .= ' reset';
            } elseif ($flags & Nethgui_Renderer_WidgetFactoryInterface::BUTTON_CUSTOM) {
                $attributes['type'] = 'button';
                $cssClass .= ' custom';
            } elseif ($flags & Nethgui_Renderer_WidgetFactoryInterface::BUTTON_DROPDOWN) {
                $attributes['type'] = 'button';
                $attributes['name'] = FALSE;
                $attributes['id'] = FALSE;
                $cssClass .= ' dropdown';
                $childContent = $this->renderChildren();
            }

            $attributes['value'] = $this->view->translate($label);

            $content .= $this->controlTag('button', $name, $flags, $this->appendReceiverName($cssClass), $attributes);
            if (isset($childContent)) {
                $content .= $childContent;
            }
        }

        return $content;
    }

    private function prepareHrefAttribute($value)
    {
        if (is_array($value)) {
            $value = implode('/', $value);
        }

        if (preg_match('#^https?#', $value)) {
            // Leave skip processing of absolute and fully qualified URLs
            return $value;
        }
        return $this->view->getModuleUrl($value);
    }

    public function executeCommand($name, $arguments)
    {
        switch ($name) {
            case 'setLabel':
                $this->setAttribute('label', $arguments[0]);
                break;
        }
    }

}

