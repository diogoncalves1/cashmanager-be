<?php
require "../vendor/autoload.php";
require "../routes/router.php";

try {
    $uri = parse_url($_SERVER["REQUEST_URI"])["path"];

    $request = $_SERVER["REQUEST_METHOD"];
    if (!isset($router[$request])) {
        throw new Exception("This route not exists");
    }
    if (!array_key_exists($uri, $router[$request])) {
        throw new Exception("This route not exists");
    }

    $controler = $router[$request][$uri];
    $controler();
} catch (Exception $e) {
    if (processRequest($method, $uri))
        echo $e->getMessage();
}