<?php
namespace Nethgui\Component;
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
 * Inject dependencies into a target object.
 *
 * A bag of dependencies and callback functions. All callbacks
 * are invoked with the target $object and the bag itself.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.6
 * @api
 */
interface DependencyInjectorInterface extends \ArrayAccess {
    /**
     * @param object $object The object to initialize
     * @return \Nethgui\Component\DependencyInjectorInterface
     */
    public function inject($object);

    /**
     * Create an object of the given class, injecting dependencies into it.
     *
     * The class constructor is invoked with the arguments.
     * 
     * @param string $className
     * @param array $constructorArgs
     * @return an object of class $className
     */
    public function create($className, $constructorArgs = array());

    /**
     *
     * @param callable $callback
     * @param string $name
     */
    public function setInjector($callback, $name = NULL);
}
