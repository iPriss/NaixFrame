<?php

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");

class DBAccess {

    private $dbEngine;
    private $dbConn;
    private $dbQuery;
    private $dbResult;

    public $dbError;

    function __construct($db='psql', $database) {
        $this -> dbEngine = $db;
        $this -> dbConn = $this -> db_connect($database);
    }

    private function db_connect ($database) {
        switch ($this -> dbEngine) {
            case 'psql':
                $conn = False;
                try { $conn = @pg_connect($database); }
                Catch (Exception $e) { $this -> dbError = $e->getMessage(); }
                restore_error_handler();
                return $conn;
            case 'mysql':
                $conn = False;
                list($server, $username, $password, $db) = $database;
                $conn = @mysqli_connect( $server, $username, $password, $db );

                if ( mysqli_connect_errno() ) {
                    $this -> dbError = mysqli_connect_errno();
                    return False;
                }else{
                    return $conn;
                }
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
                $result = @pg_query( $this->dbConn, $this->dbQuery );
                $this -> dbError = pg_last_error( $this->dbConn );
                return $result;
            case 'mysql':
                $result = @mysqli_query( $this->dbConn, $this->dbQuery );
                $this -> dbError = mysqli_error( $this->dbConn );
                return $result;
        }
    }

    private function db_affected_rows () {
        switch ($this -> dbEngine) {
            case 'psql':
                if ( $this -> dbResult ) { return pg_affected_rows( $this -> dbResult ); }
                else { return 0; }
            case 'mysql':
                if ( $this -> dbConn ) { return mysqli_affected_rows( $this -> dbConn ); }
                else { return 0; }
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
        if ( !is_numeric($string) ) {
            return '\'' . $string . '\'';
        }else{
            return $string;
        }
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

    private function add_key_to_rows ($return_key, $rows) {
        $rrows = array();
        foreach ( $rows as $key => $val ) {
            $rrows[ $val[ $return_key ] ] = $val;
        }
        return $rrows;
    }

    /**
     * [db_select description]
     * @param  string $table         [table in wich params going to be insert]
     * @param  array  $keys          [array with columns used in were clause, key is the column name, and had an array inside with a value]
     * @param  array  $return_params [array with each column returned]
     * @param  array  $opts          [array with QUERY options as group_by, order_by, limit, offset]
     * @param  string $return_key    [name of the column witch is gonna be used as return array key]
     * @return array                 [array with the result]
     */
    public function db_select ( $table='', $keys=array(), $return_params=array(), $opts=array(), $return_key='' ) {
        if ( $this -> dbConn === False ){ return False; }

        # Empty table return false
        if( empty($table) ) return False;
        $this -> dbQuery = Null;
        $this -> dbError = Null;

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

        return ( !empty( $return_key ) ) ? $this->add_key_to_rows( $return_key, $this -> db_fetch_all() ) : $this -> db_fetch_all();
    } // END db_select

    /**
     * [Insert params into requested table]
     * @param  string $table         [table in wich params going to be insert]
     * @param  array  $params        [array in wich key is going to be the name of the column, value going to be the value]
     * @param  array  $return_params [columns wich the query is going to return]
     * @return array                 [array with the result]
     */
    public function db_insert ( $table='', $params=array(), $return_params=array(), $return_key='' ) {
        if ( $this -> dbConn === False ){ return False; }

        # Empty table return false
        if( empty($table) || count($params) <= 0 ) return False;
        $this -> dbQuery = Null;
        $this -> dbError = Null;

        $values  = implode( ', ', array_map( array($this, "add_quotes"), array_map( array($this, 'db_escape_string'), array_values($params) ) ) );
        $columns = implode( ', ', array_keys($params) );

        $this -> dbQuery = "INSERT INTO $table ($columns) VALUES ($values) ";

        if( $this -> dbEngine == 'psql' ) {
            $this -> dbQuery .= ( count( $return_params ) > 0 ) ? 'RETURNING ' .  implode(', ', $return_params) : '';
        }

        $this -> dbResult = $this -> db_execute();

        if ( !$this -> dbResult ) { return False; }
        if ( $this -> db_affected_rows() <= 0 ) { return 0; }

        if( $this -> dbEngine == 'psql' ) {
            if ( count( $return_params ) > 0 ) {
                return ( !empty( $return_key ) ) ? $this->add_key_to_rows( $return_key, $this -> db_fetch_all() ) : $this -> db_fetch_all();
            } else {
                return $this -> db_affected_rows();
            }
        } else if ( $this -> dbEngine == 'mysql' ) {
            if ( count( $return_params ) > 0 ) {
                return mysqli_insert_id( $this->dbConn ); // Revisar
            } else {
                return $this -> db_affected_rows();
            }
        }
    } // END db_insert

    /**
     * [db_update description]
     * @param  string $table  [table in wich params going to be insert]
     * @param  array  $keys   [array with columns used in were clause, key is the column name, and had an array inside with a value]
     * @param  array  $params [array with the params used to update]
     * @return numeric        [affected rows]
     */
    public function db_update ( $table='', $keys=array(), $params=array() ) {
        if ( $this -> dbConn === False ){ return False; }

        # Empty table return false
        if( empty($table) || count($params) <= 0 ) return False;
        $this -> dbQuery = Null;
        $this -> dbError = Null;

        $this -> dbQuery = "UPDATE $table SET";

        foreach( $params as $key => $val ) {
            $operator = ( $val == Null ) ? ' IS ' : ' = ';
            $this -> dbQuery .= ' ' . $key . $operator . $this -> add_quotes ( $this -> db_escape_string( $val ) );
        }

        if ( count($keys) > 0 ){ $this -> prepare_where_statment($keys); }

        $this -> dbResult = $this -> db_execute();

        if ( !$this -> dbResult ) { return False; }
        return $this -> db_affected_rows();
    } // END db_update

    /**
     * [db_delete description]
     * @param  string $table [table in wich params going to be insert]
     * @param  array  $keys  [array with columns used in were clause, key is the column name, and had an array inside with a value]
     * @return numeric       [affected rows]
     */
    public function db_delete ( $table='', $keys=array() ) {
        if ( $this -> dbConn === False ){ return False; }

        # Empty table return false
        if( empty($table) ) return False;
        $this -> dbQuery = Null;
        $this -> dbError = Null;

        $this -> dbQuery = "DELETE FROM $table";

        if ( count($keys) > 0 ){ $this -> prepare_where_statment($keys); }

        $this -> dbResult = $this -> db_execute();

        if ( !$this -> dbResult ) { return False; }
        return $this -> db_affected_rows();
    } // END db_delete

}

?>