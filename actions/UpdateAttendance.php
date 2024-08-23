<?php

	include 'common.php';

	function update($data) {
		$mysqli = openDB_Connection();
		try {
		    $mysqli->begin_transaction();
	        foreach ($data as $id => $dates_attended) {
	            $sql = "UPDATE Enrollments SET attendance = JSON_OBJECT('days_attended', '".json_encode($dates_attended)."') WHERE enrollment_id=".$id;
                $result = $mysqli->query($sql);
            }
            $mysqli->commit();
		} catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            throw $exception;
        }
		$mysqli->close();
	}


?>