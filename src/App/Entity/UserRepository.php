<?php 
namespace App\Entity;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
	/**
     * @param $em Entity Manager
     * @param string $uname
     * @param string $password
     * @return User Object
     */
    public function getUser($em, $uname, $pass)
    {		
		$config = $em->getConfiguration();
		$config->addCustomStringFunction('SHA2', 'DoctrineExtensions\Query\Mysql\Sha2');
		
		$qb = $this->createQueryBuilder('u');
		
		$userObj = $qb
            ->select('u.id')
			->Addselect('u.username')
            ->where('u.password = SHA2(:password,256)')
			->andWhere('u.username = :username')
			->setParameters(array('password' => $pass, 'username' => $uname))
			->getQuery()
            ->getResult();
		
		return $userObj;		
	}
}