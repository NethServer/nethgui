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
        $name = $this->getParameter('name');
        $value = $this->getParameter('value');
        $flags = $this->getParameter('flags');
        $content = '';

        $content .= $this->openTag('div', array('class' => 'tabs', 'id' => $this->view->getUniqueId($name)));

        if ($this->hasChildren()) {
            $content .=$this->openTag('ul', array('class' => 'tabs-list'));

            foreach ($this->getChildren() as $child) {
                $page = $child->getParameter('name');
                $content .= $this->openTag('li');
                $content .= $this->openTag('a', array('href' => '#' . $tabs->getUniqueId($page)));
                $content .= htmlspecialchars($this->translate($page . '_Title'));
                $content .= $this->closeTag('a');
                $content .= $this->closeTag('li');
            }

            $content .=$this->closeTag('ul');
        }


        $content .= $this->renderChildren();

        $content .= $this->closeTag('div');

        return $content;
    }

}
