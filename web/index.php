<?php

use Symfony\Component\Debug\Debug;
use AC\Controller\TodoController;
require_once __DIR__.'/../vendor/autoload.php';

Debug::enable();

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/dev.php';
require __DIR__.'/../src/controllers.php';

/* Including Entity files */
require __DIR__.'/../src/AC/Entity/Todo.php';
require __DIR__.'/../src/AC/Entity/User.php';
/* Including Repository files */
require __DIR__.'/../src/AC/Repository/IRepository.php';
require __DIR__.'/../src/AC/Repository/Repository.php';
require __DIR__.'/../src/AC/Repository/UserRepository.php';

require __DIR__.'/../src/AC/Controller/TodoController.php';
$app->mount("/",  new TodoController());
$app->run();
