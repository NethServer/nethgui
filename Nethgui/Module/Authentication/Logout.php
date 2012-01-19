<?php
namespace Nethgui\Module\Authentication;

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
 * Logs out the currently authenticated user
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Logout extends \Nethgui\Controller\AbstractController
{

    public function process()
    {
        $request = $this->getRequest();
        if ($request->isSubmitted()) {
            $request->getUser()->setAuthenticated(FALSE);
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        $view->setTemplate(function(\Nethgui\Renderer\Xhtml $renderer) {

                // return empty string on parent::renderIndex()
                if ($renderer->getDefaultFlags() & $renderer::STATE_UNOBSTRUSIVE) {
                    $renderer->rejectFlag($renderer::INSET_FORM | $renderer::INSET_WRAP);
                    return '';
                }

                $renderer->requireFlag($renderer::INSET_FORM);
                return $renderer->buttonList()
                        ->insert($renderer->button('Logout', $renderer::BUTTON_SUBMIT));
            });
    }

    public function nextPath()
    {
        return '/';
    }

}