<?php

use Entity\Todo;
use Entity\User;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

function userLogin($app,$username,$password) {
    $em = $app['orm.em'];

    $rsm = new ResultSetMappingBuilder($em);
    $rsm->addRootEntityFromClassMetadata('Entity\\User', 'u');

    $query = $em->createNativeQuery('SELECT * FROM users WHERE username = ? and password = ?', $rsm);
    $query->setParameter(1, $username);
    $query->setParameter(2, $password);
    $users = $query->getResult();
    if ($users) {
        return $users[0];
    }
    return null;
}

function getTodoList($app) {
    $user = $app['session']->get('user');
    $em = $app['orm.em'];

    $rsm = new ResultSetMappingBuilder($em);
    $rsm->addRootEntityFromClassMetadata('Entity\\Todo', 't');

    $query = $em->createQuery('SELECT t FROM Entity\\Todo t where t.userId = '.$user->getId(), $rsm);
    return $query->getResult();
}

function getTodoById($app,$id) {
    $em = $app['orm.em'];
    $todo = $em->find('Entity\\Todo',$id);
    return $todo;
}

function addTodo($app,$description) {
    $user = $app['session']->get('user');
    $todo = new Todo();
    $todo->setUserId($user->getId());
    $todo->setDescription($description);
    $todo->setCompleted(0);

    try {
        $em = $app['orm.em'];
        $em->persist($todo);
        $em->flush();
        return true;
    } catch (Exception $ex) {
        return false;
    }
}

function changeTodoCompletion($app,$id,$completed) {
    try {
        $em = $app['orm.em'];
        $todo = $em->find('Entity\\Todo',$id);
        $todo->setCompleted($completed);
        $em->persist($todo);
        $em->flush();
        return true;
    } catch (Exception $ex) {
        return false;
    }
}

function deleteTodo($app,$id) {
    try {
        $em = $app['orm.em'];
        $todo = $em->find('Entity\\Todo',$id);
        $em->remove($todo);
        $em->flush();
        return true;
    } catch (Exception $ex) {
        return false;
    }
}