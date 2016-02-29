<?php

namespace Nethgui\System;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
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
 * Implementation of the platform interface for Nethesis products
 *
 */
class NethPlatform implements PlatformInterface, \Nethgui\Authorization\PolicyEnforcementPointInterface, \Nethgui\Log\LogConsumerInterface, \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Utility\SessionConsumerInterface, \Nethgui\Component\DependencyConsumer
{
    /**
     * Cache of configuration database objects
     * @var array
     */
    private $databases;

    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;

    /**
     * Keeps User object acting on host configuration.
     * @var \Nethgui\Authorization\UserInterface
     */
    private $user;

    /**
     *
     * @var array
     */
    private $eventQueue;

    /**
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    /**
     * @var \Nethgui\Utility\PhpWrapper
     */
    private $phpWrapper;

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

    /**
     *
     * @var \Nethgui\Controller\Request
     */
    private $request;

    /**
     *
     * @var array
     */
    private $conditions;

    /**
     *
     * @param \Nethgui\Authorization\UserInterface $user
     * @param \Nethgui\Model\SystemTasks $systemTasks
     */
    public function __construct(\Nethgui\Authorization\UserInterface $user, \Nethgui\Model\SystemTasks $systemTasks)
    {
        $this->eventQueue = array('post-process' => array(), 'post-response' => array(), 'now' => array());
        $this->user = $user;
        $this->systemTasks = $systemTasks;
        $this->databases = array('SESSION' => new SessionDatabase());
        $this->phpWrapper = new \Nethgui\Utility\PhpWrapper(__CLASS__);
        $this->request = \Nethgui\Controller\NullRequest::getInstance();
        $this->conditions = array();
    }

    public function setSession(\Nethgui\Utility\SessionInterface $session)
    {
        $this->databases['SESSION']->setSession($session);
        return $this;
    }

    /**
     *
     * @param string $database SME database configuration name
     * @return EsmithDatabase
     */
    public function getDatabase($database)
    {
        if (isset($this->databases[$database])) {
            return $this->databases[$database];
        }
        $object = new EsmithDatabase($database, $this->user);
        $object
            ->setLog($this->getLog())
            ->setPolicyDecisionPoint($this->policyDecisionPoint)
            ->setPhpWrapper($this->phpWrapper)
        ;
        return $this->databases[$database] = $object;
    }

    public function getIdentityAdapter($database, $key, $prop = NULL, $separator = NULL)
    {

        $serializer = $this->getSerializer($database, $key, $prop);

        if (is_null($separator)) {
            $adapter = new \Nethgui\Adapter\ScalarAdapter($serializer);
        } else {
            $adapter = new \Nethgui\Adapter\ArrayAdapter($separator, $serializer);
        }

        return $adapter;
    }

    public function getMapAdapter($readCallback, $writeCallback, $args)
    {

        // Create a Multiple adapter
        $serializers = array();

        foreach ($args as $serializerSpec) {
            $serializers[] = call_user_func_array(array($this, 'getSerializer'), $serializerSpec);
        }

        $adapter = new \Nethgui\Adapter\MultipleAdapter($readCallback, $writeCallback, $serializers);

        return $adapter;
    }

    public function getTableAdapter($database, $typeOrKey, $filterOrProp = NULL, $separators = NULL)
    {
        if (is_null($separators)) {
            return new \Nethgui\Adapter\TableAdapter($this->getDatabase($database), $typeOrKey, $filterOrProp);
        }

        $innerAdapter = $this->getIdentityAdapter($database, $typeOrKey, $filterOrProp, $separators[0]);

        return new \Nethgui\Adapter\TabularValueAdapter($innerAdapter, $separators[1]);
    }

    /**
     *
     * @param mixed  $source
     * @param string $key
     * @param string $prop
     * @return \Nethgui\Serializer\SerializerInterface
     */
    private function getSerializer($source, $key, $prop = NULL)
    {
        if ($source instanceof \ArrayAccess) {
            $serializer = new \Nethgui\Serializer\ArrayAccessSerializer($source, $key, $prop);
        } elseif (is_string($source)) {
            if (is_null($prop)) {
                $serializer = new \Nethgui\Serializer\KeySerializer($this->getDatabase($source), $key);
            } else {
                $serializer = new \Nethgui\Serializer\PropSerializer($this->getDatabase($source), $key, $prop);
            }
        } else {
            throw new \InvalidArgumentException(sprintf('%s: cannot create a SerializerInterface instance', __CLASS__), 1336467547);
        }

        return $serializer;
    }

