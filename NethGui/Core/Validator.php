<?php
/**
 * @package Core
 */

/**
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @package Core
 */
class NethGui_Core_Validator implements NethGui_Core_ValidatorInterface
{

    private $chain = array();
    private $failureInfo;

    /**
     *
     * @param NethGui_Core_ValidatorInterface $v1
     * @param NethGui_Core_ValidatorInterface $v2
     * @return NethGui_Core_Validator
     */
    public function orValidator(NethGui_Core_ValidatorInterface $v1, NethGui_Core_ValidatorInterface $v2)
    {
        $this->chain[] = new NethGui_Core_OrValidator($v1, $v2);
        return $this;
    }

    /**
     * If the first and only argument is an array checks if current value is
     * in that array.
     *
     * Otherwise you can pass arbitrary arguments. It will be checked if the
     * current value matches any of them.
     *
     * @return  NethGui_Core_Validator
     */
    public function memberOf()
    {
        $args = func_get_args();

        if (isset($args[0]) && is_array($args[0]) && count($args) == 1) {
            $set = $args[0];
        } else {
            $set = $args;
        }

        $messageTemplate = array('member of [${0}]', array('${0}' => implode(', ', $set)));

        return $this->addToChain(__FUNCTION__, $messageTemplate, $set);
    }

    /**
     * @see preg_match
     * @param string $e A PHP preg_match compatible regular expression
     * @return NethGui_Core_Validator
     */
    public function regexp($e)
    {
        $messageTemplate = array('regexp "${0}"', array('${0}' => $e));
        return $this->addToChain(__FUNCTION__, $messageTemplate, $e);
    }

    /**
     * Checks if current value is not empty
     * 
     * @see PHP empty
     * @return NethGui_Core_Validator
     */
    public function notEmpty()
    {
        return $this->addToChain(__FUNCTION__);
    }

    /**
     * Checks if current value is empty
     * 
     * @see PHP empty
     * @return NethGui_Core_Validator
     */
    public function isEmpty()
    {
        return $this->addToChain(__FUNCTION__);
    }

    /**
     * Force the evaluation result
     * @param bool exit status
     * @return NethGui_Core_Validator
     */
    public function forceResult($result)
    {
        $this->chain[] = ($result === TRUE);
        return $this;
    }

    /**
     * Check if the given value is a valid IPv4 address
     * @return NethGui_Core_Validator
     */
    public function ipV4Address()
    {
        return $this->addToChain(__FUNCTION__);
    }

    /**
     * @todo
     * @return NethGui_Core_Validator
     */
    public function ipV6Address()
    {
        return $this->notImplemented(__FUNCTION__);
    }

    /**
     * @todo
     * @return NethGui_Core_Validator
     */
    public function ipV4Netmask()
    {
        return $this->notImplemented(__FUNCTION__);
    }

    /**
     * @todo
     * @return NethGui_Core_Validator
     */
    public function ipV6Netmask()
    {
        return $this->notImplemented(__FUNCTION__);
    }

    public function integer()
    {
        return $this->addToChain(__FUNCTION__);
    }

    public function positive()
    {
        return $this->addToChain(__FUNCTION__);
    }

    public function negative()
    {
        return $this->addToChain(__FUNCTION__);
    }

    public function lessThan($cmp)
    {
        $template = array('less than ${0}', array('${0}'=> $cmp));
        return $this->addToChain(__FUNCTION__, $template, $cmp);
    }

    public function greatThan($cmp)
    {
        $template = array('great than ${0}', array('${0}'=> $cmp));
        return $this->addToChain(__FUNCTION__, $template, $cmp);
    }

    public function equal($cmp)
    {
        $template = array('equal to ${0}', array('${0}'=> $cmp));
        return $this->addToChain(__FUNCTION__, $template, $cmp);
    }


    /**
     * Invert the evaluation result for the next rule.
     * @return NethGui_Core_Validator
     */
    public function not()
    {
        $this->chain[] = -1;
        return $this;
    }

