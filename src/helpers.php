<?php

/**
 * A bunch of helpers/aliases!
 *
 * @todo these helpers are named like normal PHP functions. May not clash with functions in other libraries.
 *			If they do, then a prefix can be added.
 */

/**
 *
 */
function boot()
{
    app()['twig'] = app()->share(app()->extend('twig', function ($twig, $app) {
        $twig->addGlobal('user', session()->get('user'));

        return $twig;
    }));
}

/**
 * Shorthand access to $app
 */
function app()
{
    global $app;
    return $app;
}

function db()
{
    return app()['db'];
}

/**
 * Retuns the current session
 */
function session()
{
    return app()['session'];
}

/**
 * Get the FlashBag from current session
 */
function flashbag()
{
    return session()->getFlashBag();
}

/**
 * Shorthand to $app['twig']
 */
function twig()
{
    return app()['twig'];
}
