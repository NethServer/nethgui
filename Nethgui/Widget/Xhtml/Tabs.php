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
class Nethgui_Widget_Xhtml_Tabs extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $content = '';

        $content .= $this->openTag('div', array('class' => $this->getAttribute('class', 'Tabs')));

        if ($this->hasChildren()) {
            $content .=$this->openTag('ul', array('class' => 'TabList tabList'));

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

        $content .= $this->closeTag('div');

        return $content;
    }

    public function insert(Nethgui_Renderer_WidgetInterface $widget)
    {
        if ($widget instanceof Nethgui_Widget_Xhtml_Panel) {
            $widget->setAttribute('class', $widget->getAttribute('class', '') . ' ' . $this->getAttribute('tabClass', 'TabPanel tab-panel'));
            parent::insert($widget);            
        } else {
            $panel = new Nethgui_Widget_Xhtml_Panel($this->view);
            parent::insert($panel);
            $panel
                ->setAttribute('name', $widget->getAttribute('name'))
                ->setAttribute('class', $this->getAttribute('tabClass', 'TabPanel tab-panel'))
                ->insert($widget);
        }

        return $this;
    }

}
