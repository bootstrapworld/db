<?php

	include 'common.php';

	function update($data) {
		$mysqli = openDB_Connection();
		try {
		    $mysqli->begin_transaction();
	        foreach ($data as $id => $dates_attended) {
	            
	            // the last elt is always the implementation status
	            // remove it so we can use it later
	            $status = array_pop($dates_attended);

	            $sql = "UPDATE Enrollments SET attendance = JSON_OBJECT('days_attended', '".json_encode($dates_attended)."') WHERE enrollment_id=".$id;
                $result = $mysqli->query($sql);

                $status = quoteOrNull($status);
                $sql = "UPDATE Enrollments SET implemented=".$status."
                        WHERE enrollment_id =  (
                            SELECT MIN(TR_id) AS enrollment_id FROM Enrollments AS E
                            LEFT JOIN (SELECT enrollment_id AS TR_id, person_id, Ev.type FROM Enrollments AS R, Events AS Ev WHERE R.event_id = Ev.event_id) AS TR
                            ON TR.person_id = E.person_id
                            AND TR.type='Training'
                            WHERE E.enrollment_id = ".$id."
                            GROUP BY E.person_id
                        );";
                echo $sql."<p>";
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