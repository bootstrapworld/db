<?php

	include 'common.php';

	function update($data) {
		$mysqli = openDB_Connection();

    // create comma-delimited strings for columns, values, and col=val pairs
    $columns = implode(", ", array_keys($data));
    $values = implode(", ", array_map('quoteOrNull', array_values($data)));
    $updateFields = implode(", ", array_map(
	    function($column,$value) { return $column."=".$value; }, 
	    array_keys($data), array_map('quoteOrNull', array_values($data))
    ));

    // insert these strings into a query
		$sql = "INSERT INTO Events ($columns)
						VALUES ($values) 
						ON DUPLICATE KEY UPDATE 
						$updateFields;";
             
		
		$result = $mysqli->query($sql);
		if($result){
			echo $mysqli->insert_id;
		} else {
			echo "ERROR: Sorry $sql. ". $mysqli->error;
		}
		$mysqli -> close();
	}

	function delete($event_id) {
		$mysqli = openDB_Connection();

    $values = implode(", ", array_map('quoteOrNull', array_values($event_id)));

		$sql = "DELETE FROM Events WHERE event_id=$values;";
		$result = $mysqli->query($sql);
		if($result){
			echo $values;
		} else {
			echo "ERROR: Sorry $sql. ". $mysqli->error;
		}
		$mysqli -> close();
	}

	function searchForNames() {
		$mysqli = openDB_Connection();

    $pattern = "'".$_REQUEST['search']."%'";

    // insert these strings into a query
		$sql = "SELECT * FROM Events WHERE title LIKE $pattern";
		$result = $mysqli->query($sql);
		if($result){
			while($row = $result->fetch_assoc()) { $myArray[] = $row; }
      echo json_encode($myArray);
		} else {
			echo "ERROR: Sorry $sql. ". $mysqli->error;
		}
		$mysqli -> close();
	}

?>