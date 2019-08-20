<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});

$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {

        $entityManager = $app['orm.em'];
        $user = $entityManager->getRepository('\App\Entity\User')
            ->findOneBy(
                [
                    'username' => $username,
                    'password' => $password
                ]
            );

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});

$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}/{format}', function (Request $request, $id, $format) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $entityManager = $app['orm.em'];

    if ($id){
        $todo = $entityManager->getRepository('\App\Entity\Todo')->find($id);

        if ($format === 'json') {
            return $app->json($todo, Response::HTTP_OK)->setEncodingOptions(JSON_NUMERIC_CHECK);
        }

        return $app['twig']->render(
            'todo.html',
            [
                'todo' => $todo
            ]
        );

    } else {

        $limit = 5;
        $page  = $request->get('page', 1) ;
        $start = $limit * ($page - 1);
        $start = $start < 0 ? 0 : $start;
        $user  = $entityManager->getRepository('\App\Entity\User')->find($user->getId());
        $totalPages = ceil($user->getTodos()->count() / $limit);


        if ($page > $totalPages) {
            $page = $totalPages;
            return $app->redirect('/todo' . $id . '?page=' . $page);
        }

        return $app['twig']->render(
            'todos.html',
            [
                'todos' => $user->getTodos($start, $limit),
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]
        );
    }
})
->value('id', null)
->value('format', null);

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $entityManager = $app['orm.em'];
    $description = $request->get('description');

    if ($description !== '') {
        $todo = new \App\Entity\Todo();
        $todo->setDescription($description)
            ->setUser($entityManager->getRepository('\App\Entity\User')->find($user->getId()))
            ->setStatus('pending');
        $entityManager->persist($todo);
        $entityManager->flush();
        $app['session']->getFlashBag()->add('info', 'You have added an item.');
    } else {
        $app['session']->getFlashBag()->add('error', 'A description cannot be empty.');
    }

    return $app->redirect('/todo');
});

$app->post('/todo/done/{id}', function ($id) use ($app) {
    $entityManager = $app['orm.em'];
    $todo = $entityManager->find('\App\Entity\Todo', $id);
    $todo->setStatus('done');
    $entityManager->persist($todo);
    $entityManager->flush();
    $app['session']->getFlashBag()->add('info', 'You marked item ' . $id . ' as done');

    return $app->redirect('/todo#' . $id);
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $entityManager = $app['orm.em'];
    $todo = $entityManager->find('\App\Entity\Todo', $id);
    $entityManager->remove($todo);
    $entityManager->flush();
    $app['session']->getFlashBag()->add('info', 'You have delete item ' . $id);

    return $app->redirect('/todo');
});
