<?php

include('framework/Loader.php');
include('framework/methods.php');


$router = new Framework\Router();
$router->addRoute(
    new Framework\Router\Route\Simple(array(
        "pattern" => ":name/profile",
        "controller" => "home",
        "action" => "index"
    ))
);

$router->url = "chris/profile";
$router->dispatch();