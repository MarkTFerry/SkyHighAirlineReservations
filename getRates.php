<?php
/*
Description: This endpoint is used for retrieving the rates of different flights.
It can also be used to check the availability of flights.
Method: Get
Input: Flight class (Domestic/International) and type (Economic/Business)
Output: An array containing an object for each flight.
Each object has the following attributes: ID, To, From, Price, Time, Class, Type
*/

include 'common.php';

if( !isset($_GET["all"]) && (!isset($_GET["class"]) || !isset($_GET["type"])) ){
    die('{"error":"Class and type both need to be selected."}');
}

try {
    // Parameters are defined in common.php
    $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

    if(isset($_GET["all"])){
        $statement = $connection->prepare("SELECT * FROM ".$rateTable);
    } else {
        $statement = $connection->prepare("SELECT * FROM ".$rateTable." WHERE Class = :class AND Type = :type");
        
        $class = intval($_GET["class"]);
        $type = intval($_GET["type"]);
        
        $statement->bindParam(':class', $class);
        $statement->bindParam(':type', $type);
    }
    
    $statement->execute();
    $result = $statement->fetchAll();
    echo json_encode($result);
    
    // Close the connection
    $connection = null;
} catch(PDOException $e) {
    error_log($e->getMessage());
}

?>
