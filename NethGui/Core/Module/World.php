<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * World module.
 *
 * This is the root of the modules composition.
 *
 * @package NethGuiFramework
 */
final class NethGui_Core_Module_World extends NethGui_Core_Module_Composite
{
    /**
     *
     * @var NethGui_Core_ModuleInterface
     */
    private $currentModule;

    public function __construct(NethGui_Core_ModuleInterface $currentModule)
    {
        parent::__construct('');
        $this->currentModule = $currentModule;
    }


    public function process(NethGui_Core_ResponseInterface $response)
    {
        $this->parameters = array(
            'cssMain' => base_url() . 'css/main.css',
            'js' => array(
                'base' => base_url() . 'js/jquery-1.5.1.min.js',
                'ui' => base_url() . 'js/jquery-ui-1.8.10.custom.min.js',
                'test' => base_url() . 'js/test.js',
            ),
            'currentModule' => $response->getInnerResponse($this->currentModule),
        );

        if ($response->getFormat() == NethGui_Core_ResponseInterface::HTML) {
            $response->setViewName('NethGui_Core_View_decoration');
        }
        parent::process($response);
    }

}
