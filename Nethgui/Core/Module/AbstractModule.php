<?php
/**
 */

namespace Nethgui\Core\Module;

/**
 */
abstract class AbstractModule implements \Nethgui\Core\ModuleInterface, \Nethgui\Core\LanguageCatalogProvider, \Nethgui\Log\LogConsumerInterface
{

    /**
     * @var string
     */
    private $identifier;

    /**
     *
     * @var ModuleInterface;
     */
    private $parent;
    /*
     * @var bool
     */
    private $initialized = FALSE;

    /**
     * @var \Nethgui\System\PlatformInterface
     */
    private $platform;

    /**
     * Template applied to view, if different from NULL
     *
     * @see \Nethgui\Core\ViewInterface::setTemplate()
     * @var string|callable
     */
    private $viewTemplate;
    private $uiClientCommands = array();

    public function __construct($identifier = NULL)
    {
        $this->viewTemplate = NULL;
        if (isset($identifier)) {
            $this->identifier = $identifier;
        } else {
            $this->identifier = \Nethgui\array_end(explode('\\', get_class($this)));
        }
    }

    public function setPlatform(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return \Nethgui\System\PlatformInterface
     */
    protected function getPlatform()
    {
        return $this->platform;
    }

    /**
     *  Overriding methods can read current state from model.
     */
    public function initialize()
    {
        if ($this->initialized === FALSE) {
            $this->initialized = TRUE;
        } else {
            throw new Exception("Double Module initialization is forbidden.");
        }
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getTitle()
    {
        return $this->getIdentifier() . '_Title';
    }

    public function getDescription()
    {
        return $this->getIdentifier() . '_Description';
    }

    public function setParent(\Nethgui\Core\ModuleInterface $parentModule)
    {
        $this->parent = $parentModule;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        $template = $this->getViewTemplate();
        if ( ! is_null($template)) {
            $view->setTemplate($template);
        }

        foreach ($this->uiClientCommands as $commandArgs) {
            list($methodName, $arguments, $bucketName) = $commandArgs;
            $command = $view->createUiCommand($methodName, $arguments);
            $view->offsetSet($bucketName, $command);
        }
    }

    /**
     *
     * @param string $methodName
     * @param array $arguments
     * @param string|object $receiver
     */
    protected function addUiClientCommand($methodName, $arguments = array(), $bucketName = NULL)
    {
        $this->uiClientCommands[] = array($methodName, $arguments, $bucketName);
    }

    protected function setViewTemplate($template)
    {
        $this->viewTemplate = $template;
    }

    protected function getViewTemplate()
    {
        return $this->viewTemplate;
    }

    /**
     * @param string $languageCode
     * @return string
     */
    public function getLanguageCatalog()
    {
        return strtr(get_class($this), '\\', '_');
    }

    public function getTags()
    {
        return array();
    }

    public function setLog(\Nethgui\Log\AbstractLog $log)
    {
        throw new Exception(sprintf('Cannot invoke setLog() on %s', get_class($this)));
    }

    public function getLog()
    {
        $platform = $this->getPlatform();

        if($platform instanceof \Nethgui\Log\LogConsumerInterface) {
            return $platform->getLog();
        }
        return new \Nethgui\Log\Nullog;
    }

}
