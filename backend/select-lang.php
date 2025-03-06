<?php
session_start();
$lang = $_GET['lang'];
$path = $_SESSION['path'];
$expiration = time() + 10 * 365 * 24 * 60 * 60;
setcookie("lang", $lang, $expiration, "/");
header("location: " . $path);
