<?php

namespace App\Controllers;

use League\Plates\Engine;

class Controller
{
    public static function view(string $view, array $data = [])
    {
        $viewsPath = dirname(__FILE__, 2) . "/views";

        if (!file_exists($viewsPath . DIRECTORY_SEPARATOR . $view . ".php")) {
            throw new \Exception("This view not exists");
        }

        $templates = new Engine($viewsPath);
        echo $templates->render($view, $data);
    }
}
