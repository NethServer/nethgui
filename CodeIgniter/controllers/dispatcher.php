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
        $this->load->helper('url');
        $this->load->helper('form');

        require_once(APPPATH . '../NethGui/Framework.php');

        $NFW = NethGui_Framework::getInstance($this);
        $NFW->getDispatcher()->dispatch($method, $parameters);
    }

}

