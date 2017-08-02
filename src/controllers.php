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
            "user_id" => $user['id']
            ]);
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
    $validator = Validation::createValidator();
    $violations = $validator->validate($description, array(
        new Length(array('min' => 3)),
        new NotBlank(),
    ));

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
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $em = $app['db.orm.em'];
    $todo = $em->getRepository(Todo::class)->find($id);
    $em->remove($todo);
    $em->flush();
    $app['session']->getFlashBag()->add('flashMsg', 'Task removed successfuly!');
    $app['session']->getFlashBag()->add('type', 'success');

    return $app->redirect('/todo');
});

$app->post('/todo/completed/{id}', function ($id) use ($app) {

    $sql = "UPDATE todos set is_complete = 1 WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return $app->redirect('/todo');
});

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos where user_id = '${user['id']}' and id='$id'";
    $query = $app['db']->fetchAll($sql);

    $response = new \Symfony\Component\HttpFoundation\JsonResponse();
    $response->setEncodingOptions(JSON_NUMERIC_CHECK);
    $response->setData(array('suppliers' => $query));

    return $response;

});