<?php 


class TodoORM {

    public function get_todo($app,$id){
        $qb = $app['db']->createQueryBuilder();
        $qb->select('*')
        ->from('todos')
        ->where(
            $qb->expr()->eq('id', $id)
            );
        $results = $qb->execute()->fetchAll();
        //  'name = ?'
        // $queryBuilder->setParameter($placeholder, $value) instead:
        return $results[0];
    }

    public function get_todos($app, $user_id, $offset, $pagesize){
        $qb = $app['db']->createQueryBuilder();
        $qb->select('*')
        ->from('todos')
        ->where(
            $qb->expr()->eq('user_id', $user_id)
            )
        ->setMaxResults($pagesize)
        ->setFirstResult($offset);
        $results = $qb->execute()->fetchAll();
        return $results;
    }

    public function add_todo($app, $description, $user_id){
        $qb = $app['db']->createQueryBuilder();
        $qb->insert('todos')
        ->values(array(
            'user_id' => $user_id,
            'description' => "'" .$description."'"
        ));
        $app['db']->executeUpdate($qb->getSQL());
    }

    public function delete_todo($app, $id, $user){
        $qb = $app['db']->createQueryBuilder();
        $qb->delete('todos')
        ->where(
            $qb->expr()->eq('id', $id)
            )
        ->andWhere(
            $qb->expr()->eq('user_id', $user['id'])
        );
        return $app['db']->executeUpdate($qb->getSQL());
    }

    public function mark_as_completed($app, $id, $user){
        $qb = $app['db']->createQueryBuilder();
        $qb->update('todos')
        ->set('completed', true)
        ->where(
            $qb->expr()->eq('id', $id)
        )
        ->andWhere(
            $qb->expr()->eq('user_id', $user['id'])
        );

        $app['db']->executeUpdate($qb->getSQL());
    }

    public function get_total_count($app, $user_id){
        $qb = $app['db']->createQueryBuilder();
        $qb->select('count(*) as total')
        ->from('todos')
        ->where(
            $qb->expr()->eq('user_id', $user_id)
            );
        $results = $qb->execute()->fetchAll();

        return $results[0]['total'];
    }
}