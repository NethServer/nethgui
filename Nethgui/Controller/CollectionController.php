<?php
namespace Nethgui\Controller;

/*
 * Copyright (C) 2013 Nethesis S.r.l.
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
 * @since 1.0
 */
class CollectionController extends \Nethgui\Controller\CompositeController implements \Nethgui\Adapter\AdapterAggregateInterface
{
    /**
     *
     * @var \Nethgui\Controller\Collection\ActionInterface
     */
    private $indexAction = NULL;

    /**
     * @var array
     */
    private $elementActions = array();

    /**
     * @var array
     */
    private $collectionActions = array();

    /**
     *
     * @var \Nethgui\Adapter\AdapterInterface
     */
    private $adapter = NULL;

    /**
     * Set the adapter object that gives access to the database collection
     *
     * @param \Nethgui\Adapter\AdapterInterface $adapter
     * @return \Nethgui\Controller\CollectionController
     */
    protected function setAdapter(\Nethgui\Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function hasAdapter()
    {
        return $this->adapter instanceof \Nethgui\Adapter\AdapterInterface;
    }

    /**
     * @return \Nethgui\Controller\Collection\ActionInterface
     */
    protected function getIndexAction()
    {
        return $this->indexAction;
    }

    /**
     *
     * @param \Nethgui\Controller\Collection\ActionInterface $action
     * @return \Nethgui\Controller\CollectionController
     * @throws \LogicException
     */
    protected function setIndexAction(\Nethgui\Controller\Collection\ActionInterface $action)
    {
        if ($this->indexAction !== NULL) {
            throw new \LogicException(sprintf("%s: the index action has been already set. Call this method before getIndexAction() invocation.", __CLASS__), 1373448488);
        }

        $this->indexAction = $action;
        $this->addChild($this->indexAction);
        return $this;
    }

    public function initialize()
    {
        if ( ! $this->hasAdapter()) {
            throw new \LogicException(sprintf('%s: you must call setAdapter() before %s::initialize()', get_class($this), __CLASS__), 1373448489);
        }

        // propagate the adapter to every that is missing it:
        foreach ($this->getChildren() as $childAction) {
            if ($childAction instanceof \Nethgui\Controller\Collection\ActionInterface && ! $childAction->hasAdapter()) {
                $childAction->setAdapter($this->getAdapter());
            }
        }

        /**
         * Calling the parent method at this point ensures that the collection
         * adapter has been set BEFORE the child initialization
         */
        parent::initialize();
    }

    /**
     * Add $childAction to the controller, and propagate the adapter to the child,
     * if it has not been done yet.
     *
     * @param \Nethgui\Module\ModuleInterface $childAction
     */
    public function addChild(\Nethgui\Module\ModuleInterface $childAction)
    {
        if ($childAction instanceof \Nethgui\Controller\Collection\ActionInterface) {
            if ($childAction instanceof \Nethgui\Controller\Collection\ActionInterface && ! $childAction->hasAdapter()) {
                $childAction->setAdapter($this->getAdapter());
            }
        }
        return parent::addChild($childAction);
    }

    /**
     * A row action is executed in a row context (i.e. row updating, deletion...)
     *
     * @see getRowActions()
     * @param \Nethgui\Controller\Collection\ActionInterface $action
     * @return \Nethgui\Controller\CollectionController
     */
    public function addElementAction(\Nethgui\Controller\Collection\ActionInterface $action)
    {
        $this->elementActions[] = $action;
        $this->addChild($action);
        return $this;
    }

    /**
     * Actions for a single element of the collection
     * @return array
     */
    public function getElementActions()
    {
        return $this->elementActions;
    }

    /**
     * A collection-wide action involves the whole collection (i.e. create
     * a new element, print the collection...) or a subset of it.
     *
     * @see getCollectionActions()
     * @return \Nethgui\Controller\CollectionController
     */
    public function addCollectionAction(\Nethgui\Module\ModuleInterface $a)
    {
        $this->collectionActions[] = $a;
        $this->addChild($a);
        return $this;
    }

    /**
     * Actions for the whole collection
     * @return array
     */
    public function getCollectionActions()
    {
        return $this->collectionActions;
    }

    public function renderIndex(\Nethgui\Renderer\Xhtml $renderer)
    {
        $indexId = $this->getIndexAction()->getIdentifier();

        // Make sure Index action is the first
        $this->sortChildren(function(\Nethgui\Module\ModuleInterface $a, \Nethgui\Module\ModuleInterface $b) use ($indexId) {
                if ($a->getIdentifier() === $indexId) {
                    return -1;
                } elseif ($b->getIdentifier() === $indexId) {
                    return 1;
                }
                return 0;
            });

        return parent::renderIndex($renderer);
    }

}