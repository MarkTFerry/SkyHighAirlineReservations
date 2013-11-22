<?php
include 'common.php';

$USER_ADMIN = 'SkyHighWebAppAdmin';
$PASS_ADMIN = 'dwfmwG2zrWP4twk8';

$AdminSaltConstant = "AdminPortalPassword";

$missingParametersError = '{"error":"Your request is missing one or more parameters."}';
$SQLerror = '{"error":"SQL statement execution failed. View the error log for more details."}';

function authenticateAdmin($request){
    global $PassSaltConstant, $AdminSaltConstant, $DB_HOST, $DB_NAME, $USER_SELECT, $PASS_SELECT;
    $userUpper = strtoupper($request->adminUser);
    $passwordHashed = sha1($request->adminPass.$userUpper.$PassSaltConstant.$AdminSaltConstant);
    try {
        // Parameters are defined in common.php
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

        $statement = $connection->prepare("SELECT * FROM admins WHERE Username = :user AND Password = :pass");
        $statement->bindValue(':user', $userUpper);
        $statement->bindValue(':pass', $passwordHashed);
        
        $statement->execute();
        
        if(!$statement->fetch()){
            die('{"error":"The username or password you entered is incorrect. Please try again."}');
        }
        
        // Close the connection
        $connection = null;
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
}

function cleanFilename($name){
    return preg_replace('/[^A-Za-z0-9 _ .-]/', '', $name);
}

if(!isset($_POST["request"])){
    die('{"error":"Request string missing or empty."}');
}

$request = json_decode($_POST['request']);
if(!$request){
    die('{"error":"The request could not be parsed."}');
}

if( !isset($request->action) || !isset($request->adminUser) || !isset($request->adminPass) ){
    die($missingParametersError);
}

$response = new stdClass();

switch ($request->action)
{
case "adminLogin":
    authenticateAdmin($request);
    $response->success = 1;
    break;
case "addAdmin":
case "addUser":
    authenticateAdmin($request);
    
    if( !isset($request->user) || !isset($request->pass) ){
        die($missingParametersError);
    }
    
    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_INSERT, $PASS_INSERT);

        $userUpper = strtoupper($request->user);
        if($request->action == "addAdmin"){
            $statement = $connection->prepare("INSERT INTO admins (Username,Password) VALUES (:user,:pass)");
            $passHashed = sha1($request->pass.$userUpper.$PassSaltConstant.$AdminSaltConstant);
        } else {
            $statement = $connection->prepare("INSERT INTO users (Username,Password) VALUES (:user,:pass)");
            $passHashed = sha1($request->pass.$userUpper.$PassSaltConstant);
        }
        
        $statement->bindValue(":user", $userUpper);
        $statement->bindValue(":pass", $passHashed);
        $statement->execute();
        
        if($statement->rowCount() > 0){
            $response->success = 1;
        } else {
            $response->error = "An account with that name already exists.";
        }
        
        // Close the connection
        $connection = null;
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
    break;
case "viewAdmin":
case "viewUser":
case "viewRate":
case "viewBooking":
    authenticateAdmin($request);
    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

        if($request->action == "viewAdmin"){
            $statement = $connection->prepare("SELECT * FROM admins");
        } else if($request->action == "viewUser"){
            $statement = $connection->prepare("SELECT * FROM users");
        } else if($request->action == "viewRate"){
            $statement = $connection->prepare("SELECT * FROM rates");
        } else {
            $statement = $connection->prepare("SELECT * FROM bookedflights");
        }
        
        $statement->execute();
        $result = $statement->fetchAll();
        $response->result = json_encode($result);
        
        // Close the connection
        $connection = null;
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
    break;
case "deleteAdmin":
case "deleteUser":
    authenticateAdmin($request);
    
    if(!isset($request->user)){
        die($missingParametersError);
    }
    
    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_DELETE, $PASS_DELETE);

        $upperUser = strtoupper($request->user);
        
        if($request->action == "deleteAdmin"){
            $statement = $connection->prepare("DELETE FROM admins WHERE Username = :user");
        } else {
            $statement = $connection->prepare("DELETE FROM users WHERE Username = :user");
        }        
        
        $statement->bindValue(':user', $upperUser);
        $statement->execute();
        
        if($statement->rowCount() > 0){
            $response->success = 1;
        } else {
            $response->error = "An account with that name does not exist.";
        }
        
        // Close the connection
        $connection = null;
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
    break;
