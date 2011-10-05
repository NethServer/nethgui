<?php
/**
 * @package Widget
 * @author Davide Principi <davide.principi@nethesis.it>
 * @ignore
 */

/**
 * Abstract Help Widget class
 * @package Widget
 * @ignore
 */
class Nethgui_Widget_Help extends Nethgui_Widget_Abstract
{
    protected function includeTemplate(Nethgui_Renderer_Abstract $dview, $flags)
    {
        $titleLevel = $this->getAttribute('titleLevel', 1);

        $content = $this->openTag(sprintf('h%d', $titleLevel), array('class' => $dview->getModule()->getIdentifier()));
        $content .= $this->view->translate($dview->getModule()->getTitle());
        $content .= $this->closeTag(sprintf('h%d', $titleLevel));
        $content .= "<p>Module description here..</p>\n";
        $content .= (String) parent::includeTemplate($dview, $flags);
        //$content .= $this->openTag('dl', array('class' => 'FieldDefinitions'));
        //$content .= $this->closeTag('dl');
        return $dview->literal($content);
    }

    public function render()
    {
        $whatToDo = $this->getAttribute('do', 'DONTKNOW');
        $flags = $this->getAttribute('flags', 0);
        $name = $this->getAttribute('name', NULL);
        $data = $this->getAttribute('data', '');
        $value = $this->getAttribute('value', NULL);
        $member = $this->view[$name];

        if ($whatToDo == 'inset' && $member instanceof Nethgui_Core_ViewInterface) {
            $renderer = new Nethgui_Renderer_Help($member);
            return $this->includeTemplate($renderer->setNestingLevel($this->getAttribute('titleLevel') + 1), $flags);
        } elseif ($whatToDo == 'literal') {
            return $data;
        }

        if (in_array($whatToDo, array('textInput', 'button'))) {
            $label = $this->getAttribute('label', $name . '_label');
            $fieldTitle = htmlspecialchars($this->view->translate($label));
        } elseif (in_array($whatToDo, array('checkBox', 'radioButton', 'fieldsetSwitch'))) {
            $label = $this->getAttribute('label', $name . '_' . $value . '_label');
            $fieldTitle = htmlspecialchars($this->view->translate($label));
        }


        $content = '';

        if (isset($fieldTitle)) {
            $content .= $this->openTag('dt', array('class' => $this->view->getUniqueId($name)));
            $content .= $fieldTitle;
            $content .= $this->closeTag('dt');
            $content .= '<dd><p>todo</p></dd>';
        }

        return $content . parent::render();
    }

}
