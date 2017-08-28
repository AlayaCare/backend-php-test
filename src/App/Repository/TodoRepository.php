<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class TodoRepository extends EntityRepository
{
    /**
     * Return total of todos filtered by user
     *
     * @param $user user ID
     * @return mixed
     */
    public function getListCountByUser($user) {
        $queryBuilder = $this->_em->createQueryBuilder();

        $query = $queryBuilder
            ->select('count(t.id)')
            ->from('App\Entity\Todo', 't')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user);

        $count = $query->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * Get Todos by user
     *
     * @param $user User ID
     * @param $limit Number of todos to return
     * @param int $offset Numbder of todos to skip
     * @return array
     */
    public function findListByUser($user, $limit, $offset = 0) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $query = $queryBuilder
            ->select('t')
            ->from('App\Entity\Todo', 't')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->andWhere('t.user = :user')
            ->setParameter('user', $user);

        $result = $query->getQuery()->getResult();

        return $result;
    }
}
