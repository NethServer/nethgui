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
 * Objects implementing this interface are injected with required Models, by
 * invoking the returned setters methods.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.6
 */
interface DependencyConsumer
{
    /**
     * The returned hash is indexed by the model class name and has a callable
     * objects as value.  Each callable is invoked with the model instance as
     * only argument.
     *
     * @return array
     */
    public function getDependencySetters();
}