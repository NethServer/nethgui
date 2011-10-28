<?php
/**
 * @package Module
 */

/**
 * @package Module
 */
class Nethgui_Module_Menu extends Nethgui_Core_Module_Standard
{

    /**
     *
     * @var RecursiveIterator
     */
    private $menuIterator;
    /**
     *
     * @var string Current menu item identifier
     */
    private $currentItem;

    public function __construct(RecursiveIterator $menuIterator, $currentItem)
    {
        parent::__construct();
        $this->menuIterator = $menuIterator;
        $this->currentItem = $currentItem;
    }

    /**
     * TODO
     * @param RecursiveIterator $rootModule
     * @return string
     */
    private function iteratorToHtml(RecursiveIterator $menuIterator, Nethgui_Renderer_Abstract $view, Nethgui_Renderer_WidgetInterface $widget, $level = 0)
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

    private function makeModuleAnchor(Nethgui_Renderer_Abstract $view, Nethgui_Core_ModuleInterface $module)
    {
        $itemView = new Nethgui_Core_View($module);

        $placeholders = array(
            '%HREF' => htmlspecialchars(Nethgui_Framework::getInstance()->buildModuleUrl($module, '')),
            '%CONTENT' => htmlspecialchars($itemView->translate($module->getTitle())),
            '%TITLE' => htmlspecialchars($itemView->translate($module->getDescription())),
        );

        if($module->getIdentifier() == $this->currentItem) {
            $placeholders['%CLASS']='currentMenuItem';
            $tpl = '<a href="%HREF" title="%TITLE" class="%CLASS">%CONTENT</a>';
        } else {
            $tpl = '<a href="%HREF" title="%TITLE">%CONTENT</a>';
        }
        return $view->literal(strtr($tpl, $placeholders))->setAttribute('hsc', FALSE);
    }

    public function renderModuleMenu(Nethgui_Renderer_Abstract $view)
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


        return $view->form()->setAttribute('method','get')->insert($view->textInput("search",$view::LABEL_NONE)->setAttribute('placeholder',$view->translate('Search')."..."))->insert($view->button("submit",$view::BUTTON_SUBMIT))->insert($rootList);
    }

    private function iteratorToSearch(RecursiveIterator $menuIterator, &$tags = array())
    {
        $menuIterator->rewind();

        while ($menuIterator->valid()) {

            $module = $menuIterator->current();
 
            list($key,$value) = each($module->getTags(Nethgui_Framework::getInstance()));
            if($key) { 
                $tags[$key] =$value;
            }
 
            if ($menuIterator->hasChildren()) {
                $this->iteratorToSearch($menuIterator->getChildren(), $tags); 
            } 

            $menuIterator->next();
        }
        return $tags;
    }



    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if ($mode === self::VIEW_SERVER) {
            $view->setTemplate(array($this, 'renderModuleMenu'));
        }

        $request = $this->getRequest();
        if(is_null($request) || $mode != self::VIEW_CLIENT)
            return;

        $action = array_shift($request->getArguments());
        if(!$action) { //search
           $tmp = $this->iteratorToSearch($this->menuIterator);
           #$view['tags'] = array_values(array_unique(explode(" ", strtolower($tmp))));
           $view['tags'] = $tmp;
        }

    }
}
