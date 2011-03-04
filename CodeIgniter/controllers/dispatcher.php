<?php

/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * TODO: describe class
 *
 * @package NethGuiFramework
 * @subpackage CodeIgniter
 */
final class Dispatcher extends CI_Controller {

    public function _remap($method, $parameters = array())
    {
        if ($method == 'phpinfo')
        {
            phpinfo();
            return;
        }

        $this->load->helper('url');
        $this->load->helper('form');

        require_once(APPPATH . '../NethGui/Dispatcher.php');

        $nethgui = new NethGui_Dispatcher($this);
        $nethgui->main($method, $parameters);
    }

}

