<?php
require_once "bootstrap.php";
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$userRepo = $entityManager->getRepository('Users');
$todosRepo = $entityManager->getRepository('Todos');

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app, $userRepo) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $user = $userRepo->findOneBy(array('username' => $username, 'password' => $password));
        // $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        // $user = $app['db']->fetchAssoc($sql);
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


$app->get('/todo/{id}', function (Request $request) use ($app, $todosRepo) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $id = $request->query->get('id');
    if ($id){
        // $sql = "SELECT * FROM todos WHERE id = '$id'";
        // $todo = $app['db']->fetchAssoc($sql);
        $todo = $todosRepo->findOneBy(array('id' => $id));
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $todos = $todosRepo->findAll(array('user_id' => $user['id']));
        $adapter = new ArrayAdapter($todos);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(5);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));
        

        return $app['twig']->render('todos.html', array(
            'pager' => $pagerfanta
        ));
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app, $entityManager) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todo = new Todos();
    $user_id = $user['id'];
    $description = $request->get('description');
    $todo->setUser_ID($user_id);
    $todo->setDescription($description);
    $entityManager->persist($todo);
    $entityManager->flush();

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app, $todosRepo, $entityManager) {
    $todo = $todosRepo->findOneBy(array('id' => $id));
    $entityManager->remove($todo);
    $entityManager->flush();
    return $app->redirect('/todo');
});