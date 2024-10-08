<?php

	include 'common.php';
	if($method == "duplicateImplementation") { duplicateImplementation($data); }

	function delete($implementation_id) { genericDelete("Implementations", 'implementation_id', $implementation_id); }

    function update($data) {
    	$mysqli = openDB_Connection();
		$columns = array_keys($data);
		$values = array_values($data);
        
		$updateFields = implode(", ", array_map(
			function($column,$value) { return $column."=".$value; }, 
			$columns, $values
		));

		$sql = "UPDATE Implementations SET
		            course_name         = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['course_name'])).",
		            subject             = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['subject'])).",
		            grade_level         = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['grade_level'])).",
		            computer_access     = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['computer_access'])).",
		            start               = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['start'])).",
		            curriculum          = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['curriculum'])).",
		            model               = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['model'])).",
		            module_theme        = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['module_theme'])).",
		            when_teaching       = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['when_teaching'])).",
		            dataset_selection   = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['dataset_selection'])).",
		            lesson_list         = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['lesson_list'])).",
		            num_students        = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['num_students'])).",
		            demographics_json   = '".json_encode($data['demographics_json'])."',
		            exams               = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['exams'])).",
		            standards           = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['standards'])).", 
		            status              = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['status'])).", 
		            parent_impl_id      = ".quoteOrNull(mysqli_real_escape_string( $mysqli, $data['parent_impl_id']))."
		        WHERE implementation_id=".$data['implementation_id'];

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
                        CONCAT('(Copy) ', course_name) AS course_name,
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