<?php

function openDB_Connection() {
	// servername, username, password, database name
	$mysqli = new mysqli("localhost", "u804343808_admin", "92AWe*MP", "u804343808_testingdb");

	// Check connection
	if ($mysqli -> connect_errno) {
		echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
		exit();
	}
	
	return $mysqli;
}

function quoteOrNull($value) {
	if (trim($value) === '') return 'NULL';
	else return "'".$value."'";
}

function writeToLog($data) {
	$mysqli = openDB_Connection();
	$columns = implode(", ", array_keys($data));
	$values = implode(", ", array_map('quoteOrNull', array_values($data)));
	
	$updateFields = implode(", ", array_map(
		function($column,$value) { return $column."=".$value; }, 
		array_keys($data), array_map('quoteOrNull', array_values($data))
	));
        try {
        //  $stmt = $mysqli->prepare("INSERT INTO Logs (data) VALUES (?)")
        }
        catch(Exception $e) {
          echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        //$stmt->bind_param("s", array_values($data)[0]);

        //$val = $mysqli->real_escape_string($data["data"])
        
	$sql = "INSERT INTO Logs ($columns)
			VALUES ($values) 
			ON DUPLICATE KEY UPDATE $updateFields";

	$result = $mysqli->query($sql);
	//$result = $stmt->execute();
	if($result){
		echo $mysqli->insert_id;
	} else {
		echo "ERROR: Sorry $sql. ". $mysqli->error;
	}
	$mysqli->close();
}

function pyretLog() {
	$mysqli = openDB_Connection();
        $stmt = $mysqli->prepare("INSERT INTO Logs (data) VALUES (?)");
        $data = file_get_contents('php://input');
        $data_json = json_decode($data, true);
        $data_json["ip"] = $_SERVER['REMOTE_ADDR'];
        $data = json_encode($data_json);
        $stmt->bind_param("s", $data);
        $result = $stmt->execute();
	if($result){
		echo $mysqli->insert_id;
	} else {
		echo "ERROR: Sorry $sql. ". $mysqli->error;
	}
	$mysqli->close();
}

function deleteAllRows() {
	$mysqli = openDB_Connection();
    $sql = "DELETE FROM Logs";
    $result = $mysqli->query($sql);
    if($result){
		echo "deleted!";
	} else {
		echo "ERROR: Sorry $sql. ". $mysqli->error;
	}
	$mysqli->close();
}

$method = $_REQUEST["method"];
$data = json_decode($_REQUEST["data"], true);

if($method == "update") { writeToLog($data); }
if($method == "pyretLog") { pyretLog(); }
if($method == "deleteAll") { deleteAllRows(); }

?>
