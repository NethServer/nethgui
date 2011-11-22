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
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui_Widget_Xhtml_Hidden extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value', $this->view[$name]);
        $flags = $this->getAttribute('flags');
        $content = '';

        if ( ! is_array($value)) {
            $value = array($name => $value);
        }

        $content .= $this->hiddenArrayRecursive($value, $flags);

        return $content;
    }

    private function hiddenArrayRecursive($valueArray, $flags, $path = array())
    {
        $content = '';

        foreach ($valueArray as $name => $value) {
            $namePath = $path;
            $namePath[] = $name;

            if (is_array($value)) {
                $content .= $this->hiddenArrayRecursive($value, $flags, $namePath);
            } else {
                $attributes = array(
                    'type' => 'hidden',
                    'value' => $value,
                    'name' => $this->getControlName(implode('/', $namePath)),
                    'id' => FALSE
                );

                if($this->hasAttribute('class')) {
                    $attributes['class'] = $this->getAttribute('class');
                }

                $content .= $this->controlTag('input', FALSE, $flags, 'Hidden', $attributes);
            }
        }

        return $content;
    }

}