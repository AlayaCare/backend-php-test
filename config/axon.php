<?php

/**
	Axon ORM for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2010 F3 Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Axon
		@version 1.4.2
**/

//! Axon Object Relational Mapper
class Axon extends Core {

	//! Minimum framework version required to run
	const F3_Minimum='1.4.2';

	//@{
	//! Locale-specific error/exception messages
	const
		TEXT_AxonTable='Unable to map table {@CONTEXT} to Axon',
		TEXT_AxonEmpty='Axon is empty',
		TEXT_AxonNotMapped='The field {@CONTEXT} does not exist',
		TEXT_AxonCantUndef='Cannot undefine an Axon-mapped field',
		TEXT_AxonCantUnset='Cannot unset an Axon-mapped field',
		TEXT_AxonConflict='Name conflict with Axon-mapped field',
		TEXT_AxonInvalid='Invalid virtual field expression',
		TEXT_AxonReadOnly='Virtual fields are read-only',
		TEXT_AxonEngine='Database engine is not supported';
	//@}

	//@{
	//! Axon properties
	private $db=NULL;
	private $table=NULL;
	private $keys=array();
	private $criteria=NULL;
	private $order=NULL;
	private $offset=NULL;
	private $fields=array();
	private $virtual=array();
	private $empty=TRUE;
	//@}

	/**
		Similar to Axon->find method but provides more fine-grained control
		over specific fields and grouping of results
			@param $fields string
			@param $criteria mixed
			@param $grouping mixed
			@param $order mixed
			@param $limit mixed
			@param $offset mixed
			@param $ttl integer
			@public
	**/
	public function lookup(
		$fields,$criteria=NULL,$grouping=NULL,$order=NULL,
		$limit=NULL,$offset=NULL,$ttl=0) {
			return SQLdb::sql(
				'SELECT '.$fields.' FROM '.$this->table.
					(is_null($criteria)?'':(' WHERE '.$criteria)).
					(is_null($grouping)?'':(' GROUP BY '.$grouping)).
					(is_null($order)?'':(' ORDER BY '.$order)).
					(is_null($limit)?'':(' LIMIT '.$limit)).
					(is_null($offset)?'':(' OFFSET '.$offset)).';',
				$this->db,
				$ttl
			);
	}

	/**
		Alias of the lookup method
			@public
	**/
	public function select() {
		// PHP doesn't allow direct use as function argument
		$args=func_get_args();
		return call_user_func_array(array($this,'lookup'),$args);
	}

	/**
		Return an array of DB records matching criteria
			@return array
			@param $criteria mixed
			@param $order mixed
			@param $limit mixed
			@param $offset mixed
			@param $ttl integer
			@public
	**/
	public function find(
		$criteria=NULL,$order=NULL,$limit=NULL,$offset=NULL,$ttl=0) {
			return $this->lookup(
				'*',$criteria,NULL,$order,$limit,$offset,$ttl);
	}

	/**
		Return the first record that matches the specified criteria
			@return array
			@param $criteria mixed
			@param $order mixed
			@param $offset mixed
			@param $ttl integer
			@public
	**/
	public function findOne($criteria=NULL,$order=NULL,$offset=NULL,$ttl=0) {
		list($result)=$this->find($criteria,$order,1,$offset,$ttl);
		return $result;
	}

	/**
		Return number of DB records that match criteria
			@return integer
			@param $criteria mixed
			@public
	**/
	public function found($criteria=NULL) {
		list($result)=$this->lookup('COUNT(*) AS found',$criteria);
		return $result['found'];
	}

	/**
		Hydrate Axon with elements from framework array variable, keys of
		which must be identical to field names in DB record; Virtual fields
		are read-only
			@param $name string
			@public
	**/
	public function copyFrom($name) {
		foreach (array_keys($this->fields) as $field)
			if (is_array(F3::get($name)) &&
				array_key_exists($field,F3::get($name)))
					$this->fields[$field]=F3::get($name.'.'.$field);
		$this->empty=FALSE;
	}