    /**
     * Check if the value is a valid Unix user name
     * @return NethGui_Core_Validator
     */
    public function username()
    {
        return $this->addToChain(__FUNCTION__);
    }

    /**
     * Check if the value is collection of elements satisfying the given validator
     * @param NethGui_Core_Validator $v Member validator
     * @return NethGui_Core_Validator 
     */
    public function collectionValidator(NethGui_Core_Validator $v)
    {
        $this->chain[] = new NethGui_Core_CollectionValidator($v);
        return $this;
    }

    public function getFailureInfo()
    {
        return $this->failureInfo;
    }

    public function evaluate($value)
    {
        $this->failureInfo = array();

        if (empty($this->chain)) {
            return FALSE;
        }

        $notFlag = FALSE;

        foreach ($this->chain as $expression) {
            if (is_integer($expression) && $expression < 0) {
                // set $notFlag flag. Next $expression will be inverted: NOT(exp)
                $notFlag = TRUE;
                continue;
            } elseif (is_array($expression) && is_callable($expression[1])) {
                // $expression is an array of four elements
                // 0. the original method name, as a string
                // 1. a callable
                // 2. an optional array of arguments
                // 3. the error message template plus arguments
                $args = array();
                if (isset($expression[2]) && is_array($expression[2])) {
                    $args = $expression[2];
                } else {
                    $args = array();
                }

                if ( ! isset($expression[3]) || ! is_array($expression[3])) {
                    $expression[3] = array($expression[0], array());
                }

                array_unshift($args, $value);
                $isValid = call_user_func_array($expression[1], $args);
                if (($isValid XOR $notFlag) === FALSE) {
                    $this->failureInfo = $expression[3];
                    return FALSE;
                }
            } elseif ($expression instanceof NethGui_Core_ValidatorInterface) {
                $isValid = $expression->evaluate($value);
                if (($isValid XOR $notFlag) === FALSE) {
                    $this->failureInfo = $expression->getFailureInfo();
                    return FALSE;
                }
            } elseif ($expression === FALSE) {
                $this->failureInfo = 'forceResult';
                return FALSE;
            } elseif ($expression === TRUE) {
                break;
            }

            // reset $notFlag flag
            $notFlag = FALSE;
        }

        $this->failureInfo = FALSE;
        return TRUE;
    }

    /**
     * In development environment a not implemented rule is simply ignored,
     * otherwise an exception is raised.
     * 
     * @codeCoverageIgnore
     * @param string $method
     * @return NethGui_Core_Validator
     */
    private function notImplemented($method)
    {
        if (defined('ENVIRONMENT')
            && ENVIRONMENT == 'development') {
            NethGui_Framework::getInstance()->logMessage($method . ' is not implemented - SKIPPING, as we are in development environment.', 'warning');
            return $this;
        }

        throw new NethGui_Exception_Validation($method . ' is not implemented.');
    }

    /**
     * @param string the calling Method name
     * @param string Optional the error message template applyed to sprintf()
     * @param mixed Optional - First argument to evaluation function
     * @param mixed Optional - Second argument to evaluation function
     * @param mixed Optional - ...
     *
     */
    private function addToChain()
    {
        $args = func_get_args();

        $originalMethodName = array_shift($args);
        $errorMessageTemplate = array_shift($args);

        $methodName = 'eval' . ucfirst($originalMethodName);

        $this->chain[] = array(
            $originalMethodName,
            array($this, $methodName),
            $args,
            $errorMessageTemplate,
        );

        return $this;
    }

    private function evalNotEmpty($value)
    {
        if ( ! empty($value)) {
            return TRUE;
        }
        return FALSE;
    }

    private function evalIsEmpty($value)
    {
        return empty($value);
    }

    private function evalRegexp($value, $exp)
    {
        return (preg_match($exp, $value) > 0);
    }

    private function evalMemberOf($value, $args)
    {
        return in_array($value, $args, TRUE);
    }

