<?php

$DB_HOST = '127.0.0.1';
$DB_NAME = 'cse360fa_t29-32';

$USER_SELECT = '';
$PASS_SELECT = '';

$USER_INSERT = '';
$PASS_INSERT = '';

$USER_DELETE = '';
$PASS_DELETE = '';

$PassSaltConstant = 'SkyHighPass';

$adminTable = 't29_skyhigh_admins';
$userTable = 't29_skyhigh_users';
$rateTable = 't29_skyhigh_rates';
$bookedFlightsTable = 't29_skyhigh_bookedflights';


/*
This function uses the cookie stored during login to check that the user
is using a valid account. If the cookies is not present or the account data
is invalid, the function terminates the script and produces an error.
This function should be called at the beginning of endpoints that require
a user to be logged in.
*/
function authenticateUser() {
    global $PassSaltConstant, $DB_HOST, $DB_NAME, $USER_SELECT, $PASS_SELECT, $userTable;

    if( !isset($_COOKIE["user"]) || !isset($_COOKIE["pass"]) ){
    die('{"error":"Authenitcation Error: You need to login first."}');
    }
    
    $user = strtoupper($_COOKIE["user"]);
    $pass = sha1($_COOKIE["pass"].$user.$PassSaltConstant);
    
    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

        $statement = $connection->prepare("SELECT * FROM ".$userTable." WHERE Username = :user AND Password = :pass");
        $statement->bindParam(':user', $user);
        $statement->bindParam(':pass', $pass);
        
        $statement->execute();
        
        if(!$statement->fetch()){
            die('{"error":"Authenitcation Error: Please logout and login again."}');
        }
        $connection = null;
    } catch(PDOException $e) {
        error_log($e->getMessage());
        die();
    }
}

/*function validateDate($date, $format = 'm/d/Y')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}*/

function validateDate($date){
    $testDate = explode('/', $date);
    if(count($testDate) == 3){
        if(checkdate($testDate[0], $testDate[1], $testDate[2])){
            return true;
        }
    }
    return false;
}

?>
