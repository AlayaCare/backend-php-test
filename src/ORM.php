<?php 
 class ORM {
//GET TODO
    public function getTodo($app,$id){
        $QueryBuilder = $app['db']->createQueryBuilder();
        $QueryBuilder->select('*')
        ->from('todos')
        ->where(
            $QueryBuilder->expr()->eq('id', $id)
			);
        $results = $QueryBuilder->execute()->fetchAll();
        return $results[0];
    }
//GET TODOS
    public function getTodos($app, $user_id, $offset, $pagesize){
        $QueryBuilder = $app['db']->createQueryBuilder();
        $QueryBuilder->select('*')
        ->from('todos')
        ->where(
            $QueryBuilder->expr()->eq('user_id', $user_id)
            )
        ->setMaxResults($pagesize)
        ->setFirstResult($offset);
        $results = $QueryBuilder->execute()->fetchAll();
        return $results;
    }
//ADD TODO
    public function addTodo($app, $description, $user_id){
        $QueryBuilder = $app['db']->createQueryBuilder();
        $QueryBuilder->insert('todos')
        ->values(array(
            'user_id' => $user_id,
            'description' => "'".$description."'"
        ));
        $app['db']->executeUpdate($QueryBuilder->getSQL());
    }
//DELETE TODO
    public function deleteTodo($app, $id, $user){
        $QueryBuilder = $app['db']->createQueryBuilder();
        $QueryBuilder->delete('todos')
        ->where(
            $QueryBuilder->expr()->eq('id', $id)
            )
        ->andWhere(
            $QueryBuilder->expr()->eq('user_id', $user['id'])
        );
        return $app['db']->executeUpdate($QueryBuilder->getSQL());
    }
//MARK TODO AS COMPLETED
    public function markCompleted($app, $id, $user){
        $QueryBuilder = $app['db']->createQueryBuilder();
        $QueryBuilder->update('todos')
        ->set('completed', true)
        ->where(
            $QueryBuilder->expr()->eq('id', $id)
        )
        ->andWhere(
            $QueryBuilder->expr()->eq('user_id', $user['id'])
        );
         $app['db']->executeUpdate($QueryBuilder->getSQL());
    }
//GET TOTAL OF TODOS
    public function getTotal($app, $user_id){
        $QueryBuilder = $app['db']->createQueryBuilder();
        $QueryBuilder->select('count(*) as total')
        ->from('todos')
        ->where(
            $QueryBuilder->expr()->eq('user_id', $user_id)
            );
        $results = $QueryBuilder->execute()->fetchAll();
         return $results[0]['total'];
    }
//GET USER INFO
	public static function getUser($app, $username, $password){    
        $QueryBuilder = $app['db']->createQueryBuilder();
        $QueryBuilder->select('*')
        ->from('users')
        ->where(
            $QueryBuilder->expr()->eq('username', "'".$username."'"));
        $results = $QueryBuilder->execute()->fetchAll();
        if(isset($results[0])){     
            return $results[0];
        }
        return false;
    }
} 