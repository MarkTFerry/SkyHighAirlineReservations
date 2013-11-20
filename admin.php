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
    return preg_replace("^[^-A-Za-z0-9_\s\.]*$","",$name);
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
    authenticateAdmin($request);
    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_SELECT, $PASS_SELECT);

        if($request->action == "viewAdmin"){
            $statement = $connection->prepare("SELECT * FROM admins");
        } else if($request->action == "viewUser"){
            $statement = $connection->prepare("SELECT * FROM users");
        } else {
            $statement = $connection->prepare("SELECT * FROM rates");
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

        //$statement = $connection->prepare("INSERT INTO rates (ID,Airline,Type,Class,From,To,Price,Time) VALUES (NULL,:Airline,:Type,:Class,:From,:To,:Price,:Time)");
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
    authenticateAdmin($request);
    
    if(!isset($request->idNum)){
        die($missingParametersError);
    }
    
    try {
        $connection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $USER_DELETE, $PASS_DELETE);
       
        $statement = $connection->prepare("DELETE FROM rates WHERE ID = :idNum");
        $statement->bindValue(':idNum', $request->idNum, PDO::PARAM_INT);
        $statement->execute();
        
        if($statement->rowCount() > 0){
            $response->success = 1;
        } else {
            $response->error = "An rate with that ID does not exist.";
        }
        
        // Close the connection
        $connection = null;
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
                                          `BookingID` int(11) NOT NULL,
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
                                          `Airline` varchar(50) NOT NULL,
                                          `ID` int(11) NOT NULL AUTO_INCREMENT,
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