<?php


namespace AC\Entity;

/**
 * Interface IEntity
 * @package AC\Entity
 */
interface IEntity
{
 public function fill(Array $data);
 public function toArray();
}