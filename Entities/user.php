<?php
/**
 * @Entity @Table(name="products")
 */
class User
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    	/** @Column(type="string") */
    protected $username;
	/** @Column(type="string") */
	protected $password;
	
	function __construct($id=null,$username=null,$password=null)
	{
		$this->id=$id;
		$this->username=$username;
		$this->password=$password;

	}

    public function getId()
    {
        return $this->id;
    }

    public function getusername()
    {
        return $this->username;
    }

    public function setusername($username)
    {
        $this->username = $username;
    }

    public function getpassword()
    {
        return $this->password;
    }

    public function setpassword($password)
    {
        $this->password = $password;
    }

   // login 
    public static function login ($app,$username,$password)
    {
    	$sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);
        return $user; 
    }
}
