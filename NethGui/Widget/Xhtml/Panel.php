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
class NethGui_Widget_Xhtml_Panel extends NethGui_Widget_Xhtml
{

 

    public function render()
    {        
        $content = '';

        $attributes = array(
            'class' => 'panel', 
            'id' => $this->view->getUniqueId('Panel_' . self::getInstanceCounter()),
        );

        $content .= $this->openTag('div', $attributes);
        $content .= $this->renderChildren();
        $content .= $this->closeTag('div');
      
        return $content;
    }

}