<?php
/*
Description: This endpoint checks if a user exists in the user table with a given username and password
Method: Post
Input: user (username) and pass (password)
Output: An object with attribute error is returned when a user can't login.
An object with attribute success is returned when a user supplies valid credentials.
Additionally, the endpoint will create a cookie with the users credentials after a successful login.
*/

include 'common.php';

if( !isset($_POST["user"]) || !isset($_POST["pass"]) ){
    die('{"error":"Username or password is blank."}');
}

$user = strtoupper($_POST["user"]);
$passUnhashed = $_POST["pass"];
$pass = sha1($_POST["pass"].$user.$PassSaltConstant);

try {
    // Parameters are defined in common.php
    $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

    $statement = $connection->prepare("SELECT * FROM ".$userTable." WHERE Username = :user AND Password = :pass");
    $statement->bindParam(':user', $user);
    $statement->bindParam(':pass', $pass);
    
    $statement->execute();
    
    if($statement->fetch()){
        setcookie('user',$user);
        setcookie('pass',$passUnhashed);
        echo '{"success":1}';
    } else {
        echo '{"error":"The username or password you entered is incorrect. Please try again."}';
    }
    
    // Close the connection
    $connection = null;
} catch(PDOException $e) {
    error_log($e->getMessage());
}

?>
