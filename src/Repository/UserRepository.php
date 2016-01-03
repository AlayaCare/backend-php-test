<?php

namespace Repository;

use Doctrine\DBAL\Connection;
use Entity\User;

/**
 * Class UserRepository
 *
 * @package Repository
 *
 * @author Jerome Catric
 */
class UserRepository extends AbstractRepository
{

    /**
     * Constructor
     *
     * @param Connection $db the connection to the database
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->tableName = 'users';
    }


    /**
     * Convert an associative array to an object user
     *
     * @param array $data the associative array
     *
     * @return \Entity\User the object user
     */
    protected function convertArrayToObject(array $data)
    {
        $user = new User();
        $user->setId($data['id']);
        $user->setUsername($data['username']);
        $user->setPassword($data['password']);

        return $user;
    }

    /**
     * Convert an object user to an associative array
     *
     * @param \Entity\User $entity The object user
     *
     * @return array The associative array
     */
    protected function convertObjectToArray($entity)
    {
        $data = array();
        $data['id'] = $entity->getId();
        $data['password'] = $entity->getPassword();
        $data['username'] = $entity->getUsername();

        return $data;
    }

}