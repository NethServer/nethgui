<?php
$bootstrapJs = <<<'EOJS'
jQuery(document).ready(function($) {
    $('script.unobstrusive').remove();
    $('#CurrentModule').Component();
    $('.Navigation').Navigation();
    $('#allWrapper').css('display', 'block');
    $('.HelpArea').HelpArea();
});
EOJS;

$view
    // Javascript:
    ->useFile('js/jquery-1.6.2.min.js')
    ->useFile('js/jquery-ui-1.8.16.custom.min.js') //->useFile('js/jquery-ui.js')
    ->useFile('js/jquery.dataTables.min.js')
    ->useFile('js/jquery.qtip.min.js')
    ->useFile(sprintf('js/jquery.ui.datepicker-%s.js', $view['lang']))
    //->useFile('js/nethgui.js')
    ->includeFile('jquery.nethgui.js')
    ->includeFile('jquery.nethgui.controller.js')
    ->includeFile('jquery.nethgui.loading.js')
    ->includeFile('jquery.nethgui.helparea.js')
    ->includeJavascript($bootstrapJs)
    // CSS:
    ->useFile('css/default/jquery-ui-1.8.16.custom.css')
    ->useFile('css/jquery.qtip.min.css')
    ->useFile('css/base.css')
    ->useFile('css/blue.css')
;

$currentModule = $view[$view['currentModule']]->getModule();
$moduleTitle = $view->getTranslator()->translate($currentModule, $currentModule->getAttributesProvider()->getTitle());
$pageTitle = $view['company'] . " - " . $moduleTitle;
$pathUrl = $view->getPathUrl();

$HelpArea = $view->panel()
    ->setAttribute('class', 'HelpArea')
    ->insert(
    $view->panel()
    ->setAttribute('class', 'wrap')
    ->insert(
        $view->elementList($view::BUTTONSET)->insert($view->button('Hide', $view::BUTTON_CANCEL))
    )
);

// Must render CurrentModule before NotificationArea to catch notifications
if ($currentModule instanceof \Nethgui\Core\Module\Standard) {
    $currentModuleOutput = (String) $view->inset($view['currentModule'], $view::INSET_FORM | $view::INSET_WRAP)->setAttribute('class', 'Action');
} else {
    $currentModuleOutput = (String) $view->inset($view['currentModule']);
}
$menuOutput = $view->inset('Menu');

?><!DOCTYPE html>
<html lang="<?php echo $view['lang'] ?>">
    <head>
        <title><?php echo $pageTitle ?></title>
        <link rel="icon"  type="image/png"  href="<?php echo $pathUrl . '/images/favicon.ico' ?>" />
        <meta name="viewport" content="width=device-width" />  
        <script>document.write('<style type="text/css">#allWrapper {display:none}</style>')</script>
        <?php echo $view->literal($view['Resource']['css']) ?>
    </head>
    <body>
        <div id="allWrapper">
            <?php echo $view->inset('Notification') ?>
            <div id="pageHeader">
                <a id="product" href="Dashboard" title='NethServer'></a>
                <h1 id="ModuleTitle"><?php echo $moduleTitle ?></h1>
                <div id="productTitle">NethServer</div>
            </div>
            <div id="pageContent">
                <div class="primaryContent" role="mainTask">
                    <div id="CurrentModule"><?php echo $currentModuleOutput ?></div>
                    <div id="footer"><p><?php echo htmlspecialchars($view['company'] . ' - ' . $view['address']) ?></p></div>
                </div>

                <div class="secondaryContent" role="otherTask">
                    <h2><?php echo htmlspecialchars($view->translate('Other modules')) ?></h2>
                    <?php echo $menuOutput ?>
                </div>
            </div>
            <?php echo $view->literal($HelpArea, $view::STATE_UNOBSTRUSIVE); ?>
        </div>
        <?php echo $view->literal($view['Resource']['js']) ?>
    </body>
</html>
