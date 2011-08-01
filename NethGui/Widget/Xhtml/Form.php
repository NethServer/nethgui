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
class NethGui_Widget_Xhtml_Form extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getParameter('name');
        $value = $this->getParameter('value');
        $flags = $this->getParameter('flags');
        $action = $this->getParameter('action');
        $content = '';

        $content .= $this->openTag('form', array('method' => 'post', 'action' => $form->buildUrl($action)));
        $content .= $this->openTag('div', array('id' => $this->view->getUniqueId($name ? $name : 'Form')));
        $content .= $this->renderChildren();
        $content .= $this->closeTag('div');
        $content .= $this->closeTag('form');

        return $form;
    }

    /**
     *
     * @param array|string $_ Arguments for URL
     * @return string the URL
     */
    public function buildUrl()
    {
        $parameters = array();
        $path = $this->view->getModulePath();

        foreach (func_get_args() as $arg) {
            if (is_array($arg)) {
                $parameters = array_merge($parameters, $arg);
            } else {
                $path[] = strval($arg);
            }
        }

        return NethGui_Framework::getInstance()->buildUrl($path, $parameters);
    }

}