case "addRate":
    authenticateAdmin($request);
    
    if( !isset($request->airline) || !isset($request->type) || !isset($request->classVal) || !isset($request->from)
        || !isset($request->to)|| !isset($request->price)|| !isset($request->timeVal)){
        die($missingParametersError);
    }
    
    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_INSERT, $PASS_INSERT);

        $statement = $connection->prepare("INSERT INTO rates (`ID`,`Airline`,`Type`,`Class`,`From`,`To`,`Price`,`Time`) 
                                           VALUES (NULL,:Airline,:Type,:Class,:From,:To,:Price,:Time)");
        $statement->bindValue(":Airline", $request->airline);
        $statement->bindValue(":Type", $request->type, PDO::PARAM_INT);
        $statement->bindValue(":Class", $request->classVal, PDO::PARAM_INT);
        $statement->bindValue(":From", $request->from);
        $statement->bindValue(":To", $request->to);
        $statement->bindValue(":Price", $request->price, PDO::PARAM_INT);
        $statement->bindValue(":Time", $request->timeVal);
        $statement->execute();
        
        if($statement->rowCount() > 0){
            $response->success = 1;
        } else {
            $response->error = "The rate could not be added.";
        }
        
        // Close the connection
        $connection = null;
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
    break;
case "deleteRate":
case "deleteBooking":
    authenticateAdmin($request);
    
    if(!isset($request->idNum)){
        die($missingParametersError);
    }
    
    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_DELETE, $PASS_DELETE);
       
        if($request->action == "deleteRate"){
            $statement = $connection->prepare("DELETE FROM rates WHERE ID = :idNum");
        } else {
            $statement = $connection->prepare("DELETE FROM bookedflights WHERE BookingID = :idNum");
        }
        $statement->bindValue(':idNum', $request->idNum, PDO::PARAM_INT);
        $statement->execute();
        
        if($statement->rowCount() > 0){
            $response->success = 1;
        } else {
            if($request->action == "deleteRate"){
                $response->error = "A rate with that ID does not exist.";
            } else {
                $response->error = "A booking with that ID does not exist.";
            }
        }
        
        // Close the connection
        $connection = null;
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
    break;
case "addLogo":
    authenticateAdmin($request);
    
    if(!isset($request->airline) || !isset($request->imageData)){
        die($missingParametersError);
    }
    
    $path = dirname(__FILE__).'/resources/logos/';
    $filename = cleanFilename( $request->airline.'.png' );
        
    // Remove header from data URI
    $imgData = substr( $request->imageData, strpos($request->imageData,",")+1);
    $imgResource = imagecreatefromstring( base64_decode($imgData) );
    
    if($imgResource === false){
        $response->error = 'Could not process image. Please try again or upload a different file.';
    } else if(is_file( $path.$filename )){
        $response->error = 'A logo for that airline already exists. Please delete the old logo first.';
    } else {
        imagepng($imgResource, $path.$filename);
        $response->success = 1;
    }
    break;
case "viewLogo":
    authenticateAdmin($request);
    
    $path = dirname(__FILE__).'/resources/logos/';
    $fileArray = scandir($path);
    
    for($i=2; $i<count($fileArray); $i++){
        $response->result[$i-2] = $fileArray[$i];
    }
    
    break;
case "deleteLogo":
    authenticateAdmin($request);
    
    if(!isset($request->airline)){
        die($missingParametersError);
    }
    
    $path = dirname(__FILE__).'/resources/logos/';
    $filename = cleanFilename( $request->airline.'.png' );
    
    if(is_file($path.$filename)){
        unlink($path.$filename);
        $response->success = 1;
    } else {
        $response->error = 'The logo could not be deleted.';
    }
    
    break;
