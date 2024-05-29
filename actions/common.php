<?php

$method = $_REQUEST["method"];
$data = json_decode($_REQUEST["data"], true);

if($method == "updateOrganization") { updateOrganization($data); }
if($method == "deleteEvent") { deleteOrganization($data); }
if($method == "searchForNames") { searchForNames($data); }
  
function quoteOrNull($value) {
	if (trim($value) === '') return 'NULL';
	else return "'".$value."'";
}

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

?>