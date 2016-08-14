<?php

class Todo {
	
	    private $app;

    function __construct( $app ) {
		        $this->app = $app;
		   }
		
		    
	
			    function add( array $params ) {
				       $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
				        $data = $this->app['db']->executeUpdate( $sql, [$params['user_id'], $params['description']] );
			        return $data;
			    }
			    
			    function delete( array $params ) {
			    	$sql = "DELETE FROM todos WHERE id = ? AND user_id = ?";
			    	$data = $this->app['db']->executeUpdate( $sql, [$params['id'], $params['user_id']] );
			    	return $data;
			    }
				
			   function getvals( array $params ) {
					
					
					       if ( isset( $params['id'] ) ) {
						            $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";
						
						          return $this->app['db']->fetchAssoc( $sql, [$params['id'], $params['user_id']] );
						        } else {
							           $sql = "SELECT * FROM todos WHERE user_id = ?";
						
							          return $todos = $this->app['db']->fetchAll( $sql, [$params['user_id']] );
						       }
							    }
							
							}
							?>