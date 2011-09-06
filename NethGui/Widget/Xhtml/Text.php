<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 */

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 */
class NethGui_Widget_Xhtml_Text extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $hsc = $this->getAttribute('escapeHtml', TRUE);
        $tag = $this->getAttribute('tag', 'span');
        $value = $this->getAttribute('value', '${0}');
        $cssClass = 'Text ' . $this->getClientEventTarget();

        $text = '';

        if ($flags & NethGui_Renderer_Abstract::STATE_DISABLED) {
            $cssClass .= ' disabled';
        }

        $args = array('${0}' => $this->view->offsetExists($name) ? $this->view[$name] : '${0}');

        if ($this->hasAttribute('args')) {
            $args = array();
            if ( ! is_array($this->getAttribute('args'))) {
                throw new InvalidArgumentException('`args` attribute must be an array!');
            }
            $i = 1;
            foreach ($this->getAttribute('args') as $arg) {
                $args['${' . $i . '}'] = is_null($arg) ? ('${' . $i . '}') : $arg;
                $i ++;
            }
        }

        $text = $this->view->translate($value, $args);

        if ($hsc) {
            $text = htmlspecialchars($text);
        }

        return $this->controlTag($tag, $name, $flags, $cssClass, array('name' => FALSE), $text);
    }

}

