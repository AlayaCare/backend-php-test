<?php

date_default_timezone_set('America/Toronto');

// include the prod configuration
require __DIR__.'/prod.php';

// enable the debug mode
$app['debug'] = true;