case "addBooking":
    authenticateAdmin($request);
    
    if( !isset($request->user) || !isset($request->dateVal) || !isset($request->adults) ||
        !isset($request->children) || !isset($request->infants) || !isset($request->rateID) ){
        die($missingParametersError);
    }
    
    $userUpper = strtoupper($request->user);
    
    if(!validateDate($request->dateVal)){
        die('{"error":"The date entered is invalid."}');
    }
    
    $adults = intval($request->adults);
    $children = intval($request->children);
    $infants = intval($request->infants);
    $rateID = intval($request->rateID);
    
    try {
        // Parameters are defined in common.php
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

        $statement = $connection->prepare("SELECT * FROM users WHERE Username = :user");
        $statement->bindValue(':user', $userUpper);
        
        $statement->execute();
        
        if(!$statement->fetch()){
            die('{"error":"An account with that username does not exist."}');
        }
        
        $statement = $connection->prepare("SELECT * FROM rates WHERE ID = :rateID");
        $statement->bindValue(':rateID', $rateID);
        
        $statement->execute();
        
        if(!$statement->fetch()){
            die('{"error":"A rate with that ID does not exist."}');
        }
        
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_INSERT, $PASS_INSERT);
        
        $statement = $connection->prepare("INSERT INTO bookedflights (BookingID,Username,Date,Adults,Children,Infants,RateID,hasReceipt) 
                                   VALUES (NULL,:User,:Date,:Adults,:Children,:Infants,:RateID,:hasReciept)");
                                   
        $statement->bindValue(':User', $userUpper);
        $statement->bindValue(':Date', $request->dateVal);
        $statement->bindValue(':Adults', $adults);
        $statement->bindValue(':Children', $children);
        $statement->bindValue(':Infants', $infants);
        $statement->bindValue(':RateID', $rateID);
        $statement->bindValue(':hasReciept', 0);

        $statement->execute();
        
        if($statement->rowCount() > 0){
            $response->success = 1;
        } else {
            $response->error = "The flight could not be booked.";
        }
        
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
    
    break;
case "setupServer":
    $connection = mysql_connect($DB_HOST, $request->adminUser, $request->adminPass);
    if (!$connection) {
        die('{"error":"Could not connect to MySQL server."}');
    }

    $db_selected = mysql_select_db($DB_NAME, $connection);

    if (!$db_selected) {
      $sql = 'CREATE DATABASE '.$DB_NAME;

      if (mysql_query($sql, $connection)) {
          $response->results = "Created skyhigh database";
      } else {
          die($SQLerror);
      }
    } else {
        $response->results = "Database skyhigh found";
    }
    
    mysql_close($connection);

    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $request->adminUser, $request->adminPass);

        $statement = $connection->prepare("CREATE TABLE IF NOT EXISTS `bookedflights` (
                                          `BookingID` int(11) NOT NULL AUTO_INCREMENT,
                                          `Username` varchar(100) NOT NULL,
                                          `Date` varchar(10) NOT NULL,
                                          `Adults` int(11) NOT NULL,
                                          `Children` int(11) NOT NULL,
                                          `Infants` int(11) NOT NULL,
                                          `RateID` int(11) NOT NULL,
                                          `hasReceipt` tinyint(1) NOT NULL,
                                          PRIMARY KEY (`BookingID`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $statement->execute();
        $response->results = $response->results."<br><br>Created table for booked flights";
        
        $statement = $connection->prepare("CREATE TABLE IF NOT EXISTS `rates` (
                                          `ID` int(11) NOT NULL AUTO_INCREMENT,
                                          `Airline` varchar(50) NOT NULL,
                                          `Type` int(11) NOT NULL,
                                          `Class` int(11) NOT NULL,
                                          `From` varchar(50) NOT NULL,
                                          `To` varchar(50) NOT NULL,
                                          `Price` int(11) NOT NULL,
                                          `Time` time NOT NULL,
                                          PRIMARY KEY (`ID`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $statement->execute();
        $response->results = $response->results."<br><br>Created table for rates";
        
        $statement = $connection->prepare("CREATE TABLE IF NOT EXISTS `users` (
                                          `Username` varchar(100) NOT NULL,
                                          `Password` varchar(40) NOT NULL,
                                          PRIMARY KEY (`Username`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $statement->execute();
        $response->results = $response->results."<br><br>Created table for users";
        
        $statement = $connection->prepare("CREATE TABLE IF NOT EXISTS `admins` (
                                          `Username` varchar(100) NOT NULL,
                                          `Password` varchar(40) NOT NULL,
                                          PRIMARY KEY (`Username`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $statement->execute();
        $response->results = $response->results."<br><br>Created table for admins";
        
        $defaultUser = "SKYHIGHADMIN";
        $defaultPass = "password";
        $defaultPassHashed = sha1($defaultPass.$defaultUser.$PassSaltConstant.$AdminSaltConstant);
        
        $statement = $connection->prepare("INSERT INTO admins (Username,Password) VALUES (:user,:pass)");
        $statement->bindParam(':user', $defaultUser);
        $statement->bindParam(':pass', $defaultPassHashed);
        $statement->execute();
        $response->results = $response->results."<br><br>Added default admin account to admins";
        
        $statement = $connection->prepare("GRANT SELECT ON *.* TO '".$USER_SELECT."'@'localhost' IDENTIFIED BY '".$PASS_SELECT."';");
        $statement->execute();
        $response->results = $response->results."<br><br>Added user for SELECT";
        
        $statement = $connection->prepare("GRANT SELECT, INSERT ON *.* TO '".$USER_INSERT."'@'localhost' IDENTIFIED BY '".$PASS_INSERT."';");
        $statement->execute();
        $response->results = $response->results."<br><br>Added user for INSERT";
        
        $statement = $connection->prepare("GRANT SELECT, DELETE ON *.* TO '".$USER_DELETE."'@'localhost' IDENTIFIED BY '".$PASS_DELETE."';");
        $statement->execute();
        $response->results = $response->results."<br><br>Added user for DELETE";
        
        $connection = null;
    } catch(PDOException $e) {
        error_log($e->getMessage());
        die($SQLerror);
    }
    break;
default:
  die('{"error":"Invalid action."}');
}

echo json_encode($response);
?>