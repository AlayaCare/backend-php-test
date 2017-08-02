<?php

namespace Todo;

use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

class User
{

    /**
     * @var array
     */
    private $data;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct($data, Connection $connection)
    {
        $this->data = $data;
        $this->connection = $connection;
    }

    /**
     * Attempts to login the user using the provided username and password.
     * If login successful logged in user data will be returned.
     *
     * @param string $username
     * @param string $password
     * @param Connection $connection
     * @return array
     * @throws InvalidCredentials
     */
    public static function login($username, $password, Connection $connection)
    {
        //fetch record by username
        $builder = $connection->createQueryBuilder();
        $builder->select('*')
            ->from('users')
            ->where('username = ?')
            ->setParameter(0, $username, PDO::PARAM_STR);

        $records = $builder->execute()->fetchAll();

        //verify only one record is found
        $userFound = count($records) == 1;

        if ($userFound) {
            $record = reset($records);

            //verify password
            $validPassword = (new BCryptPasswordEncoder(10))
                ->isPasswordValid($record['password'], $password, 'salt is not used');
        }

        if ($userFound && $validPassword) {
            return $record;
        }

        //invalid credentials
        throw new InvalidCredentials('Wrong username or password.');
    }

    /**
     * Provides a collection of reminders for this user.
     *
     * @param int $perPage Number of items per page
     * @param int $page Page number
     * @param int $total Will capture the number of total reminders
     * @return array
     */
    public function getReminders($perPage = null, $page = 1, &$total = null)
    {
        //build query
        $builder = $this->connection->createQueryBuilder();
        $builder->select('*')
            ->from('todos')
            ->where('user_id = ?')
            ->setParameter(0, $this->data['id'], PDO::PARAM_INT);

        if ($perPage !== null && is_numeric($perPage)) {
            //give total number of reminders
            $totalQuery = $this->connection->createQueryBuilder();
            $totalQuery->select('count(*) AS total')
                ->from('todos')
                ->where('user_id = ?')
                ->setParameter(0, $this->data['id']);

            $totalRecord = $totalQuery->execute()->fetch();
            $total = $totalRecord['total'];

            //consider paginate parameters
            $builder->setFirstResult($perPage * ($page - 1))
                ->setMaxResults($perPage);
        }

        $reminders = [];
        //converting status to boolean
        foreach ($builder->execute()->fetchAll() as $row) {
            $row['is_completed'] = (bool)$row['is_completed'];
            $reminders[] = $row;
        }

        return $reminders;
    }

    /**
     * Produces data for the reminder with a given id.
     * Will return null if reminder not found.
     *
     * @param string $reminderId
     * @return array|null
     */
    public function findReminder($reminderId)
    {
        //build query
        $builder = $this->connection->createQueryBuilder();
        $builder->select('*')
            ->from('todos')
            ->where('user_id = ?')
            ->setParameter(0, $this->data['id'], PDO::PARAM_INT)
            ->andWhere('id = ?')
            ->setParameter(1, $reminderId);

        $dataSet = $builder->execute()->fetchAll();

        if (count($dataSet) == 1) {
            $reminder = reset($dataSet);
            $reminder['is_completed'] = (bool)$reminder['is_completed'];

            return $reminder;
        }
    }

    /**
     * Adds a reminder.
     *
     * @param string $description
     * @return bool
     */
    public function addReminder($description)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->insert('todos')
            ->values([
                'user_id' => '?',
                'description' => '?'
            ])
            ->setParameter(0, $this->data['id'])
            ->setParameter(1, $description);

        $affectedRows = $builder->execute();

        return $affectedRows == 1;
    }

    /**
     * Deletes a reminder with the provided id.
     *
     * @param string $reminderId
     * @return bool
     */
    public function deleteReminder($reminderId)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->delete('todos')
            ->where('user_id = ?')
            ->andWhere('id = ?')
            ->setParameter(0, $this->data['id'])
            ->setParameter(1, $reminderId);

        $affectedRows = $builder->execute();

        return $affectedRows == 1;
    }

    /**
     * Changes status for a reminder with a given id.
     *
     * Will return false if there's a problem.
     *
     * @param string $reminderId
     * @return bool
     */
    public function toggleReminderStatus($reminderId)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->update('todos')
            ->set('is_completed', 'NOT is_completed')
            ->where('user_id = ?')
            ->andWhere('id = ?')
            ->setParameter(0, $this->data['id'])
            ->setParameter(1, $reminderId);

        $affectedRows = $builder->execute();

        return $affectedRows == 1;
    }
}