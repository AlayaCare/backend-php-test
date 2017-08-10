<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Filesystem\Tests;


/**
 * Test class for Filesystem.
 */
class Demo
{
    public function getData($app,$array,$tablename,$condtion)
    {
		foreach($condtion as $key=>$each)
		{
			$con = $key."= '".$each."' and";
		}
		$con = trim($con,'and');
		$sql = "SELECT * FROM ".$tablename." WHERE ".$con;
        $todos = $app['db']->fetchAll($sql);
        return $todos;
    }
	public function getDataSingle($app,$array,$tablename,$condtion)
    {
		foreach($condtion as $key=>$each)
		{
			$con = $key."= '".$each."' and";
		}
		$con = trim($con,'and');
		$sql = "SELECT * FROM ".$tablename." WHERE ".$con;
        $todos = $app['db']->fetchAssoc($sql);
        return $todos;
    }
	public function deleteData($app,$array,$tablename,$condtion)
    {
		foreach($condtion as $key=>$each)
		{
			$con = $key."= '".$each."' and";
		}
		$con = trim($con,'and');
		$sql = "DELETE FROM ".$tablename." WHERE ".$con;
        $todos = $app['db']->executeUpdate($sql);
        return $todos;
    }
	public function insertData($app,$array,$tablename,$condtion)
    {
		$keysfull='';$valfull='';
		foreach($condtion as $key=>$each)
		{
			$keysfull.= " ".$key.",";
			$valfull.= " '".$each."',";
		}
		$keysfull = trim($keysfull,',');
		$valfull = trim($valfull,',');
		$sql = "INSERT INTO todos (".$keysfull.") VALUES (".$valfull.")";
        $todos = $app['db']->executeUpdate($sql);
        return $todos;
    }
}
