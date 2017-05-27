<?php

class DBAccess {

    private $dbEngine;
    private $dbConn;
    private $dbQuery;
    private $dbResult;

    function __construct($db='pgsql', $database) {
        // $database = parse_ini_file("../required/config.ini", true)['database'];
        $this -> dbEngine = $db;
        $this -> dbConn = $this -> db_connect($database);
    }

    private function db_connect ($database) {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_connect( $database );
            case 'mysql':
                list($server, $username, $password, $db) = $database;
                return mysqli_connect( $server, $username, $password, $db );
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
                return mysqli_affected_rows( $this -> dbConn );
        }
    }

    private function db_fetch_all () {
        switch ($this -> dbEngine) {
            case 'psql':
                return pg_fetch_all( $this -> dbResult );
            case 'mysql':
                return mysqli_fetch_all( $this -> dbResult, MYSQLI_ASSOC );
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

    private function add_quotes( $string ) {
        return '\'' . $string . '\'';
    }

    private function prepare_where_statment ($keys) {
        foreach ($keys as $key => $def) {
            $this -> dbQuery .= ( strpos( $this -> dbQuery, 'WHERE' ) === False ) ? ' WHERE ' : ' AND ';
            $this -> dbQuery .= $key;

            $null = ( array_key_exists('use_null', $def) && $def['use_null'] === True && ( empty($def['value']) || $def['value'] == 'NULL' ) ) ? TRUE : FALSE;

            if ( !array_key_exists('operator', $def) && strpos( $def['value'], '%' ) !== False ) {
                $this -> dbQuery .=  ' LIKE';                
            } else if ( !array_key_exists('operator', $def) ) {
                $this -> dbQuery .=  ($null) ? ' IS' : ' =';
            } else {
                $this -> dbQuery .=  ' ' . $def['operator'];
            }

            $this -> dbQuery .= ( is_string( $def['value'] ) ) ? ' \'' . $this -> db_escape_string( $def['value'] ) . '\' ' : ' ' . $this -> db_escape_string( $def['value'] );
        }
    }

    /**
     * @param  string
     * @param  array
     * @param  array
     * @param  array
     * @param  string
     * @return array
     */
    public function db_select ( $table='', $keys=array(), $return_params=array(), $opts=array(), $return_key='' ) {
        # Empty table return false
        if( empty($table) ) return False;
        $this -> dbQuery = Null;

        $return_params = ( count( $return_params ) > 0 ) ? implode(', ', $return_params) : '*';

        $this -> dbQuery = 'SELECT ' . $return_params . ' FROM ' . $table;

        if ( count($keys) > 0 ){ $this -> prepare_where_statment($keys); } 

        // Adding opts
        if ( count( $opts ) > 0 ) {
            $this -> dbQuery .= ( array_key_exists('group_by', $opts) && is_array($opts['group_by']) ) ? ' GROUP BY ' . implode(', ', $opts['group_by']) : '';
            $this -> dbQuery .= ( array_key_exists('order_by', $opts) && is_array($opts['order_by']) ) ? ' ORDER BY ' . implode(', ', $opts['order_by']) : '';
            $this -> dbQuery .= ( array_key_exists('limit', $opts) )  ? ' LIMIT ' . (string) $opts['limit']  : '';
            $this -> dbQuery .= ( array_key_exists('offset', $opts) ) ? ' OFFSET ' . (string) $opts['offset'] : '';
        }

        $this -> dbResult = $this -> db_execute();

        if ( !$this -> dbResult ) { return False; } 
        if ( $this -> db_affected_rows() <= 0 ) { return 0; } 

        $rows = $this -> db_fetch_all();
        if( !empty( $return_key ) ){
            $rrows = array();
            foreach ( $rows as $key => $val ) {
                $rrows[ $val[ $return_key ] ] = $val;
            }
            $rows = $rrows;
        }
        return $rows;
    } // END db_select

    /**
     * [Insert params into requested table]
     * @param  string $table         [table in wich params going to be insert]
     * @param  array  $params        [array in wich key is going to be the name of the column, value going to be the value]
     * @param  array  $return_params [columns wich the query is going to return]
     * @return array                 [array]
     */
    public function db_insert( $table='', $params=array(), $return_params=array() ) {
        # Empty table return false
        if( empty($table) || count($params) <= 0 ) return False;
        $this -> dbQuery = Null;

        $values  = implode( ', ', array_map( array($this, "add_quotes"), array_map( array($this, 'db_escape_string'), array_values($params) ) ) );
        $columns = implode( ', ', array_keys($params) );

        $this -> dbQuery = "INSERT INTO $table ($columns) VALUES ($values) ";

        if( $this -> dbEngine == 'psql' ) {
            $this -> dbQuery .= ( count( $return_params ) > 0 ) ? implode(', ', $return_params) : '';
        }

        $this -> dbResult = $this -> db_execute();

        if( $this -> dbEngine == 'psql' ) {
            if ( !$this -> dbResult ) { return False; } 
            if ( $this -> db_affected_rows() <= 0 ) { return 0; } 

            $rows = $this -> db_fetch_all();
            if( !empty( $return_key ) ){
                $rrows = array();
                foreach ( $rows as $key => $val ) {
                    $rrows[ $val[ $return_key ] ] = $val;
                }
                $rows = $rrows;
            }
            return $rows;
        }else if( $this -> dbEngine == 'psql' ) {
            if( count( $return_params ) > 0 ) {

            }else{
                return $this -> db_affected_rows();
            }
        }

    } // END db_insert 

}

?>