    /**
     * Validate IP Address
     *
     * Updated version suggested by Geert De Deckere
     *
     * @access       public
     * @param        string
     * @return       string
     * @author CodeIgniter
     */
    private function evalIpV4Address($value)
    {
        $ip_segments = explode('.', $value);

        // Always 4 segments needed
        if (count($ip_segments) != 4) {
            return FALSE;
        }
        // IP can not start with 0
        if ($ip_segments[0][0] == '0') {
            return FALSE;
        }

        // Check each segment
        foreach ($ip_segments as $segment) {
            // IP segments must be digits and can not be
            // longer than 3 digits or greater then 255
            if ($segment == '' OR preg_match("/[^0-9]/", $segment) OR $segment > 255 OR strlen($segment) > 3) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Check if $value is a valid Linux username
     * @param string $value 
     */
    private function evalUsername($value)
    {
        return strlen($value) < 32 && $this->evalRegexp($value, '/^[a-z][-_a-z0-9]*$/');
    }

    /**
     * Check if $value is an integer
     * @param string $value
     */
    private function evalInteger($value)
    {
        return is_numeric($value) && (string) $value == (string) intval($value);
    }

    private function evalPositive($value)
    {
        return $value > 0;
    }

    private function evalNegative($value)
    {
        return $value < 0;
    }

    private function evalLessThan($value, $cmp)
    {
        return $value < $cmp;
    }

    private function greaterThan($value, $cmp)
    {
        return $value > $cmp;
    }

    private function evalEqual($value, $cmp)
    {
        return $value == $cmp;
    }

}

/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @package Core
 */
class NethGui_Core_CollectionValidator implements NethGui_Core_ValidatorInterface
{

    /**
     *
     * @var NethGui_Core_ValidatorInterface
     */
    private $memberValidator;
    private $failureInfo;
    /**
     *
     * @var Iterator
     */
    private $iterator;

    public function __construct(NethGui_Core_ValidatorInterface $memberValidator)
    {
        $this->memberValidator = $memberValidator;
    }

    public function evaluate($iterableObject)
    {
        $this->failureInfo = array();

        if (is_array($iterableObject)) {
            $iterableObject = new ArrayObject($iterableObject);
            $this->iterator = $iterableObject->getIterator();
        } elseif ($iterableObject instanceof IteratorAggregate) {
            $this->iterator = $iterableObject->getIterator();
        } elseif ($iterableObject instanceof Iterator) {
            $this->iterator = $iterableObject;
        } else {
            $this->failureInfo[] = array("Not a collection", array());
            return FALSE;
        }

        foreach ($this->iterator as $e) {
            if ($this->memberValidator->evaluate($e) === FALSE) {
                $this->failureInfo[] = $this->memberValidator->getFailureInfo();
                return FALSE;
            }
        }


        return TRUE;
    }

    public function getFailureInfo()
    {
        return $this->failureInfo;
    }

}

/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @package Core
 * @internal
 * @see NethGui_Core_Validator::orValidator()
 */
class NethGui_Core_OrValidator implements NethGui_Core_ValidatorInterface
{

    /**
     *
     * @var NethGui_Core_ValidatorInterface
     */
    private $v1;
    /**
     *
     * @var NethGui_Core_ValidatorInterface
     */
    private $v2;
    private $failureInfo;

    public function __construct(NethGui_Core_ValidatorInterface $v1, NethGui_Core_ValidatorInterface $v2)
    {
        $this->v1 = $v1;
        $this->v2 = $v2;
    }

    public function evaluate($value)
    {
        $this->failureInfo = array();
        $e1 = $this->v1->evaluate($value);

        if ($e1 === FALSE) {
            $e2 = $this->v2->evaluate($value);

            if ($e2 === FALSE) {
                $this->failureInfo[] = $this->v1->getFailureInfo();
                $this->failureInfo[] = $this->v2->getFailureInfo();
                return FALSE;
            }
            return TRUE;
        }

        return TRUE;
    }

    public function getFailureInfo()
    {
        return $this->failureInfo;
    }

}
