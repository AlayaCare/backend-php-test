<?php


namespace AC\Repository;
use AC\Entity\IEntity;
use AC\Entity\User;
use Silex\Application;

/**
 * Class UserRepository
 * @package AC\Repository
 */
class UserRepository extends Repository implements IRepository
{

    public function __construct(Application $app){
        parent::__construct($app);
    }

    public function findById($id)
    {
        $queryResult=$this->app["db"]->fetchAssoc("SELECT * FROM users where id=? limit 1", [(int) $id]);
        if($queryResult)
            return $this->toObject($queryResult);
        return $queryResult;
    }

    public function findAll()
    {
        $queryResult=$this->app["db"]->fetchAll("SELECT * FROM users");
        $userList=[];
        foreach ($queryResult as $userData){
           array_push($userList,$this->toObject($userData));
        }
        return $userList;
    }

    public function insert(IEntity $entity)
    {
        // TODO: Implement insert() method.
    }


    public function update(IEntity $entity)
    {
        // TODO: Implement update() method.
    }

    public function remove($id)
    {
        // TODO: Implement remove() method.
    }

    public function login($username,$password)
    {
        $queryResult=$this->app["db"]->fetchAssoc("SELECT * FROM users where username=? and password=? limit 1", [$username,md5($password)]);
        if($queryResult)
            return $this->toObject($queryResult);
        return $queryResult;
    }
    public function toObject(array $data)
    {
        $user= new User();
        $user->fill($data);
        return $user;
    }
}
