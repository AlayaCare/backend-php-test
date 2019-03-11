<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AC\Core\ErrorCode;
use AC\Entity\Todo;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $user=$app['repository.users']->login($username,$password);
        if ($user){
            $app['session']->set('user', $user->toArray());
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $todo=$app['repository.todos']->findByIdAndUserId($id,$user['id']);
        if($todo){
            return $app['twig']->render('todo.html', [
                'todo' => $todo,
            ]);
        }else{
            return $app['twig']->render('error.html', [
                'error' => ErrorCode::UNAUTHORIZED,
            ]);
        }
    } else {
        $todos=$app['repository.todos']->findAllByUser($user['id']);
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    $todo=new Todo();
    $todo->fill(['description'=>$description,'user_id'=>$user_id]);
    $errors = $app["validator"]->validate($todo);
    if(count($errors) > 0){
        $app['session']->getFlashBag()->add('todo_errors', 'A Todo can not be created without a description.');
    }else{
        $app['repository.todos']->insert($todo);
    }
    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $todo = $app['repository.todos']->findByIdAndUserId($id,$user['id']);
    if ($todo){
        $app['repository.todos']->remove($id);
    }else{

        return $app['twig']->render('error.html', [
            'error' => ErrorCode::DOES_NOT_EXIST,
        ]);
    }


    return $app->redirect('/todo');
});