<?php

use Doctrine\ORM\Tools\Pagination\Paginator;
use Entity\Todo;
use Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    if ($app['session']->get('user') !== null) {
        $em = $app['db.orm.em'];
        $user = $em->getRepository(User::class)->find($app['session']->get('user')->getId());
    } else {
        $user = null;
    }

    $twig->addGlobal('user', $user);

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
    }
});

$app->get('/login', function (Request $request) use ($app) {
    return $app['twig']->render('login.html', []);
});

$app->post('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $em = $app['db.orm.em'];

        $user = $em->getRepository(User::class)->findOneBy([
            'username' => $username,
            'password' => $password,
            ]);

        if ($user !== null) {
            $app['session']->set('user', $user);

            return $app->redirect('/todo');
        }
    }
    $app['session']->getFlashBag()->add('flashMsg', 'The user name and password combination is not valid');
    $app['session']->getFlashBag()->add('type', 'danger');

    return $app['twig']->render('login.html', []);
});

$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);

    return $app->redirect('/');
});

$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    if ($id) {
        // finding task by id and user_id
        $em = $app['db.orm.em'];
        $todo = $em->getRepository(Todo::class)->findOneBy(
            [
                'id' => $id,
                'user_id' => $app['session']->get('user')->getId(),
            ]
        );
        if (!$todo) {
            $app['monolog']->info(sprintf("Task '%s' not found.", $id));
            $app['session']->getFlashBag()->add('error', "Task $id not found.");
            return $app->redirect('/todo');
        }

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    }
    $page_number = $request->get('page_number') === null ? 1 : (int) ($request->get('page_number'));
    if ($page_number === 0) {
        ++$page_number;
    }
    $totalPerPage = 10;
    $em = $app['db.orm.em'];
    $dql = "SELECT t FROM Entity\Todo t where t.user_id = ".$app['session']->get('user')->getId();
    $query = $em->createQuery($dql)
                       ->setFirstResult($totalPerPage * ($page_number - 1))
                       ->setMaxResults($totalPerPage);
    $paginator = new Paginator($query);
    $result = count($paginator) / $page_number;
    if ($result > 10) {
        $next_page = $page_number + 1;
    } else {
        $next_page = $page_number;
    }

    return $app['twig']->render('todos.html', [
            'todos' => $query->getResult(),
            'next_page' => $next_page,
        ]);
})
->before($before)
->value('id', null);

$app->post('/todo/add', function (Request $request) use ($app) {
    $user_id = $app['session']->get('user')->getId();
    $description = $request->get('description');
    $validator = Validation::createValidator();
    $violations = $validator->validate($description, [
        new Length(['min' => 3]),
        new NotBlank(),
    ]);

    if (0 !== count($violations)) {
        foreach ($violations as $violation) {
            $app['session']->getFlashBag()->add('error', $violation->getMessage());
        }
    } else {
        $em = $app['db.orm.em'];
        $user = $em->getRepository(User::class)->find($app['session']->get('user')->getId());
        $todo = new Todo($user, $description);
        $em->persist($todo);
        $em->flush();

        $app['session']->getFlashBag()->add('success', 'Task added successfuly!');
    }

    return $app->redirect('/todo');
})->before($before);

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    $em = $app['db.orm.em'];
    $todo = $em->getRepository(Todo::class)->find($id);
    if (count($todo) > 0) {
        if ($todo->canDelete($app['session']->get('user')->getId())) {
            $em->remove($todo);
            $em->flush();
            $app['session']->getFlashBag()->add('success', 'Task removed successfuly!');
        } else {
            $app['session']->getFlashBag()->add('error', 'You can\'t remove this task!');
        }
    }

    return $app->redirect('/todo');
})->before($before);

$app->post('/todo/completed/{id}', function ($id) use ($app) {
    $em = $app['db.orm.em'];
    $todo = $em->getRepository(Todo::class)->findOneBy([
        'user_id' => $app['session']->get('user')->getId(),
        'id' => $id,
        ]);

    if (count($todo) > 0) {
        $todo = $em->getRepository(Todo::class)->find($id);
        $todo->setIsComplete(1);
        $em->flush();
        $app['session']->getFlashBag()->add('success', 'Task updated!');
    } else {
        $app['session']->getFlashBag()->add('error', 'We couldn\'t update the task requested');
    }

    return $app->redirect('/todo');
});

$app->get('/todo/{id}/json', function ($id) use ($app) {
    $user_id = $app['session']->get('user')->getId();
    $em = $app['db.orm.em'];

    $todo = $em->getRepository(Todo::class)->findOneBy([
        'user_id' => $user_id,
        'id' => $id,
        ]);

    if (count($todo) > 0) {
        return json_encode($todo);
    }
    $app['session']->getFlashBag()->add('error', 'Nothing to show!');

    return $app->redirect('/todo');
})->before($before);
