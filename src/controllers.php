<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Entity\Todo;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});

$before = (function (Request $request) use ($app) {
    // redirect the user to the login screen if access to the Resource is protected
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }else{
        $user = $app['session']->get('user');
        $app['user_id'] = $user['id'];
    }
});

$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', []);
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function ($id) use ($app) {

    if ($id){
        // finding task by id and user_id
        $em = $app['db.orm.em'];
        $todo = $em->getRepository(Todo::class)->findOneBy(
            [
                "id" => $id,
                "user_id" => $user['id']
            ]);
        if (!$todo) {
            $app['monolog']->info(sprintf("Task '%s' not found.", $id));
            $app['session']->getFlashBag()->add('flashMsg', "Task $id not found.");
            $app['session']->getFlashBag()->add('type', 'danger');
            return $app->redirect('/todo');
        }
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);

    } else {
        $em = $app['db.orm.em'];
        $todos = $em->getRepository(Todo::class)->findBy(
            [
            "user_id" => $app['user_id']
            ]);
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->before($before)
->value('id', null);

$app->post('/todo/add', function (Request $request) use ($app) {

    $user_id = $app['user_id'];
    $description = $request->get('description');
    $validator = Validation::createValidator();
    $violations = $validator->validate($description, [
        new Length(['min' => 3]),
        new NotBlank(),
    ]);

    if(0 !== count($violations)){
        foreach ($violations as $violation) {
            $app['session']->getFlashBag()->add('flashMsg', $violation->getMessage());
            $app['session']->getFlashBag()->add('type', 'danger');
        }
    }else{

        $em = $app['db.orm.em'];
        $todo = new Todo($user_id, $description);
        $em->persist($todo);
        $em->flush();
        $app['session']->getFlashBag()->add('flashMsg', 'Task added successfuly!');
        $app['session']->getFlashBag()->add('type', 'success');
    }

    return $app->redirect('/todo');
})->before($before);


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $em = $app['db.orm.em'];
    $todo = $em->getRepository(Todo::class)->find($id);
    $em->remove($todo);
    $em->flush();
    $app['session']->getFlashBag()->add('flashMsg', 'Task removed successfuly!');
    $app['session']->getFlashBag()->add('type', 'success');

    return $app->redirect('/todo');
})->before($before);

$app->post('/todo/completed/{id}', function ($id) use ($app) {

    $em = $app['db.orm.em'];
    $todo = $em->getRepository(Todo::class)->find($id);
    $todo->setIs_complete(1);
    $em->flush();
    $app['session']->getFlashBag()->add('flashMsg', 'Task updated!');
    $app['session']->getFlashBag()->add('type', 'success');

    return $app->redirect('/todo');
});

$app->get('/todo/{id}/json', function ($id) use ($app) {
    $user_id = $app['user_id'];
    $em = $app['db.orm.em'];

    $todo = $em->getRepository(Todo::class)->findOneBy([
        'user_id' => $user_id,
        'id' => $id
        ]);

    if (count($todo) > 0) {
        $response = new \Symfony\Component\HttpFoundation\JsonResponse();
        $response->setEncodingOptions(JSON_NUMERIC_CHECK);
        $response->setData(['json' => $todo->getJson()]);
        return $response;
    }else{
        $app['session']->getFlashBag()->add('flashMsg', 'Nothing to show!');
        $app['session']->getFlashBag()->add('type', 'danger');

        return $app->redirect('/todo');
    }
})->before($before);