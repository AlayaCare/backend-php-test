<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

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

        // check the user repo for a valid user
        $user = $app['user.repository']->get($username, md5($password));

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
        else {
            $app['session']->getFlashBag()->add('warning', 'Incorrect Username and/or Password');
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
        // get the todo record from the Repo
        $todo = $app['todo.repository']->get($id);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        // define the max number of rows per page
        $num_rows = 5;

        // get total todos for this user
        $total_todos = $app['todo.repository']->count($user['id']);
        
        // get the number of pages to show in pagination element
        $numofpages = ceil($total_todos / $num_rows); 

        // grab the page number from the request (if not set then default to 1)
        $page_num =  $request->get('p') ?: '1';
        // calculate the offset for record retrieval
        $offset = ($page_num - 1) * $num_rows;
        // retrieve paginated set of todos
        $todos = $app['todo.repository']->getPagebyUser($user['id'], $num_rows, $offset);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
            'numofpages' => $numofpages,
            'page_num' => $page_num,
        ]);
    }
})
->value('id', null);

/*
* A route that handles json generation
*/
$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // if we have a valid id then retreve the data and display it as json
    if ($id) {
        $todo = $todo = $app['todo.repository']->get($id);
        return $app->json($todo);
    }

})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');
    
    // make sure the description field is not empty
    $errors = $app['validator']->validate($description, new Assert\NotBlank());

    // only add note if the user entered a description
    if (!count($errors)) {
        $app['todo.repository']->add($user_id, $description);
        // add a confirmation notice to the flashbag array
        $app['session']->getFlashBag()->add('notice', 'Task added!');
    }   
    return $app->redirect('/todo');
});

/*
* A route that handles the completion status
*/
$app->match('/todo/done/{id}', function ($id) use ($app) {

    // update the database and toggle the completed flag
    $app['todo.repository']->toggleDone($id);

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $app['todo.repository']->delete($id);
    // add a removal notice to the flashbag array
    $app['session']->getFlashBag()->add('notice', 'Task removed!');

    return $app->redirect('/todo');
});