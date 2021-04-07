<?php


$helper_files = [
    "app/Helper/Migration/functions.php",
    "app/Helper/Http/functions.php",
    "app/Helper/String/functions.php",
    "app/Helper/Formatters/functions.php",
];

foreach ($helper_files as $file) {
    $file = dirname(dirname(__DIR__)) . "/" . $file;
    if (file_exists($file))
        require $file;
}
