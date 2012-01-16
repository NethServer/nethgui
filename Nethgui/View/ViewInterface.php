<?php
namespace Nethgui\View;

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
 * Each module receives a view object in prepareView() operation. A view
 * contains three types of elements:
 *
 * - primitive types (int, string, array, boolean..)
 *
 * - ViewInterface
 *
 * - CommandInterface
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface ViewInterface extends \ArrayAccess, \IteratorAggregate
{
    const TARGET_XHTML = 'xhtml';
    const TARGET_JSON = 'json';

    /**
     * Set the template to be applied to the view
     *
     * - If a string is given, it identifies a PHP Template script
     *   (ie. Nethgui\Template\Main);
     *
     * - If a callback function is given, it is invoked with an array
     *   representing the view state as argument and is expected to return
     *   a string representing the view;
     *
     * - If a boolean FALSE, is given the view is rendered as an empty string.
     *
     * @param string|callback|boolean $template The template converting the view state to a string
     * @return ViewInterface
     * @api
     */
    public function setTemplate($template);

    /**
     * TODO
     *
     * @see setTemplate()
     * @return string|callback|boolean
     * @api
     */
    public function getTemplate();

    /**
     * Assign data to the View state.
     *
     * @param Traversable $data
     * @return void
     * @api
     */
    public function copyFrom($data);

    /**
     * Create a new view object associated to $module
     *
     * @api
     * @param \Nethgui\Module\ModuleInterface $module The associated $module
     * @param boolean Optional If TRUE the returned view is added to the current object with key equal to the module identifier
     * @return \Nethgui\View\ViewInterface The new view object, of the same type of the actual.
     */
    public function spawnView(\Nethgui\Module\ModuleInterface $module, $register = FALSE);

    /**
     * The module associated to this view.
     *
     * @api
     * @return \Nethgui\Module\ModuleInterface
     */
    public function getModule();

    /**
     * Gets the array of the current module identifier plus all identifiers of
     * the ancestor modules, starting from the root.   
     *
     * @see ModuleInterface::getParent()
     * @see ModuleInterface::getIdentifier()
     * @return array
     * @api
     */
    public function getModulePath();

    /**
     * Obtain the complete path list, starting from root.
     *
     * An heading '/' character treat the $path as absolute, otherwise the
     * $path is considered relative to the current module and a '..' substring
     * goes one level up.
     *
     * @see getModulePath()
     * @see getModule()
     *
     * @param string $path The path
     * @return array The path parts, starting from root
     * @api
     */
    public function resolvePath($path);

    /**
     * Return an absolute url path.
     *
     * @see resolvePath()
     * @param string $path Relative to the current module
     * @return string
     * @api
     */
    public function getModuleUrl($path = '');

    /**
     * The web site URL without trailing slash
     * 
     * @example http://www.example.org:8080
     * @return string
     * @api
     */
    public function getSiteUrl();

    /**
     * The path component of an URL with a leading slash
     * 
     * @example /my/path/to/the/app
     * @return string
     * @api
     */
    public function getPathUrl();

    /**
     * Generate a unique identifier for the given $path. If no parts are given
     * the identifier refers the the module referenced by the view.
     *
     * @param string $path Relative to the current module
     * @return string
     * @api
     */
    public function getUniqueId($path = '');

    /**
     * Get the target control identifier for the given view member
     * 
     * @see #358
     * @param string $name
     * @return string
     * @api
     */
    public function getClientEventTarget($name);

    /**
     * A method to translate a message according to the user language preferences.
     *
     * @param string $value
     * @param array $args
     * @return string
     * @see TranslatorInterface::translate()
     * @see getTranslator()
     * @api
     */
    public function translate($message, $args = array());

    /**
     * Access to the object performing string translations
     *
     * @api
     * @see translate()
     * @return \Nethgui\View\TranslatorInterface
     */
    public function getTranslator();

    /**
     * Get a command list for the given selector
     *
     * The returned command list is ready to execute().
     *
     * @api
     * @param string $selector Routes the command list to the receiver appointed by this parameter.
     * @return \Nethgui\View\ViewCommandInterface
     */
    public function getCommandList($selector = '');

    /**
     * Check if one or more commands has been added to the selected command list
     *
     * @api
     * @return boolean TRUE, if some command has been added to the list
     */
    public function hasCommandList($selector = '');

    /**
     * Get the requested resource format
     *
     * @api
     * @return string
     */
    public function getTargetFormat();
}
