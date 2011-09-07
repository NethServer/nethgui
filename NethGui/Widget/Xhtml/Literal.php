<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 */

/**
 * Renders the given literal string, optionally escaping special html characters
 * through PHP htmlspecialchars() function.
 *
 * Attributes:
 * - `data` any string or object with string representation
 * - `hsc` boolean
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 */
class NethGui_Widget_Xhtml_Literal extends NethGui_Widget_Xhtml
{
    public function render()
    {
        $data = strval($this->getAttribute('data', ''));

        if($this->getAttribute('hsc', FALSE) === TRUE) {
            $data = htmlspecialchars($data);
        }

        return $data;
    }
}