<?php
/**
 * @package Module
 * @subpackage Help
 */

/**
 * @package Module
 * @subpackage Help
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Nethgui_Module_Help_Show extends Nethgui_Module_Help_Common
{

    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        $filePath = $this->getHelpDocumentPath($this->module);

        $content = $this->readHelpDocumentContent($filePath);

        if ($content === FALSE) {
            $view['content'] = 'Error loading help contents for module ' . $this->module->getIdentifier();
            return;
        }

        $view['content'] = $content;
    }


    private function readHelpDocumentContent($filePath)
    {
        $document = new XMLReader();
        if ( ! $document->open('file://' . $filePath)) {
            return FALSE;
        }

        // Advance to BODY tag:
        while ($document->name != 'body' && $document->read());
        while ($document->name != 'div' && $document->read());

        $content = $document->readInnerXml();
        $document->close();

        return $content;
    }
}
