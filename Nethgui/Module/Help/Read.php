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
        header("Content-Type: text/html; charset=UTF-8");
        $filePath = $this->getHelpDocumentPath($this->module);
        readfile($filePath);
        exit;
    }

}

