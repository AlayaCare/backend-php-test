<?php


namespace AC\Controller;


use AC\Core\ErrorCode;
use AC\Core\StatusEnum;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AC\Entity\Todo;

class TodoController implements ControllerProviderInterface
{

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $controllers = $app["controllers_factory"];
        $controllers->get('/todo/list/{page}/{sort_by}/{sorting}', function (Request $request, $sort_by, $page, $sorting) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }
            $count = $app['repository.todos']->countByUser($user['id']);
            $paginator = $app['paginator']($count, $page);
            $todos = $app['repository.todos']->findAllPaginator($paginator, $user['id'], $sort_by, $sorting);

            return $app['twig']->render('todos.html', [
                'todos' => $todos,
                'page' => $page,
                'pagination' => $paginator
            ]);
        })->value('page', 1)
            ->value('sort_by', 'id')
            ->value('sorting', 'asc')
            ->assert('page', '\d+')
            ->assert('sorting', '(\basc\b)|(\bdesc\b)')// Match "asc" or "desc"
            ->bind('todo/list');

        $controllers->get('/todo/{id}', function ($id) use ($app) {
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
                return $app->redirect('/todo/list');
            }
        })
            ->value('id', null);

        $controllers->get('/todo/{id}/json', function ($id) use ($app) {
            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }
            if ($id){
                $todo=$app['repository.todos']->findByIdAndUserId($id,$user['id']);
                if($todo){
                    return new JsonResponse($todo->toArray(), 200);
                }else{
                    return new JsonResponse(['error'=>ErrorCode::UNAUTHORIZED], 200);
                }
            } else {
                return new JsonResponse(['error'=>ErrorCode::DOES_NOT_EXIST], 200);
            }
        })
            ->value('id', null);


        $controllers->post('/todo/add', function (Request $request) use ($app) {
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
                $app['session']->getFlashBag()->add('todo_messages', 'A Todo was created successfully.');
            }
            return $app->redirect('/todo');
        });


        $controllers->match('/todo/delete/{id}', function ($id) use ($app) {

            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }
            $todo = $app['repository.todos']->findByIdAndUserId($id,$user['id']);
            if ($todo){
                $app['repository.todos']->remove($id);
                $app['session']->getFlashBag()->add('todo_messages', 'A Todo was removed successfully.');
            }else{
                return $app['twig']->render('error.html', [
                    'error' => ErrorCode::DOES_NOT_EXIST,
                ]);
            }


            return $app->redirect('/todo');
        });

        $controllers->match('/todo/complete/{id}', function ($id) use ($app) {

            if (null === $user = $app['session']->get('user')) {
                return $app->redirect('/login');
            }
            $todo = $app['repository.todos']->findByIdAndUserId($id,$user['id']);
            if ($todo){
                $todo->setStatus(StatusEnum::COMPLETED);
                $app['repository.todos']->update($todo);
                $app['session']->getFlashBag()->add('todo_messages', 'A Todo was completed successfully.');
            }else{
                return $app['twig']->render('error.html', [
                    'error' => ErrorCode::DOES_NOT_EXIST,
                ]);
            }


            return $app->redirect('/todo');
        });
        return $controllers;
    }
}