<?php
/**
 * @package Module
 * @subpackage Help
 */

namespace Nethgui\Module\Help;

/**
 * @package Module
 * @subpackage Help
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Show extends Common
{

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if (is_null($this->module)) {
            return;
        }

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
        if ( $document->open('file://' . $filePath, 'utf-8', LIBXML_NOENT) !== TRUE) {
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
