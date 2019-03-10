<?php


namespace AC\Repository;

use AC\Entity\IEntity;

/**
 * Interface IRepository
 * @package AC\Repository
 */
interface IRepository
{
    public function findById($id);

    public function findAll();

    public function insert(IEntity $entity);

    public function update(IEntity $entity);

    public function remove($id);

    public function toObject(Array $data);
}