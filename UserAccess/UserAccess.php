<?php

require('../DBAccess/DBAccess.php');

class UserAccess extends DBAccess {

    private $userId;

    public $user_error;

    public $first_name;
    public $last_name;
    public $user_name;
    public $user_email;
    public $user_status;

    private $registration_time;
    private $email_confirmation_token;
    private $password_reminder_token;
    private $password_reminder_expire;

    function __construct() { }

    private function validate_params ( $params=array(), $validParams=array() ) {
        $valid = True;
        foreach ($validParams as $key => $val) {
            if ( !array_key_exists($key, $params) && $val['required'] === True ){
                $valid = False; break;
            }else if( array_key_exists($key, $params) && empty($params[$key]) && $val['null'] === False ){
                $valid = False; break;
            }
        }
        return $valid;
    }

    public function create_user ($inParams) {
        $validParams = array(
            "first_name" => array("required" => False, "null" => False),
            "last_name"  => array("required" => False, "null" => False),
            "user_name"  => array("required" => True,  "null" => False),
            "user_email" => array("required" => True,  "null" => False)
        );

        if( !$this -> validate_params( $inParams, $validParams ) ){
            $this -> user_error = "Invalid or missing input param";
            return False;
        }
    }

    function __destruct() { }
}

?>