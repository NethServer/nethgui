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
class NethGui_Widget_Xhtml_Text extends NethGui_Widget_Xhtml {
     public function render()
     {
         $name = $this->getAttribute('name');
         $flags = $this->getAttribute('flags');

         return $this->translate($name . '_label', array($name => $this->view[$name]));
     }
}