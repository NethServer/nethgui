<?php
namespace Nethgui\Test\Tool;

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
 * TODO: write description here
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class MockState
{
    // FIXME: substitute with a state-chain hash
    private $state = array();
    private $transitions = array();
    private $id;
    private $finalState = FALSE;

    public function __construct($id = 0)
    {
        $this->id = $id;
    }

    public function setFinal($isFinal = TRUE)
    {
        $this->finalState = (bool) $isFinal;
    }

    public function isFinal()
    {
        return $this->finalState === TRUE;
    }

    private function signature($h)
    {
        return md5(serialize($h));
    }

    /**
     * Switch to a new state if the given condition occurs.
     *
     * @param mixed $cond
     * @param mixed $v The return value of the $cond call
     * @return Test\Tool\MockState The new state
     */
    public function transition($cond, $v = NULL)
    {
        $signature = $this->signature($cond);

        $t = clone $this;
        $t->transitions = array();
        $t->id = sprintf('%s.%d', $this->id, count($this->transitions));

        $this->transitions[$signature] = $t;
        $this->set($cond, $v);
        return $t;
    }

    /**
     * Get the given return value when the given condition occurs.
     * 
     * @param mixed $cond
     * @param mixed $v
     * @return Test\Tool\MockState The current state
     */
    public function set($cond, $v)
    {
        $signature = $this->signature($cond);
        $this->state[$signature] = $v;
        return $this;
    }

    protected function format($a)
    {
        return preg_replace("/(\n| )+/", " ", json_encode($a));
    }

    /**
     * Execute an expression.
     *
     * If the given expression brings to a new state, the new state is returned.
     * Otherwise the same object is returned.
     *
     * @param mixed $exp
     * @param mixed $output
     * @return \Test\Tool\MockState The next object state, or the object itself.
     */
    public function exec($exp, &$output)
    {
        $signature = $this->signature($exp);

        $stateFound = FALSE;

        if (array_key_exists($signature, $this->state)) {
            $output = $this->state[$signature];
            $stateFound = TRUE;
        }

        if (array_key_exists($signature, $this->transitions)) {
            // If $signature is a transition, return the next state 
            // and optionally forward the exec() call on that state.     
            return $this->transitions[$signature];
        } elseif ($stateFound === FALSE) {
            $info = $this->format($exp);
            throw new \PHPUnit_Framework_ExpectationFailedException("State {$this->id}. Neither state nor transition found for condition {$info}.");
        }

        return $this;
    }

    public function call()
    {
        $args = func_get_args();
        if (count($args) == 0) {
            throw new \InvalidArgumentException('Provide at least one parameter');
        }
        $methodName = array_shift($args);
        return array($methodName, $args);
    }

    public function __toString()
    {
        return sprintf("<MockState %s>", $this->id);
    }

}
