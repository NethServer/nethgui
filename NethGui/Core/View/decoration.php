<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>
    <head>
        <title>NethGui</title>
        <link rel="stylesheet" type="text/css" href="<?php echo $cssMain; ?>" />
        <?php foreach ($js as $scriptPath): ?>
            <script type="text/javascript" src="<?php echo $scriptPath ?>" ></script>
        <?php endforeach ?>
    </head>
    <body>
        <div id="allWrapper">
            <div id="header">NethGui</div>
            <div class="colmask leftmenu">
                <div class="colleft">
                    <div class="col1">
                        <div id="breadcrumbMenu"><?php echo $breadcrumbMenu ?></div>
                        <pre id="validationReport">Validation report: <?php echo $validationReport ?></pre>
                        <div id="moduleContent"><?php echo $moduleContent ?></div>
                    </div>
                    <div class="col2"><div id="moduleMenu"><?php echo $moduleMenu ?></div></div>
                </div>
            </div>
            <div id="footer">
                Powered by <a href="<?php echo site_url('../doc') ?>">NethGuiFramework</a> &ndash; Copyright 2011 &copy; Nethesis S.r.l
            </div>
        </div>
        <pre><?php echo $request ?></pre>
    </body>
</html>
