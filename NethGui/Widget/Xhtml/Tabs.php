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
class NethGui_Widget_Xhtml_Tabs extends NethGui_Widget_Xhtml
{

    public function render()
    {                                       
        $content = '';
      
        if ($this->hasChildren()) {
            $content .=$this->openTag('ul', array('class' => 'tabs-list'));

            foreach ($this->getChildren() as $child) {
                $page = $child->getAttribute('name');
                $content .= $this->openTag('li');
                $content .= $this->openTag('a', array('href' => '#' . $this->view->getUniqueId($page)));
                $content .= htmlspecialchars($this->view->translate($page . '_Title'));
                $content .= $this->closeTag('a');
                $content .= $this->closeTag('li');
            }

            $content .=$this->closeTag('ul');
        }
        
        $content .= $this->renderChildren();
       
        return $content;
    }

    public function insert(NethGui_Renderer_WidgetInterface $widget)
    {
        $panel = new NethGui_Widget_Xhtml_Panel($this->view);
        parent::insert($panel);

        $panel
            ->setAttribute('name', $widget->getAttribute('name'))
            ->setAttribute('class', 'tab-panel ' . $this->getAttribute('tabClass'))
            ->insert($widget);
       
        return $this;
    }

}