	/**
		Populate framework array variable with Axon properties, keys of
		which will have names identical to fields in DB record
			@param $name string
			@param $fields string
			@public
	**/
	public function copyTo($name,$fields=NULL) {
		$list=array_diff(explode('|',$fields),array(''));
		$fields=array_keys($this->fields);
		$virtual=array_keys($this->virtual);
		foreach ($virtual?array_merge($fields,$virtual):$fields as $field)
			if (empty($list) || in_array($field,$list)) {
				if (in_array($field,array_keys($this->fields)))
					F3::set($name.'.'.$field,$this->fields[$field]);
				if ($this->virtual &&
					in_array($field,array_keys($this->virtual)))
                    F3::set($name.'.'.$field,$this->virtual[$field]);
			}
	}

	/**
		Dehydrate Axon
			@public
	**/
	public function reset() {
		// Null out fields
		foreach (array_keys($this->fields) as $field)
			$this->fields[$field]=NULL;
		if ($this->keys)
			// Null out primary keys
			foreach (array_keys($this->keys) as $field)
				$this->keys[$field]=NULL;
		if ($this->virtual)
			// Null out virtual keys
			foreach (array_keys($this->virtual) as $field)
				unset($this->virtual[$field]['value']);
		// Dehydrate Axon
		$this->empty=TRUE;
		$this->criteria=NULL;
		$this->order=NULL;
		$this->offset=NULL;
	}

	/**
		Retrieve first DB record that satisfies criteria
			@param $criteria mixed
			@param $order mixed
			@param $offset integer
			@public
	**/
	public function load($criteria=NULL,$order=NULL,$offset=0) {
		// Execute beforeLoad event
		if (method_exists($this,'beforeLoad') && !$this->beforeLoad())
			return;
		if ($offset>-1) {
			$virtual='';
			foreach ($this->virtual as $field=>$value)
				$virtual.=',('.$value['expr'].') AS '.$field;
			// Retrieve record
			$result=$this->lookup(
				'*'.$virtual,$criteria,NULL,$order,'1 OFFSET '.$offset
			);
			$this->offset=NULL;
			if ($result) {
				// Hydrate Axon
				foreach ($result[0] as $field=>$value)
					if (array_key_exists($field,$this->fields)) {
						$this->fields[$field]=$value;
						if (array_key_exists($field,$this->keys))
							$this->keys[$field]=$value;
					}
					else
						$this->virtual[$field]['value']=$value;
				$this->empty=FALSE;
				$this->criteria=$criteria;
				$this->order=$order;
				$this->offset=$offset;
			}
			else
				$this->reset();
		}
		else
			$this->reset();
		if (method_exists($this,'afterLoad'))
			// Execute afterLoad event
			$this->afterLoad();
	}

	/**
		Retrieve N-th record relative to current using the same criteria
		that hydrated the Axon
			@param $count integer
			@public
	**/
	public function skip($count=1) {
		if ($this->dry()) {
			trigger_error(self::TEXT_AxonEmpty);
			return;
		}
		$this->load($this->criteria,$this->order,$this->offset+$count);
	}

