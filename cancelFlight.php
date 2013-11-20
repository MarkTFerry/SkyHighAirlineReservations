<?php
/*
Description: This endpoint is used to delete a flight from the bookedflights table.
Method: Post
Input: ID (BookingID of the flight)
Output: Returns an object with atribute success or error depending on whether or not the flight was canceled.
*/

include 'common.php';
authenticateUser();

if(!isset($_POST["ID"])){
    die('{"error":"A booking to delete was not specified."}');
}

$user = strtoupper($_COOKIE["user"]);
$ID = intval($_POST["ID"]);

try {
    // Parameters are defined in common.php
    $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_DELETE, $PASS_DELETE);

    $statement = $connection->prepare("DELETE FROM bookedflights WHERE Username = :user and BookingID = :ID");
    $statement->bindValue(':user', $user);
    $statement->bindValue(':ID', $ID);
    
    $statement->execute();
    
    $response = new stdClass();
    if($statement->rowCount() > 0){
            $response->success = 1;
        } else {
            $response->error = "The flight could not be canceled.";
        }
    
    echo json_encode($response);
    
    // Close the connection
    $connection = null;
} catch(PDOException $e) {
    error_log($e->getMessage());
}

?>
