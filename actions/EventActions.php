<?php

	include 'common.php';
	if($method == "duplicateEvent") { duplicateEvent($data); }

	function update($data) { genericInsertOrUpdate("Events", $data); }
	
	function delete($data) { 
	    // delete the event using genericDelete
	    genericDelete("Events", 'event_id', $data); 
	    
	    //delete all the associated enrollments
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
	
	function duplicateEvent($data) {
		$mysqli = openDB_Connection();
		try {
		    $mysqli->begin_transaction();
		    $sql  =  "INSERT INTO Events (event_id, type, title, webpage_url, location, start, end, price,org_id, curriculum) 
	                            SELECT NULL AS event_id, type, title, webpage_url, location, start, end, price,org_id, curriculum 
	                            FROM Events WHERE event_id=".$data['event_id'];

            $result = $mysqli->query($sql);
            $new_event_id;
            if($result) {
			    $new_event_id = $mysqli->insert_id;
            } else {
                echo "ERROR when duplicating the event: Sorry $sql. ". $mysqli->error;
            }

		    $sql = "SELECT * FROM Enrollments WHERE event_id=".$data['event_id'];
		    $enrollments = $mysqli->query($sql);
		     while($row = mysqli_fetch_assoc($enrollments)) { 
	            $sql = "INSERT INTO Enrollments (person_id, event_id, type) 
	                        SELECT person_id, ".$new_event_id." AS event_id, type FROM Enrollments
	                    WHERE enrollment_id=".$row['enrollment_id'];
                $result = $mysqli->query($sql);
                if($result) {
			        
                } else {
                    echo "ERROR when duplicating an enrollment for this event: Sorry $sql. ". $mysqli->error;
                }
            }
            $mysqli->commit();
		} catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            throw $exception;
        }
		$mysqli->close();
		echo $new_event_id;
	}

?>