	/**
		Insert/update DB record
			@public
	**/
	public function save() {
		if ($this->empty) {
			// Axon is empty
			trigger_error(self::TEXT_AxonEmpty);
			return;
		}
		// Execute beforeSave event
		if (method_exists($this,'beforeSave') && !$this->beforeSave())
			return;
		$new=TRUE;
		if ($this->keys)
			// If ALL primary keys are NULL, this is a new record
			foreach ($this->keys as $value)
				if (!is_null($value)) {
					$new=FALSE;
					break;
				}
		if ($new) {
			// Insert new record
			$fields='';
			$values='';
			foreach ($this->fields as $field=>$value) {
				$fields.=($fields?',':'').$field;
				$values.=($values?',':'').':'.$field;
				$bind[':'.$field]=array($value,SQLdb::type($value));
			}
			SQLdb::sqlBind(
				'INSERT INTO '.$this->table.' ('.$fields.') '.
					'VALUES ('.$values.');',
				$bind,$this->db
			);
		}
		else {
			// Update record
			$set='';
			foreach ($this->fields as $field=>$value) {
				$set.=($set?',':'').($field.'=:'.$field);
				$bind[':'.$field]=array($value,SQLdb::type($value));
			}
			// Use prior primary key values (if changed) to find record
			$cond='';
			foreach ($this->keys as $key=>$value) {
				$cond.=($cond?' AND ':'').($key.'=:c_'.$key);
				$bind[':c_'.$key]=array($value,SQLdb::type($value));
			}
			SQLdb::sqlBind(
				'UPDATE '.$this->table.' SET '.$set.
					(is_null($cond)?'':(' WHERE '.$cond)).';',
				$bind,$this->db
			);
		}
		if ($this->keys)
			// Update primary keys with new values
			foreach (array_keys($this->keys) as $field)
				$this->keys[$field]=$this->fields[$field];
		if (method_exists($this,'afterSave'))
			// Execute afterSave event
			$this->afterSave();
	}

	/**
		Delete DB record and reset Axon
			@public
	**/
	public function erase() {
		if ($this->empty) {
			trigger_error(self::TEXT_AxonEmpty);
			return;
		}
		// Execute beforeErase event
		if (method_exists($this,'beforeErase') && !$this->beforeErase())
			return;
		$cond=$this->criteria;
		SQLdb::sql(
			'DELETE FROM '.$this->table.
				(is_null($cond)?'':(' WHERE '.$cond)).';',
			$this->db
		);
		$this->reset();
		if (method_exists($this,'afterErase'))
			// Execute afterErase event
			$this->afterErase();
	}

	/**
		Return TRUE if Axon is devoid of values in its properties
			@return boolean
			@public
	**/
	public function dry() {
		return $this->empty;
	}

	/**
		Synchronize Axon and table structure
			@param $table string
			@param $id string
			@public
	**/
	public function sync($table,$id='DB') {
		$db=&self::$global[$id];
		// Can't proceed until DSN is set
		if (!$db || !$db['dsn']) {
			trigger_error(SQLdb::TEXT_DBConnect);
			return;
		}
		$result=array(
			// MySQL schema
			'mysql'=>array(
				'SHOW columns FROM '.$table.';','Field','Key','PRI'),
			'sqlite2?'=>array(
				'PRAGMA table_info('.$table.');','name','pk',1),
			'(mssql|sybase|dblib|pgsql)'=>array(
				'SELECT c.column_name AS field,t.constraint_type AS key '.
				'FROM information_schema.columns AS c '.
				'LEFT OUTER JOIN '.
					'information_schema.key_column_usage AS k ON '.
						'c.table_name=k.table_name AND '.
						'c.column_name=k.column_name '.
				'LEFT OUTER JOIN '.
					'information_schema.table_constraints AS t ON '.
						'k.table_name=t.table_name AND '.
						'k.constraint_name=t.constraint_name '.
				'WHERE '.
					'c.table_name="'.$table.'";','field','key','PRIMARY KEY')
		);
		$match=FALSE;
		foreach ($result as $dsn=>$val)
			if (preg_match('/^'.$dsn.':/',$db['dsn'])) {
				$match=TRUE;
				break;
			}
		if (!$match) {
			// Unsupported DB back-end
			trigger_error(self::TEXT_AxonEngine);
			return;
		}
		// Execute beforeSync event
		if (method_exists($this,'beforeSync') && !$this->beforeSync())
			return;
		$result=SQLdb::sql($val[0],$id,self::$global['SYNC']);
		if (!$result) {
			self::$global['CONTEXT']=$table;
			trigger_error(self::TEXT_AxonTable);
			return;
		}
		// Initialize Axon
		$this->db=$id;
		$this->table=$table;
		foreach ($result as $col) {
			// Populate properties
			$this->fields[$col[$val[1]]]=NULL;
			if ($col[$val[2]]==$val[3])
				// Save primary key
				$this->keys[$col[$val[1]]]=NULL;
		}
		$this->empty=TRUE;
		if (method_exists($this,'afterSync'))
			// Execute afterSync event
			$this->afterSync();
	}

