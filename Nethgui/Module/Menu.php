<?php
namespace Nethgui\Module;

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
 */
class Menu extends \Nethgui\Core\Module\Standard
{

    /**
     *
     * @var \RecursiveIterator
     */
    private $menuIterator;

    /**
     *
     * @var string Current menu item identifier
     */
    private $currentItem;

    public function __construct(\RecursiveIterator $menuIterator, $currentItem)
    {
        parent::__construct();
        $this->menuIterator = $menuIterator;
        $this->currentItem = $currentItem;
    }

    /**
     * TODO
     * @param \RecursiveIterator $rootModule
     * @return string
     */
    private function iteratorToHtml(\RecursiveIterator $menuIterator, \Nethgui\Renderer\Xhtml $view, \Nethgui\Renderer\WidgetInterface $widget, $level = 0)
    {
        if ($level > 4) {
            return $widget;
        }

        $menuIterator->rewind();

        while ($menuIterator->valid()) {

            $module = $menuIterator->current();

            $widget->insert($this->makeModuleAnchor($view, $module));

            if ($menuIterator->hasChildren()) {
                $childWidget = $view->elementList()->setAttribute('class', FALSE);
                $this->iteratorToHtml($menuIterator->getChildren(), $view, $childWidget, $level + 1);
                $widget->insert($childWidget);
            }

            $menuIterator->next();
        }

        return $widget;
    }

    private function makeModuleAnchor(\Nethgui\Renderer\Xhtml $view, \Nethgui\Core\ModuleInterface $module)
    {
        $translator = $view->getTranslator();

        $placeholders = array(
            '%HREF' => htmlspecialchars($view->getModuleUrl('../' . $module->getIdentifier())),
            '%CONTENT' => htmlspecialchars($translator->translate($module, $module->getTitle())),
            '%TITLE' => htmlspecialchars($translator->translate($module, $module->getDescription())),
        );

        if ($module->getIdentifier() == $this->currentItem) {
            $placeholders['%CLASS'] = 'currentMenuItem';
            $tpl = '<a href="%HREF" title="%TITLE" class="%CLASS">%CONTENT</a>';
        } else {
            $tpl = '<a href="%HREF" title="%TITLE">%CONTENT</a>';
        }
        return $view->literal(strtr($tpl, $placeholders))->setAttribute('hsc', FALSE);
    }

    public function renderModuleMenu(\Nethgui\Renderer\Xhtml $view)
    {
        $rootList = $view->elementList()->setAttribute('wrap', '/');

        $this->menuIterator->rewind();

        while ($this->menuIterator->valid()) {

            if ($this->menuIterator->hasChildren()) {
                // Add category title with fake module
                $rootList->insert(
                    $view->panel()
                        ->setAttribute('class', 'moduleTitle')
                        ->insert($view->literal($view->translate($this->menuIterator->current()->getTitle()))->setAttribute('hsc', TRUE))
                );

                // Add category contents:
                $childWidget = $view->elementList()->setAttribute('class', FALSE);
                $this->iteratorToHtml($this->menuIterator->getChildren(), $view, $childWidget);
                $rootList->insert($childWidget);
            }

            $this->menuIterator->next();
        }

        $form = $view->form()->setAttribute('method', 'get')->insert($view->textInput("search", $view::LABEL_NONE)->setAttribute('placeholder', $view->translate('Search') . "..."))->insert($view->button("submit", $view::BUTTON_SUBMIT))->insert($rootList);

        return "<div class=\"Navigation Flat " . $view->getClientEventTarget("tags") . "\">$form</div>";
    }

    private function iteratorToSearch(\RecursiveIterator $menuIterator, &$tags = array())
    {
        $menuIterator->rewind();

        while ($menuIterator->valid()) {

            $module = $menuIterator->current();

            list($key, $value) = @each($module->getTags());
            if ($key) {
                $tags[$key] = $value;
            }

            if ($menuIterator->hasChildren()) {
                $this->iteratorToSearch($menuIterator->getChildren(), $tags);
            }

            $menuIterator->next();
        }
        return $tags;
    }

    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if ($mode === self::VIEW_SERVER) {
            $view->setTemplate(array($this, 'renderModuleMenu'));
        } elseif ($mode === self::VIEW_CLIENT) {
            $request = $this->getRequest();
            if (is_null($request)) {
                return;
            }
            $action = \Nethgui\array_head($request->getArguments());
            if ( ! $action) { //search
                $tmp2 = array();
                $tmp = $this->iteratorToSearch($this->menuIterator);
                foreach ($tmp as $url => $tags) {
                    $it = new \RecursiveIteratorIterator(new RecursiveArrayIterator($tags));
                    foreach ($it as $v) {
                        $tmp2[$url][] = $v;
                    }
                }
                $view['tags'] = $tmp2;
            }
        }
    }

}
