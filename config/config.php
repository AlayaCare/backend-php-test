$container->loadFromExtension('doctrine', array(
    'dbal' => array(
        'driver'   => 'pdo_mysql',
        'host'     => '%localhost%',
        'dbname'   => '%ac_todos%',
        'user'     => '%root%',
        'password' => '%%',
    ),
));