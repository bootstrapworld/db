<?php

$personFields = array(
	'person_id',
	'name_first',
	'name_last',
	'email_preferred',
	'email_professional',
	'email_google',
	'role',
	'employer_id',
	'home_phone',
	'work_phone',
	'cell_phone',
	'home_address',
	'city',
	'state',
	'zip',
	'grades_taught',
	'primary_subject',
	'subscriber',
	'prior_years_coding',
	'race',
	'other_credentials'
);


$registrationFields = array(
	'enrollment_id',
	'person_id',
	'event_id',
	'created',
	'attendance',
	'type'
);

$method = $_REQUEST["method"];
$data = json_decode($_REQUEST["data"], true);

if($method == "update") { update($data); }
if($method == "delete") { delete($data); }
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

function genericInsertOrUpdate($table, $data) {
		$mysqli = openDB_Connection();
		$columns = array_keys($data);
		$values = array_values($data);
		for( $i = 0; $i < count($values); $i++ ){
            $values[$i] = quoteOrNull(mysqli_real_escape_string( $mysqli, $values[$i] ));
        }
		$updateFields = implode(", ", array_map(
			function($column,$value) { return $column."=".$value; }, 
			$columns, $values
		));

		$sql = "INSERT INTO $table (".implode(', ', $columns).")
				VALUES (".implode(', ', $values).") 
				ON DUPLICATE KEY UPDATE $updateFields";


		$result = $mysqli->query($sql);
		if($result){
			echo $mysqli->insert_id;
		} else {
			echo "ERROR: Sorry $sql. ". $mysqli->error;
		}
		$mysqli->close();
}

function genericDelete($table, $column, $id) {
	$mysqli = openDB_Connection();

	$values = implode(", ", array_map('quoteOrNull', array_values($id)));

	$sql = "DELETE FROM $table WHERE $column=$values;";
	$result = $mysqli->query($sql);
	if($result){
		echo $values;
	} else {
		echo "ERROR: Sorry $sql. ". $mysqli->error;
	}
	$mysqli -> close();
}
	
	
function createUpdateFields($data) {
		$columns = implode(", ", array_keys($data));
		$values = implode(", ", array_map('quoteOrNull', array_values($data)));

		$updateFields = implode(", ", array_map(
			function($column,$value) { return $column."=".$value; }, 
			array_keys($data), array_map('quoteOrNull', array_values($data))
		));
		return ["columns" => $columns, "values" => $values, "updateFields" => $updateFields];
}

?>