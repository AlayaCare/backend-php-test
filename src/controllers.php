<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Models\User;
use Models\Todo;

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
    //Filtering user entries
    $username = filter_var($request->get('username'), FILTER_SANITIZE_STRIPPED);
    $password = filter_var($request->get('password'), FILTER_SANITIZE_STRIPPED);

    if ($username and $password) {
        //md5 for increase security
        $user = User::login($username, md5($password));

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        } else {
            $app['session']->getFlashBag()->add('error', 'Inválid login and/or password.');
        }
    } else if( !empty($_POST) ) {
        $app['session']->getFlashBag()->add('error', 'Login and password required.');
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

    $getTodo = Todo::getTodo($id);

    //Filtering user entries
    if ($id and is_numeric($id)){
        if($getTodo) {
            return $app['twig']->render('todo.html', [
                'todo' => $getTodo,
            ]);
        } else {
            return $app->redirect('/todo');
        }
    } else {
        /*** pagination ***/
        $perPage = 3;
        $count = count($getTodo);
        $totalPage = ceil(($count / $perPage));
        if(isset($_GET['page']) and is_numeric($_GET['page']) and $_GET['page'] <= $totalPage) {
            $currentPage = $_GET['page'];
        } else {
            if(isset($_GET['page']) and (is_numeric($_GET['page']) or $_GET['page'] == 'last')) {
                return $app->redirect("/todo?page={$totalPage}");
            } else {
                return $app->redirect('/todo?page=1');
            }
        };

        $pagination['totalPage'] = $totalPage;
        $pagination['currentPage'] = $currentPage;

        $getTodo = array_slice($getTodo, ( ($currentPage * $perPage) - $perPage ), $perPage);
        /*** end pagination ***/

        return $app['twig']->render('todos.html', [
            'todos' => $getTodo,
            'pagination' => $pagination
        ]);
    }
})
->value('id', null);

$app->get('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if(is_numeric($id)) {
        $todo = Todo::getTodo($id);
    }

    if($todo) {
        return json_encode($todo);
    } else {
        return $app->redirect('/todo');
    }
});

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $description = $request->get('description');

    if($description != "") {
        //Filtering user entries
        Todo::add(addslashes($description));
    } else {
        $app['session']->getFlashBag()->add('error', 'Description required.');
    }

    return $app->redirect("/todo?page=last");
});


$app->match('/todo/delete', function () use ($app) {
    //Filtering user entries
    if(is_numeric($_POST['id'])) {
        Todo::delete($_POST['id']);
    } else {
        $app['session']->getFlashBag()->add('error', 'Inválid Todo.');
    }

    return $app->redirect('/todo?page='.$_POST['currentPage']);
});

$app->match('/todo/complete', function () use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];

    if(is_numeric($_POST['id'])){
        Todo::complete($_POST['id']);

        $url = $_SERVER['HTTP_REFERER'];
        return $app->redirect($url);
    }
});