<?php

namespace Repository;

use Doctrine\DBAL\Connection;

class UserRepository {
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    public function __construct(Connection $db) {
        $this->db = $db;
    }

    public function get($username, $password) {
        // retrieve user based on username and password
        $user = $this->db->fetchAssoc("SELECT * FROM users WHERE username = :usr and password = :pwd", array(
            "usr" => $username,
            "pwd" => $password
        ));
        
        // return a valid user, otherwise return 0
        return $user ?: 0;
    }
}

?>