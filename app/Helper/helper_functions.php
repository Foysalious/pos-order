<?php


$helper_files = [
    "app/Helper/Migration/functions.php",
    "app/Helper/Http/functions.php",
    "app/Helper/String/functions.php",
    "app/Helper/Pagination/function.php",
    "app/Helper/FileManager/functions.php",
    "app/Helper/FileManager/review.php",
    "app/Helper/Formatters/functions.php",
    "app/Helper/Pagination/function.php",
    "app/Helper/Miscellaneous/functions.php",
    "app/Helper/Time/functions.php"
];

foreach ($helper_files as $file) {
    $file = dirname(dirname(__DIR__)) . "/" . $file;
    if (file_exists($file))
        require $file;
}
