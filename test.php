<?php

    require('Classes/DBAccess.php');

    $database = parse_ini_file("required/config.ini", true)['database'];
    $testDB = new DBAccess('mysql', $database);

    // Test select
    $keys = array(
    	'id' => array(
    			'value' => 45, // Required
    			'operator' => '<', // Optional (SQL Operators LIKE, >, IS)
    		)
    );

    $opts = array(
    	'order_by' => array( 'id DESC', 'random ASC' ), // Order by Column and sort direction
    	'group_by' => array( 'random' ), // Group result by certain columns
    	'offset' => 10, // Offset
    	'limit' => 10, // Limit the result by n number
    );

    // db_select(table, keys, return_column, opts, column_key [return array with certain column as key])
    // $rows = $testDB -> db_select( 'mainserver.session', array( 'user_identifier' => array('value' => '%OMAR%') ) ); // pgsql
    $rows = $testDB -> db_select( 'naixframe.test_select', $keys, array('id', 'random'), $opts, 'id' ); // mysql

    print_r($rows); echo "<br>";

    print_r("---------------------------------------------------------------------------------------"); echo "<br>";
    print_r("---------------------------------------------------------------------------------------"); echo "<br>";

    // Test insert
    $params = array(
    	'random' => "PPPPP",
    );
    
    $insertedRow = $testDB -> db_insert( 'naixframe.test_select', $params, array('id') );
    var_dump( $insertedRow ); echo "<br>";

?>