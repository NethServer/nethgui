<?php
if (!function_exists('writeHeader')) {
    function writeHeader($str, $level = 1) {
        $levels = array(
            1 => '=',
            2 => '=',
            3 => '-',
            4 => '^',
            5 => '~'
        );
        $count = strlen($str);
        $char = isset($levels[$level])?$levels[$level]:"'"; # fallback character is '
        if ($level == 1) { # write also overline
            for ($i = 0; $i < $count; $i++) {
                echo $char;
            }
        }
        echo "\n$str\n";
        for ($i = 0; $i < $count; $i++) {
            echo $char;
        }
        echo "\n";
    }
}

writeHeader($view['title'],$view['titleLevel']);
echo "\n";
echo $view['description'];
echo "\n\n";
foreach ($view['fields'] as $field) {
    echo $field['label'];
    echo "\n";
    echo "    ".$T('Describe *${0}* here..', array($field['label']));
    echo "\n\n";
}
