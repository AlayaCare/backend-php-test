<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


/**
 * Redirect to the TODO page and set a session message to display
 *
 * @param  Array    $app          Silex $app
 * @param  String   $APIMessage   [Optional] If set, pass an error message to display on frontend
 * @param  String   $messageType  [Optional] One of the bootstrap alert type. Default 'danger'
 * @return The twig template
 */
function redirectTodoWithMessage($app, $APIMessage = null, $messageType = 'danger') {

  // Set message and alert type
  $app['session']->getFlashBag()->set('message', $APIMessage);
  $app['session']->getFlashBag()->set('type', $messageType);

  // redirect to the TODO page
  return $app->redirect('/todo');
}



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
    $nbItemsPerPage = 5;

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
        // First retrieve the total number of todos for this user
        $total = $todos = $app['db']->fetchAll("SELECT COUNT(*) AS `Total` FROM `todos` WHERE `todos`.`user_id` = '${user['id']}'");

        // Then retrieve the page we want and compute SQL index
        $page = ($app["request"]->query->has('page')) ? intval($app["request"]->query->get('page')) : 1;
        $index = ($page - 1) * $nbItemsPerPage;

        // Perform request
        $sql = "SELECT * FROM `todos` WHERE `todos`.`user_id` = '${user['id']}' LIMIT $index, $nbItemsPerPage";
        $todos = $app['db']->fetchAll($sql);
        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'page'  => $page,
            'totalPages' => ceil($total[0]['Total'] / $nbItemsPerPage)
        ]);
    }
})
->value('id', null);


$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app->json($todo);
    } else {
        return $app->redirect('/todo');
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {

    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    $user_id = $user['id'];
    $description = $request->get('description');
    $confirmed = $request->get('confirmed');

    // If no description provided, set a usefull error message and redirect to the todo page
    if (empty($description)) {
      return redirectTodoWithMessage($app, 'Please add a description.');
    }

    // If we have the user confirmation, add the new task in DB
    if (!empty($confirmed)) {
      $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
      $app['db']->executeUpdate($sql);

      return $app->redirect('/todo');
    }
    // Else set flash bag for confirmation modal
    else {
      $app['session']->getFlashBag()->set('add_confirmation', $description);
      return $app->redirect('/todo');
    }

});


/**
 * Retrieve an order and toggle its done state
 */
$app->post('/todo/toggleState/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        // If the task wasn't done, set it done now !
        if (is_null($todo['done_date'])) {
          $updated = $app['db']->executeUpdate("UPDATE `todos` SET `done_date` = NOW() WHERE `todos`.`id` = $id");
          return ($updated === 1) ? redirectTodoWithMessage($app, 'Good work !', 'success') : redirectTodoWithMessage($app, 'Cannot update your task :/');
        }
        // ... else remove the done_date to see it pending in the todo list
        else {
          $updated = $app['db']->executeUpdate("UPDATE `todos` SET `done_date` = NULL WHERE `todos`.`id` = $id");
          return ($updated === 1) ? redirectTodoWithMessage($app, 'Keep on working !', 'info') : redirectTodoWithMessage($app, 'Cannot update your task :/');
        }
    } else {
        return $app->redirect('/todo');
    }
})
->value('id', null);


$app->post('/todo/delete/{id}', function (Request $request) use ($app) {

  if (null === $user = $app['session']->get('user')) {
      return $app->redirect('/login');
  }
  $user_id = $user['id'];
  $id = $request->get('id');
  $confirmed = $request->get('confirmed');

  // If we have the user confirmation, add the new task in DB
  if (!empty($confirmed)) {
    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    return redirectTodoWithMessage($app, 'Task removed!', 'info');
  }
  // Else set flash bag for confirmation modal
  else {
    $app['session']->getFlashBag()->set('delete_confirmation', $id);
    return $app->redirect('/todo');
  }
});
