<?php

    require('Classes/DBAccess.php');

    $database = parse_ini_file("required/config.ini", true)['database'];
    $testDB = new DBAccess('psql', $database);

    // Test select
    $keys = array(
    	'id' => array(
    			'value' => 255, // Required
    			'operator' => '<', // Optional (SQL Operators LIKE, >, IS)
    		)
    );

    $opts = array(
    	'order_by' => array( 'id DESC', 'random ASC' ), // Order by Column and sort direction
    	'group_by' => array( 'id', 'random' ), // Group result by certain columns
    	// 'offset' => 10, // Offset
    	'limit' => 50, // Limit the result by n number
    );

    // db_select(table, keys, return_column, opts, column_key [return array with certain column as key])
    $rows = $testDB -> db_select( 'naixframe.test_select', $keys, array('id', 'random'), $opts, 'id' ); // mysql

    if ( !$testDB -> dbError ) {
        print_r($rows); echo "<br>";
    } else {
        print_r( $testDB -> dbError ); echo "<br>";
    }

    print_r("---------------------------------------------------------------------------------------"); echo "<br>";
    print_r("---------------------------------------------------------------------------------------"); echo "<br>";

    // Test insert
    $params = array( 'random' => rand(0, 99999), 'date' => date('Y-m-d H:i:s') );

    // db_insert(table, params, return_column, column_key [return array with certain column as key]) // On mysql only can return auto generated id (True or False)
    $insertedRow = $testDB -> db_insert( 'naixframe.test_select', $params, array('id', 'random'), 'id' );
    if ( !$testDB -> dbError ) {
        print_r( $insertedRow ); echo "<br>";
    } else {
        print_r( $testDB -> dbError ); echo "<br>";
    }

    print_r("---------------------------------------------------------------------------------------"); echo "<br>";
    print_r("---------------------------------------------------------------------------------------"); echo "<br>";

    // Test update
    $keys = array( 'id' => array( 'value' => 126 ) );
    $params = array( 'random' => 45 );

    // db_update(table, keys, params)
    $updatedRow = $testDB -> db_update( 'naixframe.test_select', $keys, $params );
    if ( !$testDB -> dbError ) {
        print_r( $updatedRow ); echo "<br>";
    } else {
        print_r( $testDB -> dbError ); echo "<br>";
    }

    print_r("---------------------------------------------------------------------------------------"); echo "<br>";
    print_r("---------------------------------------------------------------------------------------"); echo "<br>";

    // Test delete
    $keys = array( 'id' => array( 'value' => 130 ) );

    // db_update(table, keys, params)
    $deletedRow = $testDB -> db_delete( 'naixframe.test_select', $keys );
    if ( !$testDB -> dbError ) {
        print_r( $deletedRow ); echo "<br>";
    } else {
        print_r( $testDB -> dbError ); echo "<br>";
    }

?>