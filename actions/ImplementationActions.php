<?php

	include 'common.php';
	if($method == "duplicateImplementation") { duplicateImplementation($data); }

	function delete($implementation_id) { genericDelete("Implementations", 'implementation_id', $implementation_id); }

    function update($data) {
    	$mysqli = openDB_Connection();
		$columns = array_keys($data);
		$values = array_values($data);
		
		for( $i = 0; $i < count($values); $i++ ){
		    if ($columns[$i] == "demographics_json") $values[$i] = "'".json_encode($data['demographics_json'])."'";
            else $values[$i] = quoteOrNull(mysqli_real_escape_string( $mysqli, $values[$i] ));
        }
        

		$updateFields = implode(", ", array_map(
			function($column,$value) { return $column."=".$value; }, 
			$columns, $values
		));

        $sql = "INSERT INTO Implementations (".implode(', ', $columns).")
				VALUES (".implode(', ', $values).") 
				ON DUPLICATE KEY UPDATE $updateFields";

		$result = $mysqli->query($sql);
		if($result){
		    $id = $mysqli->insert_id;
		    // attempts to update a record with identical data result in NO INSERT, so we need
		    // to detect this and return the original id instead (quotes removed)
			echo $id? $id : $values[0];
		} else {
			echo "ERROR: Sorry $sql. ". $mysqli->error;
		}
		$mysqli->close();
    }


	function duplicateImplementation($data) {
		$mysqli = openDB_Connection();
		try {
		    $mysqli->begin_transaction();
		    $sql  =  "INSERT INTO Implementations (
		                implementation_id, 
		                person_id, 
		                course_name, 
		                subject, 
		                grade_level, 
		                computer_access, 
		                start, 
		                curriculum, 
		                model, 
		                module_theme, 
		                when_teaching, 
		                dataset_selection, 
		                lesson_list, 
		                num_students, 
		                demographics_json, 
		                exams, 
		                standards, 
		                status, 
		                parent_impl_id
		              )
		              SELECT 
	                    NULL AS implementation_id, 
                        person_id,
                        course_name,
                        subject,
                        grade_level,
                        computer_access,
                        start,
                        curriculum,
                        model,
                        module_theme,
                        when_teaching,
                        dataset_selection,
                        lesson_list,
                        num_students,
                        demographics_json,
                        exams,
                        standards,
                        'Unknown' AS status,
                        ".$data['implementation_id']." AS parent_id
                    FROM Implementations WHERE implementation_id=".$data['implementation_id'];

            $result = $mysqli->query($sql);
            $new_implementation_id;
            if($result) {
			    $new_implementation_id = $mysqli->insert_id;
            } else {
                echo "ERROR when duplicating the implementation: Sorry $sql. ". $mysqli->error;
            }
            $mysqli->commit();
		} catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            throw $exception;
        }
		$mysqli->close();
		echo $new_implementation_id;
	}

?>