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
class Dispatcher extends CI_Controller
{

    public function _remap($method, $parameters = array())
    {
        if ($method == 'phpinfo' && ENVIRONMENT == 'development') {
            phpinfo();
            exit;
        }        

        $NFW = new \Nethgui\Framework();
        $NFW->dispatch($method, $parameters);            
    }

}

