<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Michelf\Markdown;
use Models\User;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    $readmeHtml = Markdown::defaultTransform(file_get_contents('README.md'));
    return $app['twig']->render('index.html', [
        'readme' => $readmeHtml,
    ]);
});