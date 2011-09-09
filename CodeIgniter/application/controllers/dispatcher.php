<?php

/**
 * Nethgui
 *
 * @package NethguiFramework
 */

/**
 * TODO: describe class
 *
 * @package NethguiFramework
 * @subpackage CodeIgniter
 */
class Dispatcher extends CI_Controller {

    public function _remap($method, $parameters = array())
    {
        $this->load->helper('url');
        $this->load->helper('form');

        if($method == 'phpinfo' && ENVIRONMENT == 'development') {
            phpinfo();
            exit;
        }

        require_once(APPPATH . '../../Nethgui/Framework.php');

        $NFW = Nethgui_Framework::getInstance($this);
        $NFW->dispatch($method, $parameters);
    }


}