    public function signalEvent($eventSpecification, $arguments = array())
    {
        $matches = array();

        preg_match('/(?P<event>[^@ ]+)(@(?P<queue>[^ ]+))?( +(?P<detached>&))?/', $eventSpecification, $matches);

        if ( ! isset($matches['event'])) {
            throw new \InvalidArgumentException(sprintf("%s: invalid event specification", get_class($this)), 1325578497);
        }

        $detached = isset($matches['detached']) && $matches['detached'] === '&' ? TRUE : FALSE;

        if ($detached) {
            $queue = 'post-response';
        } else {
            $queue = isset($matches['queue']) && in_array($matches['queue'], array_keys($this->eventQueue)) ? $matches['queue'] : 'now';
        }

        $cmd = '/usr/bin/sudo -n /sbin/e-smith/signal-event ${@}';
        $args = array_merge(array($matches['event']), $arguments);

        if ($queue === 'post-process') {
            // create a sync event in post-process queue
            $process = $this->createPtrackProcess($cmd, $args, FALSE);
            $this->eventQueue['post-process'][] = $process;
        } else {
            $process = $this->createPtrackProcess($cmd, $args, $detached);
            if ($detached) {
                $this->eventQueue['post-response'][] = $process;
            } else {
                // event queue "now":
                $process->run();
                $this->notifyEvent($process);
            }
        }

        return $process;
    }

    private function notifyEvent(\Symfony\Component\Process\Process $process)
    {
        if ($process->getExitCode() !== 0) {
            $this->notifications->trackerError(array('failedTasks' => \Nethgui\Module\Tracker::findFailures($this->systemTasks->getTaskStatus($process->taskId))));
        }
    }

    private function getLangForProcess()
    {
        $locale = $this->request->getLocale();

        // FIXME: this mapping is provided for backward compatibility
        // and can be removed in future versions:
        if($locale === 'en') {
            $locale = 'en-GB';
        } elseif($locale === 'it') {
            $locale = 'it-IT';
        }
        return \str_replace('-', '_', $locale) . '.utf8';
    }

    private function createPtrackProcess($command, $args, $detached)
    {
        $taskId = md5(uniqid());
        if ($detached) {
            $this->systemTasks->setTaskStarting($taskId);
        }
        $socketPath = sprintf('/var/run/ptrack/%s.sock', $taskId);
        $dumpPath = sprintf(\Nethgui\Model\SystemTasks::PTRACK_DUMP_PATH, md5($socketPath));
        $cmd = strtr('/usr/libexec/nethserver/ptrack %detached -j -s %socketPath -d %dumpPath %verbose -- ', array(
                '%detached' => $detached ? '-D' : '',
                '%verbose' => \NETHGUI_DEBUG ? '-v' : '',
                '%dumpPath' => \escapeshellarg($dumpPath),
                '%socketPath' => \escapeshellarg($socketPath)
            )) . $this->prepareEscapedCommand($command, $args);

        $process = new \Nethgui\System\Process($cmd);
        $process->setEnv(array('LANG' => $this->getLangForProcess()));
        $process->setInput('{}');
        $process->taskId = $taskId;
        return $process;
    }

    private function getProcessInput()
    {
        $onSuccessDefault = array(
            'location' => array('url' => implode('/', $this->request->getPath()) . '?taskStatus=success&taskId={taskId}',
                'freeze' => TRUE));
        $onFailureDefault = array(
            'location' => array('url' => implode('/', $this->request->getPath()) . '?taskStatus=failure&taskId={taskId}',
                'freeze' => TRUE));

        $input = json_encode(array(
            'starttime' => microtime(TRUE),
            'conditions' => array(
                'success' => isset($this->conditions['success']) ? $this->conditions['success'] : $onSuccessDefault,
                'failure' => isset($this->conditions['failure']) ? $this->conditions['failure'] : $onFailureDefault
            )
        ));

        return $input;
    }

    public function setDetachedProcessCondition($condition, $values)
    {
        if (isset($this->conditions[$condition])) {
            return $this;
        }
        $this->conditions[$condition] = $values;
        return $this;
    }

    /**
     * Run enqueued events
     * 
     * @return void
     */
    public function runEvents($queueName)
    {
        if ( ! isset($this->eventQueue[$queueName])) {
            return;
        }

        foreach ($this->eventQueue[$queueName] as $process) {
            if($queueName === 'post-response') {
                $process->setInput($this->getProcessInput());
            }
            $process->run();
            if ($process->getExitCode() !== 0) {
                $this->getLog()->error(sprintf("%s: process on queue `%s` exited with code %d: %s", get_class($this), $queueName, $process->getExitCode(), $process->getCommandLine()));
            }
            $this->notifyEvent($process);
        }

        $this->eventQueue[$queueName] = array();
    }

    public function setPolicyDecisionPoint(\Nethgui\Authorization\PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
        return $this;
    }

