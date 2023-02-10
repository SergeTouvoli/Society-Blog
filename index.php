<?php
session_start();

require_once "class/Router.php";

$router = new Router();
$router->run();

