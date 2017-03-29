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
    $em = $app['db.orm.em'];
   
    
    if ($username) {
        /*$sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);
        */
        $em = $app['db.orm.em'];
        $user = $em->getRepository('Entity\User')->findOneBy(array('username' => $username, 'password' => $password));
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
        $em = $app['db.orm.em'];
        $todo = $em->getRepository('Entity\Todo')->findBy(
            array( 
                "id" => $id,
                "user_id" => $app['session']->get('user')->getId()
            ));

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        
        $em = $app['db.orm.em'];
        $todos = $em->getRepository('Entity\Todo')->findBy(
            array( 
                "user_id" => $app['session']->get('user')->getId()
            ));

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);

$app->get('/todo/{id}/json', function($id) use ($app){
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    if ($id){
            $em = $app['db.orm.em'];
            $todo = $em->getRepository('Entity\Todo')->find($id);
            return json_encode($todo->json());
    }
    return $app->redirect('/todo');
});



$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
    $user_id = $app['session']->get('user')->getId();
    $description = $request->get('description');
    if (!empty(trim($description))){
        $em = $app['db.orm.em'];
        $todo = new Todo($user_id);
        $todo->setDescription($description);
        $em->persist($todo);
        $em->flush();
        $request->getSession()->getFlashBag()->add('success', 'New todo added');
    }
    else{
        $request->getSession()->getFlashBag()->add('error', 'New todo failed: Please enter a descrption');
    }

    return $app->redirect('/todo');
});

$app->match('/todo/complete/{id}', function (Request $request, $id) use ($app) {

    $completed = $request->get("completed");
    $em = $app['db.orm.em'];
    $todo = $em->getRepository('Entity\Todo')->find($id);
    $todo->setCompleted($completed);
    $em->flush();

    return $app->redirect('/todo');
});

$app->match('/todo/delete/{id}', function (Request $request, $id) use ($app) {

    $em = $app['db.orm.em'];
    $todo = $em->getRepository('Entity\Todo')->find($id);
    $em->remove($todo);
    $em->flush();
    $request->getSession()->getFlashBag()->add('success', 'Todo ' . $id . ' has been deleted');
    return $app->redirect('/todo');
});