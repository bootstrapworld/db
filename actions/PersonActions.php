<?php

	include 'common.php';

	function update($data) {
		$mysqli = openDB_Connection();

    // create comma-delimited strings for columns, values, and col=val pairs
    if(array_key_exists('school_id', $data)) {
        $data['employer_id'] = $data['school_id'];
        unset($data['school_id']);
    }
    $columns = implode(", ", array_keys($data));
    $values = implode(", ", array_map('quoteOrNull', array_values($data)));

    $updateFields = implode(", ", array_map(
	    function($column,$value) { return $column."=".$value; }, 
	    array_keys($data), array_map('quoteOrNull', array_values($data))
    ));

    // insert these strings into a query
		$sql = "INSERT INTO People ($columns)
						VALUES ($values) 
						ON DUPLICATE KEY UPDATE 
						$updateFields;";
		
		$result = $mysqli->query($sql);

		//give back the person_id if one exists, otherwise the id of whatever was inserted
		if($result){
			echo $data['person_id']? $data['person_id'] : $mysqli->insert_id;
		} else {
			echo "ERROR: Hush! Sorry $sql. ". $mysqli->error;
		}
		$mysqli -> close();
	}

	function delete($person_id) {
		$mysqli = openDB_Connection();

    $values = implode(", ", array_map('quoteOrNull', array_values($person_id)));

		$sql = "DELETE FROM People WHERE person_id=$values;";
		$result = $mysqli->query($sql);
		if($result){
			echo $values;
		} else {
			echo "ERROR: Hush! Sorry $sql. ". $mysqli->error;
		}
		$mysqli -> close();
	}

	function searchForNames() {
		$mysqli = openDB_Connection();

    $pattern = "'".trim($_REQUEST['search'])."%'";

		$sql = "SELECT 
		    person_id AS id, 
		    CONCAT(name_first, ' ', name_last) AS value, 
		    IFNULL(CONCAT(email_preferred, ', ', city, ' ', state), 'no other information available') AS info 
		FROM People 
		WHERE name_last LIKE $pattern";
		$result = $mysqli->query($sql);
		if($result){
			while($row = $result->fetch_assoc()) { $myArray[] = $row; }
      echo json_encode($myArray);
		} else {
			echo "ERROR: Hush! Sorry $sql. ". $mysqli->error;
		}
		$mysqli -> close();
	}

?>