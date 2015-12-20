<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share( $app->extend( 'twig', function($twig, $app) {
            $twig->addGlobal( 'user', $app['session']->get( 'user' ) );

            return $twig;
        } ) );


$app->get( '/', function () use ($app) {
    return $app['twig']->render( 'index.html', [
                'readme' => file_get_contents( 'README.md' ),
            ] );
} );


$app->match( '/login', function (Request $request) use ($app) {
    $username = $request->get( 'username' );
    $password = $request->get( 'password' );

    if ( $username ) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc( $sql );

        if ( $user ) {
            $app['session']->set( 'user', $user );
            return $app->redirect( '/todo' );
        }
    }

    return $app['twig']->render( 'login.html', array() );
} );


$app->get( '/logout', function () use ($app) {
    $app['session']->set( 'user', null );
    return $app->redirect( '/' );
} );


$app->get( '/todo/{id}', function (Request $request, $id) use ($app) {
            if ( null === $user = $app['session']->get( 'user' ) ) {
                return $app->redirect( '/login' );
            }

            $user_id = (int) $user['id'];
            $todo = new Todo( $app );

            if ( $id ) {

                $todo = $todo->get( ['id' => $id, 'user_id' => $user_id] );

                return $app['twig']->render( 'todo.html', [
                            'todo' => $todo,
                        ] );
            } else {

                $todos = $todo->get( ['user_id' => $user_id] );

                $template_data = [
                    'todos' => $todos,
                ];
                // passing empty description check to view
                if ( $request->query->get( 'empty_description' ) ) {
                    $template_data['empty_description'] = true;
                }

                return $app['twig']->render( 'todos.html', $template_data );
            }
        } )
        ->value( 'id', null );

$app->get( '/todo/{id}/json', function ($id) use ($app) {
            if ( null === $user = $app['session']->get( 'user' ) ) {
                return $app->redirect( '/login' );
            }

            if ( $id ) {
                // Make sure we added user_id to query so users can only view their own
                $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
                $todo = $app['db']->fetchAssoc( $sql, [(int) $id, $user['id']] );

                header( 'Content-Type: application/json' );
                return json_encode( $todo );
            }
            return $app->redirect( '/todo' );
        } )
        ->value( 'id', null );

$app->post( '/todo/add', function (Request $request) use ($app) {
    if ( null === $user = $app['session']->get( 'user' ) ) {
        return $app->redirect( '/login' );
    }

    $user_id = (int) $user['id'];
    $description = $request->get( 'description' );

    // if is empty description send back to todo and display error
    if ( !$description ) {
        return $app->redirect( '/todo?empty_description=true' );
    }
    $todo = new Todo( $app );
    $operation_status = $todo->create( ['user_id' => $user_id, 'description' => $description] );

    if ( $operation_status ) {
        $app['session']->getFlashBag()->add( 'success_confirmation', 'Successfully added todo' );
    }

    return $app->redirect( '/todo' );
} );

$app->post( '/todo/update/{id}', function (Request $request, $id) use ($app) {
            if ( null === $user = $app['session']->get( 'user' ) ) {
                return $app->redirect( '/login' );
            }
            if ( $id ) {
                $int_id = (int) $id;

                $completed = $request->request->get( 'completed' );

                if ( $completed === 'c' ) {
                    // Make sure we added user_id to query so users can only change their own
                    $sql = 'UPDATE todos SET status = "c" WHERE id = ? AND user_id = ?';
                    // Protect against SQL injection
                    $app['db']->executeUpdate( $sql, [$int_id, $user['id']] );
                }

                return $app->redirect( "/todo/$int_id" );
            }
            return $app->redirect( '/todo' );
        } )
        ->value( 'id', null );

$app->match( '/todo/delete/{id}', function ($id) use ($app) {

    if ( null === $user = $app['session']->get( 'user' ) ) {
        return $app->redirect( '/login' );
    }
    $todo = new Todo( $app );
    $operation_status = $todo->delete( ['id' => $id, 'user_id' => $user['id']] );
    if ( $operation_status ) {
        $app['session']->getFlashBag()->add( 'success_confirmation', 'Successfully deleted todo' );
    }
    return $app->redirect( '/todo' );
} );
