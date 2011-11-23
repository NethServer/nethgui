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
class Nethgui\Widget\Xhtml_ElementList extends Nethgui\Widget\Xhtml
{

    private $childWrapTag;

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $cssClass = $this->getAttribute('class', 'ElementList');
        $wrap = explode('/', $this->getAttribute('wrap', 'ul/li')) + array('div', 'div');

        $this->childWrapTag = $wrap[1];

        //if ($flags & Nethgui\Renderer\WidgetFactoryInterface::STATE_DISABLED) {
        //    $cssClass .= ' disabled';
        //}

        if ($this->hasAttribute('maxElements')) {
            $maxElements = intval($this->getAttribute('maxElements'));
            if ($maxElements > 0) {
                $cssClass .= ' v' . $maxElements;
            }
        }

        $content = $this->renderChildren();

        if ($content && $wrap[0]) {
            $content = $this->openTag($wrap[0], array('class' => $cssClass))
                . $content
                . $this->closeTag($wrap[0]);
        }

        return $content;
    }

    protected function wrapChild($childOutput)
    {
        if ( ! $this->childWrapTag) {
            return parent::wrapChild($childOutput);
        }

        $childTag = explode('.', $this->childWrapTag) + array(FALSE, FALSE);

        $content = '';
        $content .= $this->openTag($childTag[0]);
        $content .= parent::wrapChild($childOutput);
        $content .= $this->closeTag($childTag[0]);

        return $content;
    }

}

