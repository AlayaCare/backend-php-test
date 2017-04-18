<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Entity\Todo;
use Entity\User;

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
        $user = $app->em->getRepository('Entity\User')->findOneBy(
            array(
                "username" => $username,
                "password" => $password
            )
        );

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todos');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}/{format}', function ($id, $format) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){

        $todo = $app->em->getRepository('Entity\Todo')->find(
            array(
                "id" => $id
            )
        );

        if($todo->getUser_Id() != $user->getId()){
            $app['session']->getFlashBag()->add('error', 'Invalid or forbidden todo.');
            return $app->redirect('/todos');
        }

        if($format == 'json'){
            return $app['twig']->render('todo_json.html', [
                'todo' => $todo,
                'encoded_todo' => $todo->getJson(),
            ]);
        }else{
            return $app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        }
    } else {
        $app['session']->getFlashBag()->add('error', 'Invalid or forbidden todo.');
        return $app->redirect('/todos');
    }
})
->value('id', null)
->value('format', null);


$app->get('/todos/{page}', function ($page) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    //pagination management
    $todos_count = $app->em->getRepository('Entity\Todo')->findBy(
        array(
            "user_id" => $user->getId()
        )
    );

    $page_count = ceil(count($todos_count)/5);
    $start = ($page-1)*5;
    $limit = 5;

    $todos = $app->em->getRepository('Entity\Todo')->findBy(
        array(
            "user_id" => $user->getId()
        ),
        array(
            "id" => 'ASC'
        ),
        $limit, $start);

    return $app['twig']->render('todos.html', [
        'todos' => $todos,
        'page' => $page,
        'page_count' => $page_count
    ]);

})
->value('page', 1);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user->getId();
    $description = $request->get('description');

    if(empty($description)){
        $app['session']->getFlashBag()->add('error', 'The todo description can\'t be empty.');
        return $app->redirect('/todo');
    }

    $todo = new Todo();
    $todo->setUserId($user_id);
    $todo->setDescription($description);
    $todo->setIs_Completed(0);
    $app->em->persist($todo);
    $app->em->flush();

    $app['session']->getFlashBag()->add('success', 'You successfulyl added a new todo.');

    return $app->redirect('/todos');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todo = $app->em->getRepository('Entity\Todo')->find(
        array(
            "id" => $id
        )
    );

    if($todo->getUser_Id() != $user->getId()){
        $app['session']->getFlashBag()->add('error', 'Invalid or forbidden todo.');
        return $app->redirect('/todos');
    }

    $app->em->remove($todo);
    $app->em->flush();

    $app['session']->getFlashBag()->add('success', 'You successfulyl deleted todo #'.$id.'.');

    return $app->redirect('/todos');
});


$app->match('/todo/complete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todo = $app->em->getRepository('Entity\Todo')->find(
        array(
            "id" => $id
        )
    );

    if($todo->getUser_Id() != $user->getId()){
        $app['session']->getFlashBag()->add('error', 'Invalid or forbidden todo.');
        return $app->redirect('/todos');
    }

    $todo->setIs_Completed(1);
    $app->em->persist($todo);
    $app->em->flush();

    $app['session']->getFlashBag()->add('success', 'You successfulyl completed todo #'.$id.'.');

    return $app->redirect('/todos');
});