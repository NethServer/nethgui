<?php
/**
 * NethGui
 *
 * @package Module
 */

/**
 * Carries notification messages to the User.
 * 
 * Keeps persistent messages into User session.
 *
 * @package Module
 */
class NethGui_Module_NotificationArea extends NethGui_Core_Module_Standard implements NethGui_Core_ValidationReportInterface
{

    private $errors = array();

    /**
     *
     * @var NethGui_Core_UserInterface;
     */
    private $user;

    public function __construct(NethGui_Core_UserInterface $user)
    {
        parent::__construct(NULL);
        $this->user = $user;
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('dismissDialog', '/^[a-zA-Z0-9]+$/');
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);
        if ( ! $request->hasParameter('dismissDialog') && isset($_GET['dismissDialog'])) {
            $this->parameters['dismissDialog'] = $_GET['dismissDialog'];
        }
    }

    public function process()
    {
        parent::process();

        if ($this->parameters['dismissDialog'] != '') {
            $this->user->dismissDialogBox($this->parameters['dismissDialog']);
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        $view['notifications'] =  new ArrayObject();

        if ($this->hasValidationErrors()) {
            $this->prepareValidationErrorNotification($view, $mode);
        }

        $this->prepareDialogBoxesNotification($view, $mode);
        
    }

    private function prepareDialogBoxesNotification(NethGui_Core_ViewInterface $view, $mode)
    {
        foreach ($this->user->getDialogBoxes() as $dialog) {
            // Spawn a view associated to the $dialog original module:
            $dialogView = $view->spawnView($dialog->getModule());
            $dialogView->setTemplate('NethGui_Template_NotificationAreaDialogBox');
            $message = $dialog->getMessage();
            $dialogView->copyFrom(
                array(
                    'dialogId' => $dialog->getId(),
                    'type' => $dialog->getType(),
                    'message' => $dialogView->translate($message[0], $message[1]),
                    'transient' => $dialog->isTransient(),
                    'actions' => $this->makeActionViewsForDialog($dialog, $mode, $dialogView),
            ));
            $view['notifications'][] = $dialogView;
        }
    }

    private function prepareValidationErrorNotification(NethGui_Core_ViewInterface $view, $mode)
    {       
        $validationView = $view->spawnView($this);
        $validationView->setTemplate('NethGui_Template_ValidationError');

        if (count($this->errors) == 1) {
            $validationView['message'] = $view->translate('Incorrect value');
        } elseif (count($this->errors) > 1) {
            $validationView['message'] = $view->translate('Incorrect values');
        }

        $validationView['type'] = NethGui_Core_DialogBox::NOTIFY_ERROR;
        $validationView['dialogId'] = 'dlgValidation';
        $validationView['transient'] = TRUE;
        $validationView['errors'] = new ArrayObject();

        foreach ($this->errors as $index => $error) {
            list($fieldName, $errorInfo, $module) = $error;
            $eV = $validationView->spawnView($module);
            $eV->setTemplate(array($this, 'renderValidationError'));
            $eV['errorInfo'] = array($eV->translate($errorInfo[0]), $errorInfo[1]);
            $eV['fieldName'] = $fieldName;
            $eV['fieldId'] = $eV->getUniqueId($fieldName);
            $eV['fieldLabel'] = $eV->translate($fieldName . '_label');
            $validationView['errors'][] = $eV;
        }

        $view['notifications'][] = $validationView;
    }

    public function renderValidationError(NethGui_Renderer_Abstract $view) {
        return $view->button($view['fieldName'], NethGui_Renderer_Abstract::BUTTON_LINK)
            ->setAttribute('value', '#' . $view['fieldId'])
            ->setAttribute('title', $view->translate($view['errorInfo'][0], $view['errorInfo'][1]));
    }

    private function makeActionViewsForDialog(NethGui_Core_DialogBox $dialog, $mode, NethGui_Core_ViewInterface $dialogView)
    {
        $actionViews = new ArrayObject();

        foreach ($dialog->getActions() as $action) {
            $view = $dialogView->spawnView($dialog->getModule());

            if ($dialog->isTransient()) {
                $viewData = $action[2];
            } else {
                /*
                 * Merge the action data with the dismiss dialog commands:
                 * (note the starting `/` indicating an absolute path)
                 */
                $viewData = array_merge(
                    $action[2], array('/NotificationArea/dismissDialog' => $dialog->getId())
                );
            }

            if ($mode == self::VIEW_CLIENT) {
                // Translate the `location` in a URL for FORM action attribute
                $path = $view->getModulePath();
                $path[] = $action[1];
                $view['location'] = NethGui_Framework::getInstance()->buildUrl($path);
                $view['name'] = $view->translate($action[0] . '_label');
                $view['data'] = $this->prepareDialogDataForClient($view, $viewData);
            } else {
                $view['location'] = $action[1];
                $view['name'] = $action[0];
                $view['data'] = $viewData;
                $view->setTemplate(array($this, 'renderDialogAction'));
                $view['transient'] = $dialog->isTransient();
            }

            $actionViews[] = $view;
        }

        return $actionViews;
    }

    private function prepareDialogDataForClient(NethGui_Core_ViewInterface $view, $data, $prefix='')
    {
        $output = array();

        foreach ($data as $key => $value) {
            if (empty($prefix)) {
                $innerPrefix = $key;
            } else {
                $innerPrefix = $prefix . '/' . $key;
            }

            if ($value instanceof Traversable) {
                $value = iterator_to_array($value);
            }

            if (is_array($value)) {
                $output = array_merge($output, $this->prepareDialogDataForClient($value, $innerPrefix));
            } else {
                $controlName = $view->getControlName($innerPrefix);
                $output[$controlName] = strval($value);
            }
        }

        return $output;
    }

    public function renderDialogAction(NethGui_Renderer_Abstract $view)
    {
        if ($view['transient'] && count($view['data']) == 0) {
            // render as link
            $widget = $view->button($view['name'], NethGui_Renderer_Abstract::BUTTON_LINK)->setAttribute('value', $view['location']);
        } else {
            // render as form
            $widget = $view->form()
                ->setAttribute('action', $view['location'])
                ->setAttribute('name', 'NotificationDialogAction_' . $view['name'])                
                ->insert($view->hidden('data'))
                ->insert($view->button($view['name'], NethGui_Renderer_Abstract::BUTTON_SUBMIT));
        }

        return $widget;
    }

    public function addValidationError(NethGui_Core_ModuleInterface $module, $fieldId, $message)
    {
        $this->errors[] = array($fieldId, $message, $module);
    }

    public function hasValidationErrors()
    {
        return count($this->errors) > 0;
    }

    public function dismissTransientDialogBoxes()
    {
        foreach ($this->user->getDialogBoxes() as $dialog) {
            if ($dialog->isTransient()) {
                $this->user->dismissDialogBox($dialog->getId());
            }
        }
    }

}