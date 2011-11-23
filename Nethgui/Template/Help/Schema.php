<?php echo '<?xml version="1.0" encoding="utf-8"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $view['lang'] ?>" xml:lang="<?php echo $view['lang'] ?>">
    <head>
        <title><?php echo htmlspecialchars($view->translate($view['title'])) ?></title>
    </head>
    <body>
        <div id="HelpDocument">
            <?php echo new \Nethgui\Renderer\Help($view['content']) ?>
        </div>
        <hr/>
        <p><?php printf("Source: %s <br/>Timestamp: %s", $view['url'], strftime("%F %T")) ?></p>
        <p>Save this file as simple HTML then run Tidy as (<code>-m</code> flag will overwrite the given file)</p>
        <pre>
        $ tidy -xml -asxhtml -i -m &lt;filename&gt;
        </pre>
    </body>
</html>
