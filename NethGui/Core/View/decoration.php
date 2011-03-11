<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>
    <head>
        <title>NethGui</title>
        <link rel="stylesheet" type="text/css" href="<?php echo $parameter['cssMain']; ?>" />
        <?php foreach ($parameter['js'] as $scriptPath): ?>
            <script type="text/javascript" src="<?php echo $scriptPath ?>" ></script>
        <?php endforeach ?>
    </head>
    <body>
        <div id="allWrapper">
            <div id="header">NethGui</div>
            <div class="colmask leftmenu">
                <div class="colleft">
                    <div class="col1">
                        <div id="breadcrumbMenu"><?php echo $response['BreadCrumb']['html'] ?></div>
                        <pre style="background: yellow" id="validationReport"><?php foreach($response['ValidationReport']['errors'] as $error) {
                            echo $error[1] . " ({$error[0]})\n";
                        } ?></pre>
                        <div id="moduleContent"><?php echo $framework->renderResponse($response['currentModule']) ?></div>
                    </div>
                    <div class="col2"><div id="moduleMenu"><?php echo $response['Menu']['html'] ?></div></div>
                </div>
            </div>
            <div id="footer">
                Powered by <a href="<?php echo site_url('../doc') ?>">NethGuiFramework</a> &ndash; Copyright 2011 &copy; Nethesis S.r.l
            </div>
        </div>
        <pre><?php echo '$request' ?></pre>
    </body>
</html>
