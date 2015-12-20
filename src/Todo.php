<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Todo
 *
 * @author eric
 */
class Todo {

    private $app;

    function __construct( $app ) {
        $this->app = $app;
    }

    function delete( array $params ) {
        $sql = "DELETE FROM todos WHERE id = ? AND user_id = ?";
        $operation_status = $this->app['db']->executeUpdate( $sql, [$params['id'], $params['user_id']] );
        return $operation_status;
    }

    function create( array $params ) {
        $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
        $operation_status = $this->app['db']->executeUpdate( $sql, [$params['user_id'], $params['description']] );
        return $operation_status;
    }

    function get( array $params ) {


        if ( isset( $params['id'] ) ) {
            $sql = "SELECT * FROM todos WHERE id = ? AND user_id = ?";

            return $this->app['db']->fetchAssoc( $sql, [$params['id'], $params['user_id']] );
        } else {
            $sql = "SELECT * FROM todos WHERE user_id = ?";

            return $todos = $this->app['db']->fetchAll( $sql, [$params['user_id']] );
        }
    }

}
