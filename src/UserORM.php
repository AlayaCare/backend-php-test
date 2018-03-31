<?php

class UserORM{

    public static function get_user($app, $username, $password){    
        $qb = $app['db']->createQueryBuilder();
        $qb->select('*')
        ->from('users')
        ->where(
            $qb->expr()->eq('username', "'".$username."'"));
        $results = $qb->execute()->fetchAll();
        if(isset($results[0])){     
            return $results[0];
        }
        return false;
    }
}