<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE is_finished = 0 AND user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

      $adapter = new ArrayAdapter($todos);
      $pagerfanta = new Pagerfanta($adapter);
      $pagerfanta->setMaxPerPage(2);
      $pagerfanta->setCurrentPage($request->query->get('page', 1));

      return $app['twig']->render('todos.html', array(
        'pager' => $pagerfanta
      ));
        /*return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);*/
    }
})
->value('id', null);



$app->get('/', function (Request $request) use ($app) {

  $results = $app['service.fake']->getResults();

  $adapter = new ArrayAdapter($results);
  $pagerfanta = new Pagerfanta($adapter);
  $pagerfanta->setMaxPerPage(10);
  $pagerfanta->setCurrentPage($request->query->get('page', 1));

  return $app['twig']->render('index.html.twig', array(
    'pager' => $pagerfanta
  ));
})->bind('homepage');

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
  if ($description == ''){
    $request->getSession()
      ->getFlashBag()
      ->add('error', 'You can not create todo without description.');
  } else {

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    $request->getSession()
      ->getFlashBag()
      ->add('success', 'Your todo has been created.');


  }

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function (Request $request , $id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $request->getSession()
    ->getFlashBag()
    ->add('success', 'Your todo has been deleted.');

    return $app->redirect('/todo');
});

$app->match('/todo/done/{id}', function (Request $request , $id) use ($app) {

  $sql = "UPDATE todos SET is_finished = 1 WHERE id = '$id'";
  $app['db']->executeUpdate($sql);

  $request->getSession()
    ->getFlashBag()
    ->add('success', 'Your todo has been completed.');

  return $app->redirect('/todo');
});


$app->get('/todo/{id}/json', function ($id) use ($app) {
  if (null === $user = $app['session']->get('user')) {
    return $app->redirect('/login');
  }

  if ($id){
    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);

    return json_encode($todo);
  }
});