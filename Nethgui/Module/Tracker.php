<?php

namespace Nethgui\Module;

/*
 * Copyright (C) 2014  Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * TODO: add component description here
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.6
 */
class Tracker extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $attributes = new SystemModuleAttributesProvider();
        $attributes->initializeFromModule($this);
        return $attributes;
    }

    /**
     *
     * @var \Nethgui\Model\SystemTasks
     */
    private $systemTasks;

    /**
     *
     * @var \Nethgui\Model\UserNotifications
     */
    private $notifications;
    private $taskId = FALSE;

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        $taskId = \Nethgui\array_head($request->getPath());
        if ($taskId) {
            $this->bindTask($request, $taskId);
        }

        if ($request->isMutation() && count($this->systemTasks->getRunningTasks()) > 0) {
            throw new \Nethgui\Exception\HttpException('Service Unavailable', 503, 1405692423);
        }

    }

    private function bindTask(\Nethgui\Controller\RequestInterface $request, $taskId)
    {
        try {
            $this->systemTasks->getTaskStatus($taskId);
            $this->taskId = $taskId;
        } catch (\RuntimeException $ex) {
            if ($ex->getCode() === 1405613538) {
                throw new \Nethgui\Exception\HttpException('Not found', 404, 1405612090, $ex);
            } else {
                throw $ex;
            }
        }
    }

    private function applyTaskUiDefaults($ui, $context)
    {
        extract($context, EXTR_REFS);

        return array_replace_recursive(array('conditions' => array(
            'running' => array(
                'dialog' => array(
                    'action' => 'open',
                    'title' => $view->translate('Tracker_title_taskRunning'),
                ),
                'location' => array(
                    'url' => function($data) use ($view) {
                        return $view->getModuleUrl($data['taskInfo']['id']);
                    },
                    'sleep' => 4000,
                    'freeze' => FALSE,
                ),
                'message' => function($data) {
                return trim($data['last']['title'] . "\n" . $data['last']['message']);
            },
                'notification' => FALSE
            ),
            'success' => array(
                'dialog' => array(
                    'action' => 'close',
                    'title' => '',
                ),
                'location' => FALSE,
                'message' => "",
                'notification' => FALSE,
            ),
            'failure' => array(
                'dialog' => array(
                    'action' => 'close',
                    'title' => '',
                ),
                'location' => FALSE,
                'message' => "",
                'notification' => array(
                    'data' => function($data) {
                        return array('failedTasks' => \Nethgui\Module\Tracker::findFailures($data));
                    },
                    'template' => 'trackerError',
                ),
            ),
            )), is_array($ui) ? $ui : array());
    }

    private function evalUiStatus($A, $data)
    {
        array_walk_recursive($A, function(&$v) use ($data) {
            if (is_callable($v)) {
                $v = call_user_func($v, $data);
            }
        });

        if(isset($A['location']['url'])) {
            $url = &$A['location']['url'];
            $url = strtr($url, array('{taskId}' => $this->taskId));
        }

        return $A;
    }

    private function prepareRunningTaskView(\Nethgui\View\ViewInterface $view)
    {
        $data = $this->systemTasks->getTaskStatus($this->taskId);
        $ui = $this->applyTaskUiDefaults(isset($data['ui']) ? $data['ui'] : array(), array('view' => $view, 'notifications' => $this->notifications));
        
        $status = 'running';
        if (isset($data['exit_code'])) {
            $status = $data['exit_code'] ? 'failure' : 'success';
        }

        $data['taskInfo'] = array('id' => $this->taskId);
        $s = $this->evalUiStatus($ui['conditions'][$status], $data);

        if(is_numeric($data['progress'])) {
            $view['progress'] = intval(100 * $data['progress']);
        } else {
            $view['progress'] = '...';
        }

        $view['message'] = $s['message'];
        $view['trackerState'] = array(
            'dialog' => $s['dialog'],
            'location' => $s['location']
        );

        if(is_array($s['notification'])) {
            call_user_func(array($this->notifications, $s['notification']['template']), $s['notification']['data']);
            unset($view['notification']);
        } elseif(is_string($s['notification'])) {
            $this->notifications->notice($s['notification']);
        }
    }

    /**
     * Walk into the task tree dump, and find what has gone wrong.
     *
     * Recursion stops on non-leaf tasks for any of the following conditions:
     * - task has null/zero exit code
     * - task has non-zero exit code AND an error message (reason)
     *
     * @param array $data
     * @return array
     */
    public static function findFailures($data)
    {
        $errors = array();
        $nodes = array($data);

        while ($elem = array_shift($nodes)) {
            if (isset($elem['exit_code']) || intval($elem['code']) != 0) {
                if (count($elem['children']) > 0 && ! $elem['message']) {
                    $nodes = array_merge($nodes, $elem['children']);
                } else {
                    $errors[] = array(
                        'title' => $elem['title'],
                        'message' => $elem['message'],
                        'code' => $elem['code'],
                        'id' => $elem['id']
                    );
                }
            }
        }

        return $errors;
    }

    private function prepareInitializationView(\Nethgui\View\ViewInterface $view)
    {
        $firstRunningTask = \Nethgui\array_head(array_keys($this->systemTasks->getRunningTasks()));
        if ($firstRunningTask) {
            // Notify that the task is running:
            $this->notifications->trackerRunning(array('taskId' => $firstRunningTask));
            $view['trackerState'] = FALSE;
            $view['progress'] = FALSE;
            $view['message'] = '';
            return;
        }

        $firstStartingTask = \Nethgui\array_head(array_keys($this->systemTasks->getStartingTasks()));
        if ($firstStartingTask) {
            $view['progress'] = 0;
            $view['message'] = '...';
            $view['trackerState'] = array(
                'dialog' => array('title' => $view->translate('Tracker_title_taskStarting'), 'action' => 'open'),
                'location' => array('sleep' => 2000, 'url' => $view->getModuleUrl($firstStartingTask))
            );
            $this->notifications->trackerRunning(array('taskId' => $firstStartingTask));
            return;
        }
    }

    public function defineNotificationTemplate($name, $value)
    {
        $this->notifications->defineTemplate($name, $value);
        return $this;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        if( ! $this->getRequest()->getUser()->isAuthenticated()) {
            return;
        }

        if ($this->taskId === FALSE) {
            $this->prepareInitializationView($view);
        } else {
            $this->prepareRunningTaskView($view);
        }
    }

    public function setSystemTasks(\Nethgui\Model\SystemTasks $t)
    {
        $this->systemTasks = $t;
        return $this;
    }

    public function setUserNotifications(\Nethgui\Model\UserNotifications $n)
    {
        $this->notifications = $n;
        return $this;
    }

    public function setModuleSet(\Nethgui\Module\ModuleSetInterface $s)
    {
        $this->moduleSet = $s;
        return $this;
    }

    public function getDependencySetters()
    {
        return array(
            'SystemTasks' => array($this, 'setSystemTasks'),
            'UserNotifications' => array($this, 'setUserNotifications'),
            'ModuleSet' => array($this, 'setModuleSet')
        );
    }

}