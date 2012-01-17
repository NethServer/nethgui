<?php
namespace Nethgui\Module\Notification;

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
 * TODO: add component description here
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
abstract class AbstractNotification implements \Serializable, \Nethgui\View\ViewableInterface
{
    const NOTIFY_SUCCESS = 0x0;
    const NOTIFY_WARNING = 0x1;
    const NOTIFY_ERROR = 0x2;
    const MASK_SEVERITY = 0x3;
    const NOTIFY_MODAL = 0x4;

    private $identifier, $dismissed, $style, $type, $transient;

    public function __construct($style = 0, $type = NULL, $transient = TRUE)
    {
        $this->style = $style;
        $this->type = is_string($type) ? $type : \Nethgui\array_end(explode('\\', get_class($this)));
        $this->transient = $transient === TRUE;
        $this->dismissed = FALSE;
    }

    public function getStyle()
    {
        return $this->style;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isTransient()
    {
        return $this->transient !== FALSE;
    }

    public function getIdentifier()
    {
        if (is_null($this->identifier)) {
            $this->identifier = 'dlg' . substr(md5($this->serialize() . microtime()), 0, 6);
        }

        return $this->identifier;
    }

    public function dismiss()
    {
        $this->dismissed = TRUE;
    }

    public function isDismissed()
    {
        return $this->dismissed === TRUE;
    }

    public function serialize()
    {
        return serialize(array($this->identifier, $this->dismissed, $this->style, $this->type, $this->transient));
    }

    public function unserialize($serialized)
    {
        list($this->identifier, $this->dismissed, $this->style, $this->type, $this->transient) = unserialize($serialized);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        $view->setTemplate(array($this, 'renderXhtml'));
        $view['identifier'] = $this->identifier;
        $view['dismissed'] = $this->dismissed;
        $view['style'] = $this->style;
        $view['type'] = $this->type;
        $view['transient'] = $this->transient;
        $view['message'] = '';
    }

    public function renderXhtml(\Nethgui\Renderer\Xhtml $renderer)
    {
        if ($this->getStyle() & (\Nethgui\Module\Notification\AbstractNotification::NOTIFY_ERROR | \Nethgui\Module\Notification\AbstractNotification::NOTIFY_WARNING)) {
            $styleClass = 'ui-state-error';
        } else {
            $styleClass = 'ui-state-success';
        }

        if ($this->isTransient()) {
            $styleClass .= ' transient';
        }

        return $renderer->panel()->setAttribute('class', sprintf('%s %s', $this->getType(), $styleClass))->setAttribute('receiver', $this->getIdentifier());
    }

}