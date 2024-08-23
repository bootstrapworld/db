<?php

	include 'common.php';

	function update($data) { genericInsertOrUpdate("Events", $data); }
	function delete($data) { genericDelete("Events", 'event_id', $data); 
	    $mysqli = openDB_Connection();
		try {
		    $mysqli->begin_transaction();
		    // delete all the enrollments for this event
	        $sql = "DELETE FROM Enrollments WHERE event_id = ".$data['event_id'];
	        $result = $mysqli->query($sql);
	        // delete the event itself
	        $sql = "DELETE FROM Events WHERE event_id = ".$data['event_id'];    
	        $result = $mysqli->query($sql);
		    $mysqli->commit();
		} catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            throw $exception;
        }
		$mysqli->close();
	}

	function searchForNames() {
		$mysqli = openDB_Connection();

		$pattern = "'%".$_REQUEST['search']."%'";

		// insert these strings into a query
		$sql = "SELECT event_id AS id, title AS value, location AS info FROM Events WHERE title LIKE $pattern";
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