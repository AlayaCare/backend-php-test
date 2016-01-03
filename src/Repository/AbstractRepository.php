<?php

namespace Repository;

use Doctrine\DBAL\Connection;
use PDO;

/**
 * Class AbstractRepository
 *
 * @package Repository
 *
 * @author Jerome Catric
 */
abstract class AbstractRepository
{

    /**
     * Table name
     *
     * @var string
     */
    protected $tableName;

    /**
     * Connection to the database
     *
     * @var Connection
     */
    protected $db;

    /**
     * Convert an associative array to an object
     *
     * @param array $data the associative array
     *
     * @return mixed the object
     */
    protected abstract function convertArrayToObject(array $data);

    /**
     * Convert an object to an associative array
     *
     * @param mixed $entity The object
     *
     * @return array The associative array
     */
    protected abstract function convertObjectToArray($entity);

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param int $id The identifier.
     *
     * @return null|object The object.
     */
    public function find($id)
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('*')
            ->from($this->tableName, 't')
            ->where('t.id = :id')
            ->setParameter('id', $id);

        $stmt = $qb->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        //none result
        if (!$record) {
            return null;
        }

        $object = $this->convertArrayToObject($record);

        return $object;
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        return $this->findBy(array());
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are not supported.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $stmt = $this->getStatement($criteria, $orderBy, $limit, $offset);

        $records = $stmt->fetchAll();

        $result = array();
        if (!is_null($records) && !empty($records)) {
            foreach ($records as $record) {
                array_push($result, $this->convertArrayToObject($record));
            }
        }

        return $result;
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return null|object The object.
     *
     * @throws \Exception
     */
    public function findOneBy(array $criteria)
    {
        $stmt = $this->getStatement($criteria);

        $records = $stmt->fetchAll();

        if (count($records) == 0) {
            return null;
        } else if(count($records) > 1) {
            throw new \Exception('Not unique Record');
        }

        $object = $this->convertArrayToObject($records[0]);

        return $object;
    }

    /**
     * Get the number of records in the table
     *
     * @param array|null $criteria
     *
     * @return int the number of records
     */
    public function count(array $criteria = null)
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('count(*) AS nb')
            ->from($this->tableName, 't');

        if (!is_null($criteria) && !empty($criteria)) {
            foreach ($criteria as $columnName => $columnValue) {
                $qb->andWhere('t.' . $columnName . ' = :' . $columnName)
                    ->setParameter($columnName, $columnValue);
            }

        }

        $stmt = $qb->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        $count = intval($record['nb'], 10);

        return $count;
    }

    /**
     * Insert a new object in the table
     *
     * @param mixed $entity the new object
     *
     * @return null|object the object created
     */
    public function insert($entity)
    {
        $data = $this->convertObjectToArray($entity);
        unset($data['id']);

        try {
            $rows = $this->db->insert($this->tableName, $data);

            if ($rows) {
                return $this->find($this->db->lastInsertId());
            } else {
                throw new \InvalidArgumentException("Could not create the record");
            }

        } catch (\PDOException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * Update an object in the table
     *
     * @param mixed $entity the object to update
     */
    public function update($entity)
    {
        $data = $this->convertObjectToArray($entity);
        $id = $data['id'];
        unset($data['id']);

        $this->db->update($this->tableName, $data, array('id' => $id));
    }

    /**
     * Delete an object from the table
     *
     * @param mixed $entity the object
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function delete($entity)
    {
        $data = $this->convertObjectToArray($entity);
        $this->db->delete($this->tableName, array('id' => $data['id']));
    }

    /**
     * Delete a record in the table by id
     *
     * @param int $id id of the record
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function deleteById($id)
    {
        $this->db->delete($this->tableName, array('id' => $id));
    }

    /**
     * Generate the statement from parameters
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    private function getStatement(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('*')
            ->from($this->tableName, 't');

        if (!is_null($criteria) && !empty($criteria)) {
            foreach ($criteria as $columnName => $columnValue) {
                $qb->andWhere('t.' . $columnName . ' = :' . $columnName)
                    ->setParameter($columnName, $columnValue);
            }

        }

        if (!is_null($orderBy) && !empty($orderBy)) {
            foreach ($orderBy as $columnName => $columnValue) {
                $qb->addOrderBy($columnName, $columnValue);
            }
        }

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        if ($offset != null) {
            $qb->setFirstResult($offset);
        }

        $stmt = $qb->execute();

        return $stmt;
    }
}