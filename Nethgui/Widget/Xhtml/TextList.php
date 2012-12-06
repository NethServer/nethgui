<?php
namespace Nethgui\Widget\Xhtml;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Show an array of strings formatted as a list
 *
 * Attributes
 *
 * - separator  Text inserted between list elements (ex: ", ")
 * - tag  A slash "/" separated list of three tags:
 *    1. widget tag  (default DIV)
 *    2. list tag  (default UL)
 *    3. element tag (default LI)
 * 
 * You can specify the class attribute to set on the list and element tag, by
 * appending .class to the tag name.
 *
 * Example
 *
 *    $textList->setAttribute('tag', 'div/span.wrap/span.el');
 *
 * Prints
 *
 *    <div class="TextList ..."><span class="wrap"><span class="el">...
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class TextList extends \Nethgui\Widget\XhtmlWidget
{

    protected function renderContent()
    {
        $name = $this->getAttribute('name');
        $tags = explode('/', $this->getAttribute('tag', 'div/ul/li')) + array('', '', '');
        $addClass = $this->getAttribute('class', $this->getTagClass($tags[0]));
        $separator = $this->getAttribute('separator');



        $values = $this->getAttribute('value', $this->view[$name]);

        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        } elseif ( ! is_array($values)) {
            $values = array();
        }

        $content = '';

        $listTag = $this->getTagName($tags[1]);
        $listClass = $this->getTagClass($tags[1]);
        $elementTag = $this->getTagName($tags[2]);
        $elementClass = $this->getTagClass($tags[2]);

        $jsonOptions = json_encode(
            array(
                'separator' => $separator,
                'wrap' => array(
                    'listTag' => $listTag,
                    'listClass' => $listClass ? $listClass : NULL,
                    'elementTag' => $elementTag,
                    'elementClass' => $elementClass ? $elementClass : NULL
                )
            )
        );

        // Render elements:
        if (count($values) > 0) {
            $elements = array();

            // Wrap each element with $elementTag
            if ($elementTag) {
                $elWrapBegin = $this->openTag($elementTag, array('class' => $elementClass));
                $elWrapEnd = $this->closeTag($elementTag);
            } else {
                $elWrapBegin = '';
                $elWrapEnd = '';
            }

            foreach ($values as $value) {
                $elements[] = $elWrapBegin . htmlspecialchars($value) . $elWrapEnd;
            }

            $content = implode($separator ? $separator : '', $elements);
        }

        // Wrap the elements with $listTag
        if ($content && $listTag) {
            $content = $this->openTag($listTag, array('class' => $listClass)) .
                $content .
                $this->closeTag($listTag)
            ;
        }



        // Wrap the whole list with $widgetTag
        $widgetTag = $this->getTagName($tags[0]);
        if ($widgetTag) {

            $class = implode(' ', array_filter(array('TextList', $addClass, $this->getClientEventTarget())));

            $content = $this->openTag($widgetTag, array('class' => $class, 'data-options' => $jsonOptions)) .
                $content .
                $this->closeTag($widgetTag)
            ;
        }

        return $content;
    }

    private function getTagName($tagDef)
    {
        $parts = explode('.', $tagDef);
        if (isset($parts[0])) {
            return $parts[0];
        }
        return FALSE;
    }

    private function getTagClass($tagDef, $defaultClass = FALSE)
    {
        $parts = explode('.', $tagDef);
        if (isset($parts[1])) {
            return $parts[1];
        }
        return $defaultClass;
    }

}