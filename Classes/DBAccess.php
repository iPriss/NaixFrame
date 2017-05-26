<?php

class DBAccess {

    private $dbEngine;
    private $dbConn;
    private $dbQuery;
    private $dbResult;

    function __construct($db='pgsql') {
        $database = parse_ini_file("../required/config.ini", true)['database'];
        $this -> dbEngine = $db;
        $this -> dbConn = $this -> db_connect($database);
    }

    private function db_connect ($database) {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_connect( $database );
            case 'mysql':
                // return mysql_connect( $database );
                return mysqli_connect( $database );
                // return new mysqli( $database );
        }
    }

    private function db_begin () {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_query( $this->dbConn, 'BEGIN' );
            case 'mysql':
                return mysqli_query( $this->dbConn, 'BEGIN' );
        }
    }

    private function db_commit () {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_query( $this->dbConn, 'COMMIT' );
            case 'mysql':
                return mysqli_query( $this->dbConn, 'COMMIT' );
        }
    }

    private function db_rollback () {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_query( $this->dbConn, 'ROLLBACK' );
            case 'mysql':
                return mysqli_query( $this->dbConn, 'ROLLBACK' );
        }
    }

    private function db_execute () {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_query( $this->dbConn, $this->dbQuery );
            case 'mysql':
                return mysqli_query( $this->dbConn, $this->dbQuery );
        }
    }

    private function db_affected_rows () {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_affected_rows( $this -> dbResult );
            case 'mysql':
                return mysqli_affected_rows( $this -> dbResult );
        }
    }

    private function db_fetch_all () {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_fetch_all( $this -> dbResult );
            case 'mysql':
                return mysqli_fetch_all( $this -> dbResult );
        }
    }

    private function db_escape_string ($string) {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_escape_string($string);
            case 'mysql':
                return mysql_real_escape_string($string);
        }
    }

    public function db_select ( $table='', $keys=array(), $return_params=array(), $opts=array(), $return_key='' ) {
        # Empty table return false
        if( empty($table) ) return False;

        $return_params = ( count( $return_params ) > 0 ) ? implode(', ', $return_params) : '*';

        $this -> dbQuery = 'SELECT ' . $return_params . ' FROM ' . $table;

        if ( count($keys) > 0 ){
            foreach ($keys as $key => $def) {
                $this -> dbQuery .= ( strpos( $this -> dbQuery, 'WHERE' ) === False ) ? ' WHERE ' : ' AND ';
                $this -> dbQuery .= $key;

                $null = ( array_key_exists('use_null', $def) && $def['use_null'] === True && ( empty($def['value']) || $def['value'] == 'NULL' ) ) ? TRUE : FALSE;

                if ( !array_key_exists('operator', $def) ) {
                    $this -> dbQuery =  ($null) ? ' IS' : ' =';
                } else {
                    $this -> dbQuery =  ' ' . $def['operator'];
                }

                $this -> dbQuery .=  $this -> db_escape_string( $def['value'] ) . ' ';
            }
        } // END $key process

        // Adding opts
        if ( count( $opts ) > 0 ) {
            $this -> dbQuery .= ( array_key_exists('group_by', $opts) && is_array($opts['group_by']) ) ? ' GROUP BY ' . implode(', ', $opts['group_by']) : '';
            $this -> dbQuery .= ( array_key_exists('order_by', $opts) && is_array($opts['order_by']) ) ? ' ORDER BY ' . implode(', ', $opts['order_by']) : '';
            $this -> dbQuery .= ( array_key_exists('limit', $opts) )  ? ' LIMIT ' . (string) $opts['limit']  : '';
            $this -> dbQuery .= ( array_key_exists('offset', $opts) ) ? ' OFSET ' . (string) $opts['offset'] : '';
        }

        $this -> dbResult = $this -> db_execute();

        if ( !$this -> dbResult ) {
            return False;
        } else if ( $this -> db_affected_rows() <= 0 ) {
            return 0
        } else {
            $rows = db_fetch_all();
            if( !empty( $return_key ) ){
                $rows = array();
                foreach ( db_fetch_all() as $key => $val ) {
                    $rows[ $val[ $return_key ] ] = $val;
                }
            }
            return $rows;
        }

    } // END db_select

}

?>