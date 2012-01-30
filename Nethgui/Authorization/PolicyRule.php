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
 * Authorization rules decide if a Subject is allowed to perform an Action on
 * a given Resource
 *
 * Rules are ordered by their specificity/generality, so that more specific rules
 * are considered more relevant to the authorization decision.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class PolicyRule
{

    /**
     *
     * @var integer
     */
    private $id;

    /**
     *
     * @var string
     */
    private $effect;

    /**
     * @var string
     */
    private $description;

    /**
     *
     * @var array
     */
    private $matcher;

    /**
     *
     * @var float
     */
    private $specificity;

    /**
     * By default a rule is overridable by another rule with the same Id
     * @var boolean
     */
    private $isFinal = FALSE;

    private function __construct()
    {
        $this->id = 0;
        $this->description = '';
        $this->effect = 'DENY';
        $this->matcher = array();
    }

    /**
     * Construct an instance from a plain object
     * 
     * @param object $o
     * @return PolicyRule
     */
    public static function createFromObject($o)
    {
        $instance = new static();

        foreach ($o as $property => $value) {
            $property = strtolower($property);

            if ($property === 'id') {
                $instance->id = intval($value);
            } elseif ($property === 'description') {
                $instance->description = $value;
            } elseif ($property === 'effect') {
                $instance->effect = strtoupper($value);
            } elseif ($property === 'final') {
                $instance->isFinal = (boolean) $value;
            } else {
                $instance->matcher[$property] = $instance->parseMatcher($value);
            }
        }

        return $instance;
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return sprintf('%s (score = %.2f)', $this->description, $this->getSpecificity());
    }

    /**
     *
     * @return integer
     */
    public function getIdentifier()
    {
        return $this->id;
    }

    public function isAllow()
    {
        return $this->effect === 'ALLOW';
    }

    /**
     *
     * @param mixed $subject
     * @param mixed $action
     * @param mixed $resource
     * @return boolean
     */
    public function isApplicableTo($subject, $resource, $action)
    {
        $matchers = array('subject', 'resource', 'action');

        foreach ($matchers as $matcherId => $matcherName) {
            if ( ! isset($this->matcher[$matcherName])) {
                continue; // undefined matchers are skipped
            }

            $result = $this->matcher[$matcherName]->evaluate(func_get_arg($matcherId));
            if ( ! $result) {
                // matcher failed
                return FALSE;
            }
        }

        // all matchers evaluates to TRUE
        return TRUE;
    }

    /**
     * If a rule is not final it is overridable by
     * another rule with the same Identifier
     * 
     * @return boolean
     */
    public function isFinal()
    {
        return $this->isFinal === TRUE;
    }

    /**
     * @return float
     */
    private function getSpecificity()
    {
        if ( ! isset($this->specificity)) {
            $this->specificity = 0.0;
            foreach ($this->matcher as $matcher) {
                $this->specificity += $matcher->getSpecificity();
            }
        }

        return $this->specificity;
    }

    /**
     * Return an integer less than, equal to, or greater than zero if the
     * this instance is considered to be respectively less than, equal to,
     * or greater than the $other rule;
     *
     * @param PolicyRule $other
     * @return integer
     */
    public function compare(PolicyRule $other)
    {
        if ($this->getSpecificity() === $other->getSpecificity()) {
            return 0;
        } elseif ($this->getSpecificity() > $other->getSpecificity()) {
            return 1;
        } else {
            return -1;
        }
    }

    /**
     *
     * @param mixed $value
     * @return AbstractPolicyRuleMatcher
     */
    private function parseMatcher($value)
    {
        // Convert an array into AnyOfPolicyRuleMatcher:
        if (is_array($value)) {
            if (empty($value)) {
                return $this->parseMatcher('!*');
            }
            $a = $this->parseMatcher(\Nethgui\array_head($value));
            if (count($value) == 1) {
                return $a;
            }
            $b = $this->parseMatcher(\Nethgui\array_rest($value));
            return new AnyOfPolicyRuleMatcher($a, $b);
        }

        $parser = new PolicyExpressionParser($value);
        return $parser->parse();
    }

}

