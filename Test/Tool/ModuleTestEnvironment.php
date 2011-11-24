<?php
namespace Test\Tool;
class ModuleTestEnvironment
{

    private $request = NULL;
    private $arguments = NULL;
    private $expectedView = NULL;
    private $viewMode = \Nethgui\Core\ModuleInterface::VIEW_SERVER;
    private $databases = array();
    private $events = array();
    private $shellCommands = array();
    public $fullViewOutput = NULL;

    public function setDatabase($dbName, Test\Tool\MockState $state)
    {
        $this->databases[$dbName] = $state;
        return $this;
    }

    public function getDatabaseNames()
    {
        return array_keys($this->databases);
    }

    /**
     * @param string $name
     * @return Test\Tool\MockState
     */
    public function getDatabase($name)
    {
        return $this->databases[$name];
    }

    public function isSubmitted()
    {
        return ! is_null($this->request);
    }

    public function setRequest($request)
    {
        if (is_array($request)) {
            $this->request = $request;
        } else {
            $this->request = NULL;
        }
        return $this;
    }

    public function getRequest()
    {
        if (is_null($this->request)) {
            return array();
        }

        return $this->request;
    }

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setView($expectedView)
    {
        $this->expectedView = $expectedView;
        return $this;
    }

    public function getView()
    {
        if (is_null($this->expectedView)) {
            return array();
        }

        return $this->expectedView;
    }

    public function setViewMode($viewMode)
    {
        $this->viewMode = $viewMode;
        return $this;
    }

    public function getViewMode()
    {
        return $this->viewMode;
    }

    public function setEvents($events)
    {
        $this->events = $events;
        return $this;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function setCommand($regexp, $output)
    {
        $this->shellCommands[$regexp] = $output;
        return $this;
    }

    public function setCommands($commands)
    {
        $this->shellCommands = $commands;
        return $this;
    }

    public function getCommands()
    {
        return $this->shellCommands;
    }

}
