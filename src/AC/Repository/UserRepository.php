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
        $this->table = 'users';
    }

    public function findById($id)
    {
        $sql=$this->builder->select('u')
            ->from($this->table)
            ->where('u.id =:id')
            ->setParameter(':id',(int) $id);
        $queryResult=$this->fetchAssoc($sql);
        if($queryResult)
            return $this->toObject($queryResult);
        return $queryResult;
    }

    public function findAll()
    {
        $sql=$this->builder->select('*')
            ->from($this->table);
        $queryResult=$this->fetchAll($sql);
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
        $sql=$this->builder->select('*')
            ->from($this->table)
            ->where($this->builder->expr()->eq('username',"'".$username."'" ))
            ->andWhere($this->builder->expr()->eq('password',"'".md5($password)."'" ))
            ->setMaxResults(1);
        $queryResult=$this->fetchAssoc($sql);
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
