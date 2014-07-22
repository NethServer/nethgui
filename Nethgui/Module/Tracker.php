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

    private function prepareRunningTaskView(\Nethgui\View\ViewInterface $view)
    {
        $data = $this->systemTasks->getTaskStatus($this->taskId);
        if (isset($data['exit_code'])) {
            $view['progress'] = intval(100 * $data['progress']['progress']);
            $view['message'] = $view->translate('Tracker_title_taskCompleted');
            $view['dialog'] = array('title' => $view->translate('Tracker_title_taskFinished'), 'action' => 'close');
            if ($data['exit_code'] !== 0) {
                $this->notifications->trackerError(array('failedTasks' => $this->findFailedSubtasks($data)));
            }
        } else {
            $view['progress'] = intval(100 * $data['progress']);
            $view['message'] = trim($data['last']['title'] . "\n" . $data['last']['message']);
            $view['dialog'] = array('title' => $view->translate('Tracker_title_taskRunning'), 'action' => 'open', 'sleep' => 4000, 'nextPath' => $view->getModuleUrl($this->taskId));
        }
    }

    private function findFailedSubtasks($data)
    {
        $errors = array();
        $nodes = $data['tasks'];

        while ($elem = array_shift($nodes)) {
            if ($elem['code'] !== NULL && $elem['code'] !== 0) {
                if (count($elem['children']) > 0) {
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
            $view['dialog'] = FALSE;
            $view['progress'] = FALSE;
            $view['message'] = '';
            return;
        }

        $firstStartingTask = \Nethgui\array_head(array_keys($this->systemTasks->getStartingTasks()));
        if ($firstStartingTask) {
            $view['progress'] = 0;
            $view['message'] = '...';
            $view['dialog'] = array('title' => $view->translate('Tracker_title_taskStarting'), 'action' => 'open', 'sleep' => 2000, 'nextPath' => $view->getModuleUrl($firstStartingTask));
            $this->notifications->trackerRunning(array('taskId' => $firstStartingTask));
            return;
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        // Define a notification template that opens the first running task details:
        $this->notifications->defineTemplate('trackerRunning', strtr('<span>{{message}}</span> <a class="Button link" href="{{btnLink}}">{{btnLabel}}</a>', array(
            '{{message}}' => $view->translate('Tracker_running_tasks_message'),
            '{{btnLink}}' => $view->getModuleUrl('/Tracker/{{data.taskId}}'),
            '{{btnLabel}}' => $view->translate('Tracker_button_label')
        )));

        $this->notifications->defineTemplate('trackerError', strtr('<span>{{genericLabel}}</span> <dl>{{#data.failedTasks}}<dt>{{title}} #{{id}} (code {{code}})</dt><dd class="wspreline">{{message}}</dd>{{/data.failedTasks}}</dl>', array(
            '{{genericLabel}}' => $view->translate('Tracker_task_error_message')
        )));


        parent::prepareView($view);
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

    public function getDependencySetters()
    {
        return array(
            'SystemTasks' => array($this, 'setSystemTasks'),
            'UserNotifications' => array($this, 'setUserNotifications')
        );
    }

}