    private function prepareEscapedCommand($command, $arguments)
    {
        $escapedArguments = array();
        $i = 1;
        foreach ($arguments as $arg) {

            if (is_string($arg)) {
                $argOutput = $arg;
            } elseif (is_callable($arg)) {
                $argOutput = call_user_func($arg);
            } else {
                $argOutput = strval($arg);
            }

            $escapedArguments[sprintf('${%d}', $i)] = escapeshellarg($argOutput);
            $i ++;
        }
        $escapedArguments['${@}'] = implode(' ', $escapedArguments);

        return strtr($command, $escapedArguments);
    }

    public function exec($command, $arguments = array(), $detached = FALSE)
    {
        if ($detached === FALSE) {
            $process = new \Nethgui\System\Process($this->prepareEscapedCommand($command, $arguments));
            $process->setEnv(array('LANG' => $this->getLangForProcess()));
            $process->log = $this->getLog();
            $process->run();
        } else {
            $process = $this->createPtrackProcess($command, $arguments, $detached);
            $this->eventQueue['post-response'][] = $process;
        }

        return $process;
    }

    public function getLog()
    {
        if ( ! isset($this->log)) {
            $this->log = new \Nethgui\Log\Nullog();
        }
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        $this->phpWrapper->setLog($log);
        return $this;
    }

    public function getDateFormat()
    {
        return 'YYYY-mm-dd';
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
    }

    public function getDetachedProcesses()
    {
        return array();
    }

    public function getDetachedProcess($identifier)
    {
        return NULL;
    }

    /**
     * Creates a Validator object that checks against each of the function arguemnts
     *
     * @param integer ...
     * @return Validator
     */
    public function createValidator()
    {
        $validator = new Validator($this);

        foreach (func_get_args() as $ruleCode) {
            switch ($ruleCode) {
                case self::ANYTHING:
                    $validator->forceResult(TRUE);
                    break;

                case self::ANYTHING_COLLECTION:
                    $validator->orValidator($this->createValidator()->isEmpty(), $this->createValidator()->collectionValidator($this->createValidator()->forceResult(TRUE)));
                    break;

                case self::USERNAME_COLLECTION:
                    $validator->orValidator($this->createValidator()->isEmpty(), $this->createValidator()->collectionValidator($this->createValidator()->username()));
                    break;

                case self::SERVICESTATUS:
                    $validator->memberOf('enabled', 'disabled');
                    break;

                case self::USERNAME:
                    $validator->username();
                    break;

                case self::HOSTNAME:
                    $validator->hostname();
                    break;

                case self::HOSTNAME_FQDN:
                    $validator->hostname(1);
                    break;

                case self::HOSTNAME_SIMPLE:
                    $validator->hostname(0, 0);
                    break;

                case self::HOSTADDRESS:
                    $validator->orValidator($this->createValidator()->ipV4Address(), $this->createValidator()->hostname());
                    break;

                case self::NOTEMPTY:
                    $validator->notEmpty();
                    break;

                case self::DATE:
                    $validator->date();
                    break;

                case self::TIME:
                    $validator->time();
                    break;

                case self::IP:
                case self::IPv4:
                    $validator->ipV4Address();
                    break;

                case self::NETMASK:
                case self::IPv4_NETMASK:
                    $validator->ipV4Netmask();
                    break;

                case self::MACADDRESS:
                    $validator->macAddress();
                    break;

                case self::POSITIVE_INTEGER:
                    $validator->integer()->positive();
                    break;

                case self::NONNEGATIVE_INTEGER:
                    $validator->integer()->greatThan(-1);
                    break;

                case self::PORTNUMBER:
                    $validator->integer()->greatThan(0)->lessThan(65536);
                    break;

                case self::BOOLEAN:
                    $validator->memberOf('1', 'yes', '0', '');
                    break;

                case self::IP_OR_EMPTY:
                case self::IPv4_OR_EMPTY:
                    $validator->orValidator($this->createValidator()->ipV4Address(), $this->createValidator()->isEmpty());
                    break;

                case self::NETMASK_OR_EMPTY:
                case self::IPv4_NETMASK_OR_EMPTY:
                    $validator->orValidator($this->createValidator()->ipV4Netmask(), $this->createValidator()->isEmpty());
                    break;

                case self::YES_NO:
                    $validator->memberOf('yes', 'no');
                    break;

                case self::EMAIL:
                    $validator->email();
                    break;

                case self::EMPTYSTRING:
                    $validator->maxLength(0);
                    break;

                case self::CIDR_BLOCK:
                    $validator->cidrBlock();
                    break;

                case NULL:
                    continue;

                default:
                    throw new \InvalidArgumentException(sprintf('%s: Unknown validator code: %s', get_class($this), $ruleCode), 1326380984);
            }
        }
        return $validator;
    }

    public function setRequest(\Nethgui\Controller\Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function setUserNotifications(\Nethgui\Model\UserNotifications $notifications)
    {
        $this->notifications = $notifications;
        return $this;
    }

    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setUserNotifications'),
            'OriginalRequest' => array($this, 'setRequest')
        );
    }

}