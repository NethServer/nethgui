<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>
    <head>
        <title>NethGui</title>
        <link rel="stylesheet" type="text/css" href="<?php echo $parameters['cssMain']; ?>" />
        <?php foreach ($parameters['js'] as $scriptPath): ?>
            <script type="text/javascript" src="<?php echo $scriptPath ?>" ></script>
        <?php endforeach ?>
    </head>
    <body>
        <div id="allWrapper">
            <div id="header">NethGui</div>
            <div class="colmask leftmenu">
                <div class="colleft">
                    <div class="col1">
                        <div id="breadcrumbMenu"><?php echo $view['BreadCrumb']->render() ?></div>
                        <pre style="background: yellow" id="validationReport"><?php foreach($view['ValidationReport']['errors'] as $error) {
                            echo $error[1] . " ({$error[0]})\n";
                        } ?></pre>
                        <div id="moduleContent"><?php echo $view[$view['currentModule']]->render() ?></div>
                    </div>
                    <div class="col2"><div id="moduleMenu"><?php echo $view['Menu']->render() ?></div></div>
                </div>
            </div>
            <div id="footer">
                Powered by <a href="/Documentation">NethGuiFramework</a> &ndash; Copyright 2011 &copy; Nethesis S.r.l
            </div>
        </div>
        <pre><?php echo '$request' ?></pre>
    </body>
</html>
