<?php
namespace Nethgui\Renderer;

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
 * Enanches the abstract renderer with the wiget factory interface
 *
 * Fragments of the view string representation can be generated through the widget objects
 * returned by the factory interface.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
class Xhtml extends TemplateRenderer implements \Nethgui\Core\DelegatingCommandReceiverInterface
{

    /**
     * @var \Nethgui\Core\CommandReceiverInterface
     */
    private $commandReceiver;

    /**
     *
     * @param \Nethgui\Core\ViewInterface $view
     * @param callback $templateResolver A function that takes the template name as argument and returns the corresponding PHP script absolute path
     * @param int $inheritFlags Default flags applied to all widgets created by this renderer
     * @param \Nethgui\Core\CommandReceiverInterface $delegatedCommandReceiver object where Commands are executed
     */
    public function __construct(\Nethgui\Core\ViewInterface $view, $templateResolver, $inheritFlags, \Nethgui\Core\CommandReceiverInterface $delegatedCommandReceiver)
    {
        parent::__construct($view, $templateResolver, $inheritFlags);
        $this->commandReceiver = new HttpCommandReceiver($this->view, $delegatedCommandReceiver);
    }

    /**
     *
     * @param \Nethgui\Core\ViewInterface $view
     * @return \Nethgui\Renderer\Xhtml
     */
    public function spawnRenderer(\Nethgui\Core\ViewInterface $view)
    {
        return new static($view, $this->getTemplateResolver(), $this->getDefaultFlags(), $this->commandReceiver);
    }
    
    protected function render()
    {
        $output = parent::render();

        /**
         * Search for any non-executed command and invoke execute() on it.
         */
        foreach ($this->view as $command) {
            if ( ! $command instanceof \Nethgui\Core\CommandInterface) {
                continue;
            }
            if ( ! $command->isExecuted()) {
                $command->setReceiver($this)->execute();
            }
        }

        return $output;
    }

    protected function createWidget($widgetType, $attributes = array())
    {
        $className = 'Nethgui\Widget\Xhtml\\' . ucfirst($widgetType);

        $o = new $className($this);

        foreach ($attributes as $aname => $avalue) {
            $o->setAttribute($aname, $avalue);
        }

        return $o;
    }

    public function getDelegatedCommandReceiver()
    {
        return $this->commandReceiver;
    }

    public function executeCommand($name, $arguments)
    {
        return $this->getDelegatedCommandReceiver()->executeCommand($name, $arguments);
    }

}
