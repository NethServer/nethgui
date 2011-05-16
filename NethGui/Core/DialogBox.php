<?php
/**
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Core
 */
class NethGui_Core_DialogBox implements Serializable
{

    private $message;
    private $type;
    private $actions;
    private $module;

    public function __construct(NethGui_Core_ModuleSurrogate $module, $message, $actions = array(), $type = NethGui_Core_NotificationCarrierInterface::NOTIFY_SUCCESS)
    {
        $this->module = $module;
        $this->actions = $actions;
        $this->message =$message;
        $this->type = $type;
    }
       
    public function getActionViews(NethGui_Core_ModuleInterface $notificationModule)
    {
        $views = array();
        
        foreach($this->actions as $action) {
            $view = new NethGui_Core_View($this->module);
            
            $view['name'] = $action[0];
            $view['location'] = $action[1];            
            $view['data'] = $action[2];
            
            $view->setTemplate(array($this, 'renderDialog'));
            
            $notificationView = $view->spawnView($notificationModule, TRUE);            
            $notificationView['dismissDialog'] = $this->getId();
            $notificationView->setTemplate(array($this, 'renderNotification'));
            
            
            $views[] = $view;
        }
       
        return $views;
    }
        
    public function renderDialog(NethGui_Renderer_Abstract $view)
    {
        $form = $view->form($view['location'], 0, 'NotificationDialog_Action_' . $view['name']);

        $form->button($view['name'], NethGui_Renderer_Abstract::BUTTON_SUBMIT);
        $form->hidden($view['name'], 0, '1');

        foreach ($view['data'] as $parameterName => $parameterValue) {
            $form->hidden($parameterName, 0, $parameterValue);
        }
        
        $form->inset('NotificationArea');
        
        return $view;
    }    
    
    public function renderNotification(NethGui_Renderer_Abstract $view) {
        $view->hidden('dismissDialog');
        return $view;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isTransient()
    {
        return empty($this->actions);
    }
    
    public function getId() {
        return substr(md5($this->serialize()), 0, 6);
    }

    public function serialize()
    {
        return serialize(array($this->message, $this->actions, $this->type, $this->module));
    }

    public function unserialize($serialized)
    {
        $args = unserialize($serialized);
        
        $this->message = $args[0];
        $this->actions = $args[1];
        $this->type = $args[2];
        $this->module = $args[3];
    }

}