/**
 * A simple descent parser for the following grammar:
 *
 * Expr := SubExpr BinaryOp
 *
 * SubExpr := STRING
 *      | DOT STRING
 *      | ( Expr )
 *      | NEGATION SubExpr
 *
 * BinaryOp := EPS
 *      | AND Expr
 *      | OR Expr
 *
 *
 * AND := "&&"
 * OR := "||"
 * EQ := "=="
 * eps := ""
 * STRING := <any other string>
 * DOT := "."
 * NEGATION := "!"
 *
 * @internal
 * @since 1.0
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class PolicyExpressionParser
{

    private $tokens;
    private $symbol;
    private $value;
    private $offset;

    const T_STRING = 1;
    const T_WHITESPACE = 2;
    const T_BOOLEAN_AND = 3;
    const T_BOOLEAN_OR = 4;
    const T_NEGATION = 5;
    const T_LEFT_PARENS = 6;
    const T_RIGHT_PARENS = 7;
    const T_DOT = 8;
    const T_EQUAL = 9;
    const T_IN = 10;

    /**
     *
     * @param string $text
     */
    public function __construct($text)
    {
        if ( ! is_string($text)) {
            throw new \InvalidArgumentException(sprintf('%s: data must be a string', __CLASS__), 1327658033);
        }

        $this->input = $text;
        $this->tokens = preg_split("/(\.|!|\&\&|\|\||\(|\))/ ", $text, NULL, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
    }

    /**
     * @return AbstractPolicyRuleMatcher
     */
    public function parse()
    {
        return $this->Expr();
    }

    private function read()
    {

        list($token, $offset) = array_shift($this->tokens);

        $this->offset = $offset;

        switch ($token) {
            case FALSE;
                $this->symbol = FALSE;
                break;
            case '&&':
                $this->symbol = self::T_BOOLEAN_AND;
                break;
            case '||':
                $this->symbol = self::T_BOOLEAN_OR;
                break;
            case "!":
                $this->symbol = self::T_NEGATION;
                break;
            case "(":
                $this->symbol = self::T_LEFT_PARENS;
                break;
            case ")":
                $this->symbol = self::T_RIGHT_PARENS;
                break;
            case ".":
                $this->symbol = self::T_DOT;
                break;
            default:
                $token = trim($token);
                if (strlen($token) > 0) {
                    $this->symbol = self::T_STRING;
                } else {
                    $this->symbol = self::T_WHITESPACE;
                }
        }

        $this->value = $token;

        // skip whitespaces
        if ($this->symbol === self::T_WHITESPACE) {
            $this->read();
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    private function accept($sym)
    {
        if ($this->symbol === $sym) {
            return $this;
        }

        $this->throwError($sym);
    }

    private function tokenName($s)
    {
        switch ($s) {
            case self::T_STRING: return 'T_STRING';
            case self::T_WHITESPACE: return 'T_WHITESPACE';
            case self::T_BOOLEAN_AND: return 'T_BOOLEAN_AND';
            case self::T_BOOLEAN_OR: return 'T_BOOLEAN_OR';
            case self::T_NEGATION: return 'T_NEGATION';
            case self::T_LEFT_PARENS: return 'T_LEFT_PARENS';
            case self::T_RIGHT_PARENS: return 'T_RIGHT_PARENS';
            case self::T_DOT: return 'T_DOT';
            default: return 'UNKNOWN ' . $s;
        }
    }

    /**
     * @throws \UnexpectedValueException
     * @param integer $s
     */
    private function throwError($s)
    {
        $tok = $this->tokenName($s);
        $expr = '`' . substr($this->input, 0, $this->offset) . '` {!} `' . substr($this->input, $this->offset) . '`';
        throw new \UnexpectedValueException(sprintf('%s: unexpected token %s; %s', __CLASS__, $tok, $expr), 1327593544);
    }

    private function Expr()
    {
        return $this->BinaryOperator($this->SubExpr());
    }

    private function SubExpr()
    {
        $this->read();
        switch ($this->symbol) {
            case self::T_NEGATION:
                $SubExpr = new NegationPolicyRuleMatcher($this->SubExpr());
                break;
            case self::T_LEFT_PARENS:
                $SubExpr = $this->Expr();
                $this
                    ->accept(self::T_RIGHT_PARENS);
                break;
            case self::T_DOT:
                $this->read()
                    ->accept(self::T_STRING);
                $SubExpr = new AttributePolicyRuleMatcher($this->value);
                break;
            case self::T_STRING:
                $SubExpr = new StarPolicyRuleMatcher($this->value);
                break;
            default:
                $this->throwError($this->symbol);
        }
        return $SubExpr;
    }

    private function BinaryOperator(AbstractPolicyRuleMatcher $first)
    {
        $this->read();
        switch ($this->symbol) {
            case self::T_BOOLEAN_AND:
                return new AllOfPolicyRuleMatcher($first, $this->Expr());
            case self::T_BOOLEAN_OR:
                return new AnyOfPolicyRuleMatcher($first, $this->Expr());
            default:
                return $first;
        }
    }

}

/**
 * @internal
 */
abstract class AbstractPolicyRuleMatcher
{

    /**
     * Convert the given $value to a String 
     * 
     * @param mixed $value 
     * @return string
     */
    protected function asString($value)
    {
        if (is_object($value)) {
            return method_exists($value, '__toString') ? strval($value) : get_class($value);
        } else {
            return (String) $value;
        }
    }

    /**
     *
     * @param mixed $value 
     * @return mixed - boolean TRUE if $value matches, FALSE otherwise, or a string
     */
    abstract public function evaluate($value);

    /**
     * @codeCoverageIgnore
     * @return float
     */
    public function getSpecificity()
    {
        return 0.0;
    }

}

/**
 * Generic binary operator
 *
 * @internal
 */
abstract class BinaryOperatorRuleMatcher extends AbstractPolicyRuleMatcher
{

    /**
     *
     * @var AbstractPolicyRuleMatcher
     */
    protected $a;

    /**
     *
     * @var AbstractPolicyRuleMatcher
     */
    protected $b;

    /**
     *
     * @param array $a
     */
    public function __construct(AbstractPolicyRuleMatcher $a, AbstractPolicyRuleMatcher $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

}

/**
 * @internal
 */
class AnyOfPolicyRuleMatcher extends BinaryOperatorRuleMatcher
{

    public function evaluate($value)
    {
        $evA = $this->a->evaluate($value);
        $evB = $this->b->evaluate($value);
        return $evA || $evB;
    }

    /**
     * The MEAN of sub all matchers specificity
     * @return float
     */
    public function getSpecificity()
    {
        return min($this->a->getSpecificity(), $this->b->getSpecificity());
    }

}

/**
 * @internal
 */
class AllOfPolicyRuleMatcher extends BinaryOperatorRuleMatcher
{

    public function evaluate($value)
    {
        $evA = $this->a->evaluate($value);
        $evB = $this->b->evaluate($value);
        return $evA && $evB;
    }

    /**
     * The MAX of any sub matcher specificity
     * @return float
     */
    public function getSpecificity()
    {
        return max($this->a->getSpecificity(), $this->b->getSpecificity());
    }

}

/**
 * @internal
 */
class AttributePolicyRuleMatcher extends AbstractPolicyRuleMatcher
{

    private $methodName;

    /**
     *
     * @param string $methodName
     */
    public function __construct($methodName)
    {
        $this->methodName = $methodName;
    }

    public function evaluate($value)
    {
        if (is_object($value) && method_exists($value, $this->methodName)) {
            return $value->{$this->methodName}();
        }

        return FALSE;
    }

    public function getSpecificity()
    {
        return 0.5;
    }

}

/**
 * @internal
 */
class NegationPolicyRuleMatcher extends AbstractPolicyRuleMatcher
{

    /**
     *
     * @var AbstractPolicyRuleMatcher
     */
    private $inner;

    public function __construct(AbstractPolicyRuleMatcher $inner)
    {
        $this->inner = $inner;
    }

    public function evaluate($value)
    {
        return ! $this->inner->evaluate($value);
    }

    public function getSpecificity()
    {
        return 1.0 - $this->inner->getSpecificity();
    }

}

/**
 * @internal
 */
class StarPolicyRuleMatcher extends AbstractPolicyRuleMatcher
{

    /**
     *
     * @var string
     */
    private $pattern;

    /**
     *
     * @var string
     */
    private $regexPattern;
    private $stars;

    public function __construct($pattern)
    {
        $this->stars = 0;
        $this->pattern = $pattern;

        $regexPattern = preg_quote($pattern, '#');
        $regexPattern = str_replace('\*', '.*', $regexPattern, $this->stars);

        $this->regexPattern = '#^' . $regexPattern . '$#';
    }

    public function evaluate($value)
    {
        if ($this->stars === 0) {
            // exact match:
            return $this->asString($value) === $this->pattern;
        }

        return preg_match($this->regexPattern, $this->asString($value)) > 0;
    }

    public function getSpecificity()
    {
        if ($this->stars === 0) {
            return 1.0;
        } elseif ($this->pattern === '*') {
            return 0.0;
        }

        return 1.0 / (1.0 + (float) $this->stars);
    }

}

