<?php
namespace Nethgui\Module\Help;

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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Show extends Common
{

    public function prepareView(\Nethgui\Core\ViewInterface $view)
    {
        parent::prepareView($view);
        if (is_null($this->getTargetModule())) {
            $view->setTemplate(array($this, 'renderIndex'));
        } else {
            $view->setTemplate(array($this, 'renderDocument'));
        }
    }

    public function renderIndex(\Nethgui\Renderer\Xhtml $renderer)
    {
        $moduleList = $renderer->elementList();

        $templateList = $renderer->elementList();

        $translator = $renderer->getTranslator();
        $renderer->rejectFlag($renderer::INSET_FORM);

        foreach ($this->getModuleSet() as $module) {

            // skip Help module:
            if ($module === $this->getParent()) {
                continue;
            }

            $template = '<a href="%URL">%LABEL</a>';
            $args1 = array('%URL' => $renderer->getModuleUrl($module->getIdentifier()) . '.html', '%LABEL' => $translator->translate($module, $module->getAttributesProvider()->getTitle()));
            $args2 = array('%URL' => $renderer->getModuleUrl('../Template/' . $module->getIdentifier()) . '.html', '%LABEL' => $translator->translate($module, $module->getAttributesProvider()->getTitle()));
            $moduleList->insert($renderer->literal(strtr($template, $args1)));
            $templateList->insert($renderer->literal(strtr($template, $args2)));
        }

        return $renderer->columns()
                ->insert($renderer->fieldset()->setAttribute('template', $renderer->translate('Documents'))->insert($moduleList))
                ->insert($renderer->fieldset()->setAttribute('template', $renderer->translate('Templates'))->insert($templateList))
        ;
    }

    public function renderDocument(\Nethgui\Renderer\Xhtml $renderer)
    {
        $filePath = $this->getHelpDocumentPath($this->getTargetModule());
        $renderer->rejectFlag($renderer::INSET_FORM);

        $document = new \XMLReader();
        if ($document->open('file://' . $filePath, 'utf-8', LIBXML_NOENT) !== TRUE) {
            return FALSE;
        }

        // Advance to BODY tag:
        while ($document->name != 'body' && $document->read());
        while ($document->name != 'div' && $document->read());

        $content = $document->readInnerXml();

        return $renderer->panel()->setAttribute('class', 'HelpDocument')->insert($renderer->literal($content));
    }

}
