<?php
namespace Nethgui\Authorization;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * PDP that read policies from json encoded objects on the local filesystem
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class JsonPolicyDecisionPoint implements PolicyDecisionPointInterface, \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Log\LogConsumerInterface
{

    /**
     *
     * @var callable
     */
    private $fileNameResolver;

    /**
     *
     * @var \Nethgui\Utility\PhpWrapper
     */
    private $phpWrapper;

    /**
     *
     * @var \ArrayObject
     */
    private $rules;

    /**
     *
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    public function __construct($fileNameResolver, \Nethgui\Utility\PhpWrapper $phpWrapper = NULL)
    {
        $this->rules = new \ArrayObject();
        $this->fileNameResolver = $fileNameResolver;
        $this->phpWrapper = $phpWrapper === NULL ? new \Nethgui\Utility\PhpWrapper(__CLASS__) : $phpWrapper;
    }

    /**
     *
     * @param string $policyName
     * @return JsonPolicyDecisionPoint
     */
    public function loadPolicy($policyName)
    {
        $policyFileSpec = call_user_func($this->fileNameResolver, $policyName);

        if (strpos($policyFileSpec, '*') === FALSE) {
            $policyFiles = array($policyFileSpec);
        } else {
            $policyFiles = $this->phpWrapper->glob($policyFileSpec);
            if ($policyFiles === FALSE) {
                $this->getLog()->warning(sprintf('%s: invalid policy file specification `%s`', __CLASS__, $policyFileSpec));
                $policyFiles = array();
            }
        }
        
        foreach ($policyFiles as $policyFile) {
            $data = $this->phpWrapper->file_get_contents($policyFile);
            $this->loadJsonString(basename($policyFile), $data);
        }

        return $this;
    }

    private function loadJsonString($policyName, $data)
    {
        $rawRules = json_decode($data);

        if ($rawRules === NULL) {
            $jsonErrorCode = json_last_error();
            $jsonErrorMessage = $this->getJsonErrorReason($jsonErrorCode);
            throw new \UnexpectedValueException(sprintf("%s: error while reading policy file `%s`. Reason: %s (%d)", __CLASS__, $policyName, $jsonErrorMessage, $jsonErrorCode), 1327572840);
        }

        if ( ! is_array($rawRules)) {
            throw new \UnexpectedValueException(sprintf("%s: invalid policy file `%s`.", __CLASS__, $policyName), 1327572841);
        }

        foreach ($rawRules as $rawRule) {
            $ruleObject = PolicyRule::createFromObject($rawRule);

            // skip existing "final" rule:
            if (isset($this->rules[$ruleObject->getIdentifier()])) {
                if ($this->rules[$ruleObject->getIdentifier()]->isFinal()) {
                    continue;
                } else {
                    $this->getLog()->notice(sprintf('%s: rule#%d is overridden in policy `%s`', __CLASS__, $ruleObject->getIdentifier(), $policyName));
                }
            }

            $this->rules[$ruleObject->getIdentifier()] = $ruleObject;
        }

        // reverse sorting:
        $this->rules->uasort(function (PolicyRule $a, PolicyRule $b) {
                return - $a->compare($b);
            });

        return $this;
    }

    public function authorizeSync($request, &$message)
    {
        if ($this->rules->count() === 0) {
            $message = 'No rules defined, no restrictions applied.';
            return 0;
        }

        // Exit on the first applicable result:
        foreach ($this->rules as $rule) {
            if ($rule instanceof PolicyRule && $rule->isApplicableTo($request)) {
                if ($rule->isAllow()) {
                    $message = $rule->getDescription();
                    return 0;
                } else {
                    $message = $rule->getDescription();
                    return $rule->getIdentifier();
                }
            }
        }

        $message = 'Denied by default';
        return 1;
    }

    public function authorize($subject, $resource, $action)
    {
        $pdp = $this;

        $request = array(
            'subject' => $subject,
            'resource' => $resource,
            'action' => $action,
        );

        $f = function($request, &$message) use ($pdp) {
                return $pdp->authorizeSync($request, $message);
            };

        return new LazyAccessControlResponse($f, $request);
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
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
        return $this;
    }

    /**
     * This has been taken from the PHP online documentation
     *
     * @codeCoverageIgnore
     * @see http://it.php.net/manual/en/function.json-last-error.php
     * @param integer $errorCode
     * @return string
     */
    private function getJsonErrorReason($errorCode)
    {
        switch ($errorCode) {
            case JSON_ERROR_NONE:
                $message = 'JSON_ERROR_NONE - No errors';
                break;
            case JSON_ERROR_DEPTH:
                $message = 'JSON_ERROR_DEPTH - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $message = 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $message = 'JSON_ERROR_CTRL_CHAR - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $message = 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $message = 'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $message = 'Unknown error';
                break;
        }

        return $message;
    }

}
