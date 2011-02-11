<?php

class Dispatcher extends CI_Controller {

    /**
     * Load all `*Interface.php` files under `APPPATH/libraries/interface/` directory.
     */
    private function loadInterfaces()
    {
        $directoryIterator = new DirectoryIterator(APPPATH . 'libraries/interface');
        foreach ($directoryIterator as $element)
        {
            if (substr($element->getFilename(), -13) == 'Interface.php')
            {
                require_once($element->getPathname());
            }
        }
    }

    public function _remap($method, $parameters = array())
    {
        if ($method == 'phpinfo')
        {
            phpinfo();
            return;
        }

        $this->loadInterfaces();

        $this->load->model("local_system_configuration", "systemConfiguration");
        $this->load->model("module_loader", "moduleLoader");
        $this->load->helper('url');        

        $decoration_parameters = array(
            'css_main' => base_url() . 'css/main.css',
            'module_menu' => 'module_menu',
            'module_content' => 'module_content',
            'breadcrumb_menu' => htmlspecialchars('Bread > Crumb > Menu'),
        );

        $this->load->view('decoration.php', $decoration_parameters);
    }

}
?>
