<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
$app = require_once __DIR__.'/src/app.php';
require __DIR__.'/config/dev.php';

// replace with mechanism to retrieve EntityManager in your app
$entityManager = $app['entity_manager'];

return ConsoleRunner::createHelperSet($entityManager);
