<?php
use Core\Path;
use Core\Contract;
use Core\Router;
use Core\Autoloader;
use Core\Env;

require_once __DIR__ . '/../Core/Path.php';
Path::initFromPublic(__DIR__);
require_once Path::root('Core/Autoloader.php');
Autoloader::register();
Env::load(Path::root('.env'));
Contract::loadConfig(Path::root('config/contracts.php'));
$router = new Router();
$router->loadRoutes(Path::root('config/routes.php'));
$router->dispatch();
