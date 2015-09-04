<?php
error_reporting(E_ALL);
date_default_timezone_set("Asia/Tokyo");

spl_autoload_register(function ($class) {
    $src = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    $src = dirname(__DIR__) . DIRECTORY_SEPARATOR . $src . ".php";
    @include $src;
});
