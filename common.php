<?php

$DB_HOST = '127.0.0.1';
$DB_NAME = 'skyhigh';

$USER_SELECT = 'SkyHighWebAppS';
$PASS_SELECT = 'oNTYVidPlHydqKrM';

$USER_INSERT = 'SkyHighWebAppI';
$PASS_INSERT = 'Uh5kjk37TXhQ2bOm';

$USER_DELETE = 'SkyHighWebAppD';
$PASS_DELETE = '5RnpTTW3nNQpfGYH';

$PassSaltConstant = 'SkyHighPass';

/*
This function uses the cookie stored during login to check that the user
is using a valid account. If the cookies is not present or the account data
is invalid, the function terminates the script and produces an error.
This function should be called at the beginning of endpoints that require
a user to be logged in.
*/
function authenticateUser() {
    if( !isset($_COOKIE["user"]) || !isset($_COOKIE["pass"]) ){
    die('{"error":"Authenitcation Error: You need to login first."}');
    }
    
    $user = strtoupper($_COOKIE["user"]);
    $pass = sha1($_COOKIE["pass"].$user.$PassSaltConstant);
    
    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

        $statement = $connection->prepare("SELECT * FROM users WHERE Username = :user AND Password = :pass");
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

?>