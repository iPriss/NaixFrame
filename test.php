<?php

    require('Classes/DBAccess.php');

    $database = parse_ini_file("required/config.ini", true)['database'];
    $testDB = new DBAccess('mysql', $database);

    // Test select
    // $rows = $testDB -> db_select( 'mainserver.session', array( 'user_identifier' => array('value' => '%OMAR%') ) ); // pgsql
    $rows = $testDB -> db_select( 'naixframe.test_select', array( "id" => array("operator" => "<", "value" => 45) ), array('id', 'random'), array('order_by' => array('id DESC', 'random ASC'),'offset' => 10, 'limit' => 10), 'id' ); // mysql
    print_r($rows); echo "<br>";

    $testDB -> db_insert();

?>