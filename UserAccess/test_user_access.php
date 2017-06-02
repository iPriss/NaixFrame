<?php
    require('UserAccess.php');

    $config = parse_ini_file("../require/config.ini", true);

    $testUser = new UserAccess();

    print_r("------------------------------------------------------------------------------------------"); echo "<br>";
    print_r("--------------------------------------- CREATE --------------------------------------- "); echo "<br>";

    $userParams = array(
        "first_name" => "Omar",
        "last_name"  => "Yerden",
        "user_name"  => "omar.yerden",
        "user_email" => "omaryer@hotmail.com"
    );

    $userCreated = $testUser -> create_user( $userParams );
    if ( !$userCreated ) { print_r($testUser->user_error); echo "<br>"; }
?>