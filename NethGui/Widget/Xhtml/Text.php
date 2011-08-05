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
        $cssClass = 'text ' . $this->getClientEventTarget();

        $text = '';

        if ($flags & NethGui_Renderer_Abstract::STATE_DISABLED) {
            $text = $this->view->translate($value);
            $cssClass .= ' disabled';
        } else {

            $args = array('${0}' => $this->view[$name]);

            if ($this->hasAttribute('args')) {
                $args = array();
                if ( ! is_array($this->getAttribute('args'))) {
                    throw new InvalidArgumentException('`args` attribute must be an array!');
                }
                $i = 1;
                foreach ($this->getAttribute('args') as $arg) {
                    $args['${' . $i . '}'] = $arg;
                    $i ++;
                }
            }

            $text = $this->view->translate($value, $args);
        }


        if ($hsc) {
            $text = htmlspecialchars($text);
        }

        return $this->openTag($tag, array('class' => $cssClass, 'id' => $this->view->getUniqueId($name . '/' . self::getInstanceCounter()))) . $text . $this->closeTag($tag);
    }

}
