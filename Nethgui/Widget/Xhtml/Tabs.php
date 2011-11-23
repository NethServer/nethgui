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
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Tabs extends \Nethgui\Widget\XhtmlWidget
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

    public function insert(\Nethgui\Renderer\WidgetInterface $widget)
    {
        if ($widget instanceof \Panel) {
            $widget->setAttribute('class', $widget->getAttribute('class', '') . ' ' . $this->getAttribute('tabClass', 'TabPanel tab-panel'));
            parent::insert($widget);            
        } else {
            $panel = new Panel($this->view);
            parent::insert($panel);
            $panel
                ->setAttribute('name', $widget->getAttribute('name'))
                ->setAttribute('class', $this->getAttribute('tabClass', 'TabPanel tab-panel'))
                ->insert($widget);
        }

        return $this;
    }

}
