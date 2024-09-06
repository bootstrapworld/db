<?php
	include 'common.php';

    $data = json_decode(file_get_contents('php://input'), true);

	$mysqli = openDB_Connection();

	$sql = "INSERT INTO Submissions (instrument_id, instructor_code, form_data) 
	        VALUES (".$data['instrument_id'].", ".quoteOrNull($data['quizHash']).", ".quoteOrNull(json_encode($data['answers'])).")";
	$result = $mysqli->query($sql);
	if(!$result){
		echo "ERROR: Hush! Sorry $sql. ". $mysqli->error;
	} else {
	    echo "success";
	}
	$mysqli -> close();

?>