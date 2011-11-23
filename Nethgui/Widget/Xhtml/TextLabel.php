<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

namespace Nethgui\Widget\Xhtml;

/**
 * Attributes:
 *
 * - name
 * - flags
 * - escapeHtml
 * - tag
 * - template
 * - args
 * - icon-before
 * - icon-after
 * - class
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class TextLabel extends \Nethgui\Widget\Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags');
        $hsc = $this->getAttribute('escapeHtml', TRUE);
        $tag = $this->getAttribute('tag', 'span');
        $template = $this->getAttribute('template', '${0}');
        $cssClass = 'TextLabel';
        $text = '';

        if ($this->hasAttribute('class')) {
            $cssClass .= ' ' . $this->getAttribute('class');
        }


        if ($flags & \Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED) {
            $cssClass .= ' disabled';
            $stateDisabled = TRUE;
        } else {
            $stateDisabled = FALSE;
        }

        $args = array('${0}' => $this->view->offsetExists($name) && ! $stateDisabled ? $this->view[$name] : '${0}');

        if ($this->hasAttribute('args')) {
            $args = array();
            if ( ! is_array($this->getAttribute('args'))) {
                throw new InvalidArgumentException('`args` attribute must be an array!');
            }
            $i = 1;
            foreach ($this->getAttribute('args') as $arg) {
                $args['${' . $i . '}'] = is_null($arg) || $stateDisabled ? ('${' . $i . '}') : $arg;
                $i ++;
            }
        }

        $text = $this->view->translate($template, $args);

        if ($hsc) {
            $text = htmlspecialchars($text);
        }

        if ($this->hasAttribute('icon-before')) {
            $text = $this->openTag('span', array('class' => 'ui-icon ' . $this->getAttribute('icon-before'))) . $this->closeTag('span') . $text;
        }

        if ($this->hasAttribute('icon-after')) {
            $text .= $this->openTag('span', array('class' => 'ui-icon ' . $this->getAttribute('icon-before'))) . $this->closeTag('span');
        }

        if ($this->hasAttribute('name')) {
            $content = $this->controlTag($tag, $name, $flags, $cssClass, array('name' => FALSE, 'id' => FALSE), $text);
        } else {
            $content = $this->openTag($tag, array('class' => $cssClass));
            $content .= $text;
            $content .= $this->closeTag($tag);
        }

        return $content;
    }

}

