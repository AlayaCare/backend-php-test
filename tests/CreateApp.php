<?php

        $app = require __DIR__.'/../src/app.php';
        require __DIR__.'/../config/dev.php';
        require __DIR__.'/../src/routes.php';

        $app['session.test'] = true;

