<?php
/**
 * @package Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Module
 */
class NethGui_Module_Notification extends NethGui_Core_Module_Standard implements NethGui_Core_TopModuleInterface
{    
     private $user;
    
     public function initialize()
     {
         parent::initialize();
         $this->declareParameter('answerId');
     }
     
     public function bind(NethGui_Core_RequestInterface $request)
     {
         parent::bind($request);
         
         $this->user = $request->getUser();
     }
    
    
     public function process(NethGui_Core_NotificationCarrierInterface $carrier)
     {
         parent::process($carrier);
                          
         $answerId = $this->parameters['answerId'];
         
         // TODO: remove $answerId from $this->user session
     }
     
   public function getParentMenuIdentifier()
    {
        return NULL;
    }
         
}