	/**
		Create a virtual field
			@param $name string
			@param $expr string
			@public
	**/
	public function def($name,$expr) {
		if (array_key_exists($name,$this->fields)) {
			trigger_error(self::TEXT_AxonConflict);
			return;
		}
		if (!is_string($expr) || !strlen($expr)) {
			trigger_error(self::TEXT_AxonInvalid);
			return;
		}
		$this->virtual[$name]['expr']=F3::resolve($expr);
	}

	/**
		Destroy a virtual field
			@param $name string
			@public
	**/
	public function undef($name) {
		if (array_key_exists($name,$this->fields)) {
			trigger_error(self::TEXT_AxonCantUndef);
			return;
		}
		if (self::isdef($name)) {
			unset($this->virtual[$name]);
			return;
		}
		self::$global['CONTEXT']=$name;
		trigger_error(self::TEXT_AxonNotMapped);
	}

	/**
		Return TRUE if virtual field exists
			@param $name
			@public
	**/
	public function isdef($name) {
		return array_key_exists($name,$this->virtual);
	}

	/**
		Return value of Axon-mapped/virtual field
			@return mixed
			@param $name string
			@public
	**/
	public function __get($name) {
		if (array_key_exists($name,$this->fields))
			return $this->fields[$name];
		if (array_key_exists($name,$this->virtual))
			return $this->virtual[$name]['value'];
		self::$global['CONTEXT']=$name;
		trigger_error(self::TEXT_AxonNotMapped);
	}

	/**
		Assign value to Axon-mapped field
			@return boolean
			@param $name string
			@param $value mixed
			@public
	**/
	public function __set($name,$value) {
		if (array_key_exists($name,$this->fields)) {
			$this->fields[$name]=is_string($value)?
				F3::resolve($value):$value;
			if (!is_null($value))
				// Axon is now hydrated
				$this->empty=FALSE;
			return;
		}
		if (array_key_exists($name,$this->virtual)) {
			trigger_error(self::TEXT_AxonReadOnly);
			return;
		}
		self::$global['CONTEXT']=$name;
		trigger_error(self::TEXT_AxonNotMapped);
	}

	/**
		Clear value of Axon-mapped field
			@return boolean
			@param $name string
			@public
	**/
	public function __unset($name) {
		if (array_key_exists($name,$this->fields)) {
			trigger_error(self::TEXT_AxonCantUnset);
			return;
		}
		self::$global['CONTEXT']=$name;
		trigger_error(self::TEXT_AxonNotMapped);
	}

	/**
		Return TRUE if Axon-mapped/virtual field exists
			@return boolean
			@param $name string
			@public
	**/
	public function __isset($name) {
		return array_key_exists(
			$name,array_merge($this->fields,$this->virtual)
		);
	}

	/**
		Display class name if conversion to string is attempted
			@public
	**/
	public function __toString() {
		return get_class($this);
	}

	/**
		Intercept calls to undefined object methods
			@param $func string
			@param $args array
			@public
	**/
	public function __call($func,array $args) {
		self::$global['CONTEXT']=$func;
		trigger_error(self::TEXT_Method);
	}

	/**
		Mapper constructor
			@public
	**/
	public function __construct() {
		// Execute mandatory sync method of child class
		call_user_func_array(
			array(get_called_class(),'sync'),func_get_args()
		);
	}

}
