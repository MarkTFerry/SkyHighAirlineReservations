<?php
/*
Description: This endpoint inserts new flight into the bookedflights table
Method: Post
Input: date, adults, children, infants, rateID
Output: An object with attribute error is returned when a user can't book a flight.
The BookingID is returned when a user succesfully books a flight.
The endpoint always returns the rateObject if the rateID is valid.
*/

include 'common.php';
authenticateUser();

if( !isset($_POST["date"]) || !isset($_POST["adults"]) || !isset($_POST["children"])
    || !isset($_POST["infants"]) || !isset($_POST["rateID"]) ){
    die('{"error":"One or more parameters is missing."}');
}

$user = strtoupper($_COOKIE["user"]);
$date = $_POST["date"];
$adults = intval($_POST["adults"]);
$children = intval($_POST["children"]);
$infants = intval($_POST["infants"]);
$rateID = intval($_POST["rateID"]);

try {
    // Parameters are defined in common.php
    $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);
    
    $statement = $connection->prepare("SELECT * FROM rates WHERE ID = :RateID");                                       
    $statement->bindValue(':RateID', $rateID);
    $statement->execute();
    $rateObject = $statement->fetch();
    
    if(!$rateObject){
        die('{"error":"The rateID refers to a flight that does not exist."}');
    }
    
    $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_INSERT, $PASS_INSERT);
    
    $statement = $connection->prepare("INSERT INTO bookedflights (BookingID,Username,Date,Adults,Children,Infants,RateID,hasReceipt) 
                                       VALUES (NULL,:User,:Date,:Adults,:Children,:Infants,:RateID,:hasReciept)");
                                       
    $statement->bindValue(':User', $user);
    $statement->bindValue(':Date', $date);
    $statement->bindValue(':Adults', $adults);
    $statement->bindValue(':Children', $children);
    $statement->bindValue(':Infants', $infants);
    $statement->bindValue(':RateID', $rateID);
    $statement->bindValue(':hasReciept', 0);

    $statement->execute();
    
    $response = new stdClass();
    $response->rate = $rateObject;
    
    if($statement->rowCount() > 0){
        $response->BookingID = $connection->lastInsertId();
    } else {
        $response->error = "The flight could not be booked.";
    }
    
    // Close the connection
    $connection = null;  
    echo json_encode($response);

} catch(PDOException $e) {
    error_log($e->getMessage());
}

?>
