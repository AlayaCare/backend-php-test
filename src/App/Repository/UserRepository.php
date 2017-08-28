<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository implements UserProviderInterface
{
    // Could not make it work via EntityRepository
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
       $queryBuilder = $this->db->createQueryBuilder();
       $query = $queryBuilder
           ->select('u.*')
           ->from('users', 'u')
           ->andWhere('u.username = :username')
           ->setParameter('username', $username);
       $statement = $query->execute();
       $data = $statement->fetchAll();

       if (empty($data)) {
           throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
       }
       $user = $this->arrayToUser($data[0]);
       return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }

        $id = $user->getId();

        $queryBuilder = $this->db->createQueryBuilder();
        $query = $queryBuilder
            ->select('u.*')
            ->from('users', 'u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id);
        $statement = $query->execute();
        $data = $statement->fetchAll();

        $refreshedUser = $this->arrayToUser($data[0]);

        if (false === $refreshedUser) {
            throw new UsernameNotFoundException(sprintf('User with id %s not found', json_encode($id)));
        }

        return $refreshedUser;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return 'App\Entity\User' === $class;
    }

    protected function arrayToUser($array) {
        $user = new User();
        $user->setId($array['id']);
        $user->setUsername($array['username']);
        $user->setSalt($array['salt']);
        $user->setPassword($array['password']);
        $user->setRole($array['role']);

        return $user;
    }
}
