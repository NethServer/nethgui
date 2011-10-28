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
class Nethgui_Module_Help_Read extends Nethgui_Module_Help_Common
{

    public function process()
    {
        if (is_null($this->module)) {
            return;
        }

        $filePath = $this->getHelpDocumentPath($this->module);

        if ( ! $this->globalFunctions->is_readable($filePath)) {
            throw new Nethgui_Exception_HttpStatusClientError('File not found', 404);
        }

        $this->globalFunctions->header("Content-Type: text/html; charset=UTF-8");
        $this->globalFunctions->readfile($filePath);

        exit;
    }

}

