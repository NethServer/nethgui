<?php
/**
 * @ignore
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Renderer;

/**
 * HttpCommandReceiver
 *
 * Implements the command logic as HTTP redirects
 *
 * @ignore
 */
class HttpCommandReceiver implements \Nethgui\Core\CommandReceiverInterface, \Nethgui\Core\GlobalFunctionConsumer
{

    /**
     *
     * @var \Nethgui\Core\ViewInterface
     */
    private $view;

    /**
     *
     * @var \Nethgui\Core\CommandReceiverInterface
     */
    private $fallbackReceiver;

    /**
     *
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    private $globalFunctionWrapper;

    public function __construct(\Nethgui\Core\ViewInterface $view, \Nethgui\Core\CommandReceiverInterface $fallbackReceiver = NULL)
    {
        $this->view = $view;
        $this->fallbackReceiver = $fallbackReceiver;
        $this->globalFunctionWrapper = new \Nethgui\Core\GlobalFunctionWrapper();
    }

    public function executeCommand($name, $arguments)
    {
        if ( ! method_exists($this, $name) && isset($this->fallbackReceiver)) {
            return $this->fallbackReceiver->executeCommand($name, $arguments);
        }
        return call_user_func_array(array($this, $name), $arguments);
    }

    public function cancel()
    {
        $this->httpRedirection(302, $this->view->getModuleUrl('..'));
    }

    public function activateAction($actionId, $path = NULL, $prevComponent = NULL)
    {
        if (is_null($path)) {
            $path = $actionId;
        }

        $this->httpRedirection(302, $this->view->getModuleUrl($path));
    }

    public function enable()
    {
        $this->httpRedirection(302, $this->view->getModuleUrl());
    }

    public function redirect($url)
    {
        $this->httpRedirection(302, $url);
    }

    /**
     *
     * @param integer $code
     * @param string $location
     */
    private function httpRedirection($code, $location)
    {
        $messages = array(
            '201' => 'Created',
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '303' => 'See Other',
            '307' => 'Temporary Redirect'
        );

        if (isset($messages[strval($code)])) {
            $codeMessage = $messages[strval($code)];
        } else {
            throw new DomainException('Unknown status code for redirection: ' . intval($code));
        }

        if ( ! in_array(parse_url($location, PHP_URL_SCHEME), array('http', 'https'))) {
            $location = NETHGUI_SITEURL . $location;
        }

        $this->globalFunctionWrapper->header(sprintf('HTTP/1.1 %d %s', $code, $codeMessage));
        $this->globalFunctionWrapper->header('Location: ' . $location);

        $ob_status = $this->globalFunctionWrapper->ob_get_status();

        if ( ! empty($ob_status)) {
            $this->globalFunctionWrapper->ob_end_clean();
        }

        $this->globalFunctionWrapper->phpExit(0);
    }

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
    }

}

