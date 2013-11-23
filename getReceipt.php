<?php
/*
Description: This endpoint is used to view flight receipts
Method: Get
Input: ID (BookingID)
Output: An error is returned on failure.
The guid of the temporary PDF is returned on success.
*/

include 'common.php';
authenticateUser();

if(!isset($_POST["ID"])){
    die('{"error":"An ID was not specified."}');
}

ob_end_clean(); // Clean buffer
ignore_user_abort(true); // Keep running script after user disconnects
set_time_limit(0); // Let the script run forever
ob_start(); // Start buffering output

$user = strtoupper($_COOKIE["user"]);
$id = intval($_POST["ID"]);

$isBookingOwner = false;

try {
    // Parameters are defined in common.php
    $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

    $statement = $connection->prepare("SELECT * FROM ".$bookedFlightsTable." WHERE Username = :user and BookingID = :id and hasReceipt = :hasReceipt");
    $statement->bindValue(':user', $user);
    $statement->bindValue(':id', $id);
    $statement->bindValue(':hasReceipt', 1);
    
    $statement->execute();
    if($statement->fetch()){
        $isBookingOwner = true;
    }
    
    // Close the connection
    $connection = null;
} catch(PDOException $e) {
    error_log($e->getMessage());
}

if(!$isBookingOwner){
    die('{"error":"You do not have permission to view this receipt."}');
}

$resourceDir = dirname(__FILE__).'/resources/';
//$tempGUID = com_create_guid();
$tempGUID = substr(str_shuffle(MD5(microtime())), 0, 15);
$receiptFile = $resourceDir.'receipts/'.$id.'.pdf';
$tempFile = $resourceDir.'receiptCache/'.$tempGUID.'.pdf';

if (!copy($receiptFile, $tempFile)) {
    die('{"error":"Could not create temporary file."}');
}
// Make the temp file readable by the user
chmod($tempFile, 0644);

echo '{"GUID":"'.$tempGUID.'"}';

session_write_close(); // Close the session so other requests aren't blocked
header("Content-Encoding: none"); //send header to avoid the browser side to take content as gzip format
header("Content-Length: ".ob_get_length()); //send length header
header("Connection: close");
ob_end_flush(); // Flush ob buffer to normal buffer
flush(); // Flush normal buffer to output


// Wait for the user to load the temp PDF before timing out.
sleep(1.5*60);

if(is_file($tempFile)){
    unlink($tempFile);
} else {
    error_log('Could not find temp file: '.$tempFile);
}
?>
