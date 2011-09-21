<?php
/**
 * @package Test
 * @subpackage Tool
 */

/**
 * @package Test
 * @subpackage Tool
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Test_Tool_ModuleTestEnvironment
{

    private $request = NULL;
    private $arguments = NULL;
    private $expectedView = NULL;
    private $viewMode = Nethgui_Core_ModuleInterface::VIEW_SERVER;
    private $databases = array();
    private $events = array();
    private $shellCommands = array();

    public $fullViewOutput = NULL;

    public function setDatabase($dbName, Test_Tool_MockState $state)
    {
        $this->databases[$dbName] = $state;
    }

    public function getDatabaseNames()
    {
        return array_keys($this->databases);
    }

    public function getDatabase($name)
    {
        return $this->databases[$name];
    }

    public function isSubmitted()
    {
        return !is_null($this->request);
    }

    public function setRequest($request)
    {
        if (is_array($request)) {
            $this->request = $request;
        } else {
            $this->request = NULL;
        }
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
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setView($expectedView)
    {
        $this->expectedView = $expectedView;
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
    }

    public function getViewMode()
    {
        return $this->viewMode;
    }

    public function setEvents($events)
    {
        $this->events = $events;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function setCommand($cmd, $output)
    {
        $this->shellCommands[$cmd] = $output;
    }

    public function setCommands($commands)
    {
        $this->shellCommands = $commands;
    }

    public function getCommands()
    {
        return $this->shellCommands;
    }

}