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

        // Transfer validation errors to view
        $view['validationErrors'] = new ArrayObject();

        foreach ($this->errors as $error) {
            list($fieldName, $errorInfo, $module) = $error;

            $errorView = $view->spawnView($module);
            $errorView->setTemplate('NethGui_Template_ValidationError');
            $errorView['errorInfo'] = array($errorView->translate($errorInfo[0]), $errorInfo[1]);
            $errorView['fieldName'] = $fieldName;
            $errorView['fieldId'] = $errorView->getUniqueId($fieldName);
            $errorView['fieldLabel'] = $errorView->translate($fieldName . '_label');

            $view['validationErrors'][] = $errorView;
        }


        if (count($this->errors) == 1) {
            $view['validationLabel'] = $view->translate('Incorrect value');
        } elseif (count($this->errors) > 1) {
            $view['validationLabel'] = $view->translate('Incorrect values');
        }


        $dialogBoxList = $this->user->getDialogBoxes();

        // Transfer dialog data to view
        $view['dialogs'] = new ArrayObject();

        foreach ($dialogBoxList as $dialog) {
            $dialogView = $view->spawnView($dialog->getModule());

            $message = $dialog->getMessage();

            $dialogView->copyFrom(
                array(
                    'dialogId' => $dialog->getId(),
                    'type' => $dialog->getType(),
                    'message' => $dialogView->translate($message[0], $message[1]),
                    'transient' => $dialog->isTransient(),
                    'actions' => $this->makeActionViewsForDialog($dialog, $mode, $dialogView),
            ));
            $dialogView->setTemplate('NethGui_Template_NotificationAreaDialogBox');
            $view['dialogs'][] = $dialogView;
        }
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
            $view->button($view['name'], NethGui_Renderer_Abstract::BUTTON_LINK, $view['location']);
        } else {
            // render as form
            $form = $view->form($view['location'], 0, 'NotificationDialog_Action_' . $view['name']);
            $form->inset('dismissView');
            $form->hidden('data');
            $form->button($view['name'], NethGui_Renderer_Abstract::BUTTON_SUBMIT);
        }

        return $view;
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