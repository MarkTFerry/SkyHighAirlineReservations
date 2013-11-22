<?php
/*
Description: This endpoint is used to retrieve all the flights that a user booked.
Method: Get
Input: None
Output: An array containing an object for each flight booking.
Each object has the following attributes: BookingID, Username, Date, Adults, Children, Infants, RateID, hasReceipt
*/

include 'common.php';
authenticateUser();

$user = strtoupper($_COOKIE["user"]);

try {
    // Parameters are defined in common.php
    $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

    $statement = $connection->prepare("SELECT * FROM ".$bookedFlightsTable." WHERE Username = :user");
    $statement->bindValue(':user', $user);
    
    $statement->execute();
    $result = $statement->fetchAll();
    echo json_encode($result);
    
    // Close the connection
    $connection = null;
} catch(PDOException $e) {
    error_log($e->getMessage());
}

?>
