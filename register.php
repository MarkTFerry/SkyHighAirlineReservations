<?php
/*
Description: This endpoint inserts new users into the users table
Method: Post
Input: user (username) and pass (password)
Output: An object with attribute error is returned when a user can't register.
An object with attribute success is returned when a user succesfully registers.
*/

include 'common.php';

if( !isset($_POST["user"]) || !isset($_POST["pass"]) ){
    die('{"error":"Username or password is blank."}');
}

$user = strtoupper($_POST["user"]);
$pass = sha1($_POST["pass"].$user.$PassSaltConstant);

try {
    // Parameters are defined in common.php
    $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

    
    $statement = $connection->prepare("SELECT * FROM ".$userTable." WHERE Username = :user");
    $statement->bindParam(':user', $user);
    
    $statement->execute();
    
    // If the statement returns a result, a user with the desired username already exists
    if($statement->fetch()){
        $connection = null;
        die('{"error":"The username you entered is already in use. Please enter a different username."}');
    }
    
    // Establish a new connection since $USER_SELECT doesn't have permission to insert rows
    $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_INSERT, $PASS_INSERT);
    
    $statement = $connection->prepare("INSERT INTO ".$userTable." (Username,Password) VALUES (:user,:pass)");
    $statement->bindParam(':user', $user);
    $statement->bindParam(':pass', $pass);

    $statement->execute();
    
    // Close the connection
    $connection = null;
    
    echo '{"success":1}';

} catch(PDOException $e) {
    error_log($e->getMessage());
}

?>
