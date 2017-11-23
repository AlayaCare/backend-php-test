<?php

namespace Custom;

Class Pagination {
	
	public $table;
	Public $total_count;
	public $rows_per_page;
	public $total_pages;
	public $current_page;
	public $offset;
	public $prev_page;
	public $range; //range of num link to show;
	
	public function __construct($per_page=2, $page=1 , $total_count = 0)
	{
		$this->total_count = (int)$total_count;
		$this->rows_per_page = (int)$per_page;
		$this->current_page = (int)$page;
	}
	
	public function get_total_page()
	{
		return $this->total_pages = ceil($this->total_count/$this->rows_per_page);
	}
	
	public function offset(){
		//get the off set current page minus 1 multiply by record per page	
		return $this->offset = ($this->current_page - 1) * $this->rows_per_page;
	}
	
	public function previous_page(){
		//move to previous record by subtracting one into the current record
		return  $this->current_page - 1;
	}
	
	public function next_page(){
		//mvove to next record by incrementing the current page by one		
		return  $this->current_page + 1;
	}
	
	public function has_previous_page(){
		//check if previous record is still greater than one then it returns to true
		return $this->previous_page() >= 1 ? true : false;
	}
	
	public function has_next_page(){
		//check if Next record is still lesser than one total pages then it returns to true
		return  $this->next_page() <= $this->total_pages() ? true : false;
	}
}