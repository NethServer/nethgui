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

        if ($this->hasAttribute('value')) {
            $value = $this->getAttribute('value');
        } else {
            $value = $name . '_text';
        }

        if ($this->hasAttribute('args')) {
            $args = array();
            if ( ! is_array($this->getAttribute('args'))) {
                throw new InvalidArgumentException('`args` attribute must be an array!');
            }
            $i = 0;
            foreach ($this->getAttribute('args') as $arg) {
                $args['${' . $i . '}'] = $arg;
                $i ++;
            }
        } else {
            $args = array('${0}' => $this->view[$name]);
        }

        return htmlspecialchars($this->translate($value, $args));
    }

}