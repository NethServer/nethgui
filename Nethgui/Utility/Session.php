<?php

namespace Nethgui\Utility;

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
 * Store data in php $_SESSION variable.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @internal
 */
class Session implements \Nethgui\Utility\SessionInterface, \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Log\LogConsumerInterface
{
    const SESSION_NAME = 'nethgui';
    const SESSION_RENEW_PERIOD = 28800; // 8 hours

    /**
     *
     * @var \Nethgui\Utility\PhpWrapper
     */
    private $phpWrapper;

    /**
     *
     * @var ArrayObject
     */
    private $data;

    /**
     *
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    public function __construct(\Nethgui\Utility\PhpWrapper $phpWrapper = NULL)
    {
        $this->phpWrapper = $phpWrapper === NULL ? new \Nethgui\Utility\PhpWrapper(__CLASS__) : $phpWrapper;
        $this->data = new \ArrayObject();
        $this->phpWrapper->session_name(self::SESSION_NAME);
    }

    public function isStarted()
    {
        return $this->getSessionIdentifier() !== '';
    }

    public function start()
    {
        if ($this->isStarted()) {
            throw new \LogicException(sprintf('%s: cannot start an already started session!', __CLASS__), 1327397142);
        }

        $tsnow = time();
        $this->phpWrapper->session_start();

        NETHGUI_DEBUG && $this->getLog()->notice(sprintf('%s: session_start()', __CLASS__));

        $this->data = $this->phpWrapper->phpReadGlobalVariable('_SESSION', self::SESSION_NAME);
        if (is_null($this->data)) {
            // Session data initialization
            $security = new \ArrayObject(array(
                'reverseProxy' => (bool) $this->phpWrapper->phpReadGlobalVariable('_SERVER', 'HTTP_X_FORWARDED_HOST'),
                'started' => $tsnow,
                'renewed' => $tsnow,
                'updated' => $tsnow,
                'MaxSessionIdleTime' => 0,
                'MaxSessionLifeTime' => 0,
            ));
            $this->data = new \ArrayObject(array(
                'SECURITY' => $security
            ));
        } elseif ( ! $this->data instanceof \ArrayObject) {
            throw new \UnexpectedValueException(sprintf('%s: session data must be enclosed into an \ArrayObject', __CLASS__), 1322738011);
        }

        // Upgrade to new session storage format, where csrfToken is an array:
        if(isset($this->data['SECURITY']['csrfToken']) && is_string($this->data['SECURITY']['csrfToken'])) {
            $this->data['SECURITY']['csrfToken'] = array($this->data['SECURITY']['csrfToken']);
        }

        if(isset($this->data['SECURITY']['updated'])) {
            $updated = $this->data['SECURITY']['updated'];
            $maxSessionIdleTime = $this->data['SECURITY']['MaxSessionIdleTime'];
            $maxSessionLifeTime = $this->data['SECURITY']['MaxSessionLifeTime'];

            $this->data['SECURITY']['updated'] = $tsnow;
            if($maxSessionIdleTime > 0 && $tsnow > $updated + $maxSessionIdleTime) {
                $this->getLog()->notice(sprintf('%s: Session terminated after %d seconds of inactivity', __CLASS__, $maxSessionIdleTime));
                $this->logout();
            } elseif($maxSessionLifeTime > 0 && $tsnow > $updated + $maxSessionLifeTime) {
                $this->getLog()->notice(sprintf('%s: Session terminated after reaching the maximum age of %d seconds', __CLASS__, $maxSessionLifeTime));
                $this->logout();
            }
        }

        return $this;
    }

    public function setSessionSetupRetriever($f)
    {
        $this->sessionSetupRetriever = $f;
        return $this;
    }

    public function unlock()
    {
        static $unlocked;
        if ($this->isStarted() && $unlocked !== TRUE) {
            $key = get_class($this);
            if (isset($this->data[$key]) && $this->data[$key] === TRUE) {
                $this->phpWrapper->phpWriteGlobalVariable($this->data, '_SESSION', self::SESSION_NAME);
            }
            $this->phpWrapper->session_write_close();
            NETHGUI_DEBUG && $this->getLog()->notice(sprintf('%s: session_write_close()', __CLASS__));
            $unlocked = TRUE;
        }
        return $this;
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
    }

    private function getSessionIdentifier()
    {
        return $this->phpWrapper->session_id();
    }

    public function retrieve($key)
    {
        if ( ! $this->isStarted()) {
            $this->start();
        }

        if ( ! isset($this->data[$key]) || $this->data[$key] === NULL) {
            return NULL;
        }

        $object = $this->data[$key];

        if ( ! $object instanceof \Serializable) {
            throw new \UnexpectedValueException(sprintf('%s: only \Serializable implementors can be stored in this collection!', __CLASS__), 1322738020);
        }

        return $object;
    }

    public function store($key, \Serializable $object = NULL)
    {
        if ( ! $this->isStarted()) {
            $this->start();
        }
        $this->data[$key] = $object;
        return $this;
    }

    public function login()
    {
        $this->phpWrapper->session_regenerate_id(TRUE);
        $this->rotateCsrfToken();
        $this->data[get_class($this)] = TRUE;
        $sessionSetup = is_callable($this->sessionSetupRetriever) ? call_user_func($this->sessionSetupRetriever) : array();
        $this->data['SECURITY']['MaxSessionIdleTime'] = $sessionSetup['MaxSessionIdleTime'] ?: 0; // disabled
        $this->data['SECURITY']['MaxSessionLifeTime'] = $sessionSetup['MaxSessionLifeTime'] ?: 0; // disabled
        return $this;
    }

    public function logout()
    {
        $this->phpWrapper->setcookie(self::SESSION_NAME, 'logout', time() - 1, '/');
        $this->phpWrapper->session_destroy();
        $this->phpWrapper->session_write_close();
        $this->data[get_class($this)] = FALSE;
        return $this;
    }

    public function __destruct()
    {
        $this->unlock();
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
        return $log;
    }

    public function checkHandoff()
    {
        $tsnow = time();
        if(isset($this->data['SECURITY']['renewed']) && $this->data['SECURITY']['renewed'] + self::SESSION_RENEW_PERIOD < $tsnow) {
            $this->getLog()->notice(sprintf('%s: regenerate session id', __CLASS__));
            $this->phpWrapper->session_regenerate_id(TRUE);
            $this->data['SECURITY']['renewed'] = $tsnow;
        }
    }

    public function rotateCsrfToken()
    {
        static $once;
        if(isset($once)) {
            $once = TRUE;
            $this->getLog()->notice(sprintf('%s: CSRF token has just been generated', __CLASS__));
            return $this;
        }

        $uh = fopen('/dev/urandom', 'r');
        if($uh !== FALSE) {
            $data = fread($uh, 64);
            fclose($uh);
        }
        if(! $data) {
            $this->getLog()->error(sprintf('%s: could not generate CSRF token properly.', __CLASS__));
            $data = md5(uniqid(mt_rand(), TRUE));
        }
        array_unshift($this->data['SECURITY']['csrfToken'], bin2hex($data));
        $this->data['SECURITY']['csrfToken'] = array_splice($this->data['SECURITY']['csrfToken'], 0, 5);
        return $this;
    }
}