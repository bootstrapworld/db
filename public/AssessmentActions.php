<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

	include '../actions/common.php';

    $data = json_decode(file_get_contents('php://input'), true);
	$mysqli = openDB_Connection();
	
	$stmt = $mysqli->prepare("INSERT INTO Submissions (instructor_code, quiz_name, quiz_hash, quiz_path, quiz_questions, form_data) 
	        VALUES (?,?,?,?,?,?)");

echo $data['questions'];

$instructor = $data['instructor_code'];
$name       = $data['quizName'];
$hash       = $data['quizHash'];
$path       = $data['quizPath'];
$questions  = json_encode($data['questions']);
$answers    = json_encode($data['answers']);

$stmt->bind_param("isssss", $instructor, $name, $hash, $path, $questions, $answers);
$result = $stmt->execute();

	if(!$result){
		echo "ERROR: Sorry $sql. ". $mysqli->error;
	} else {
	    echo "success";
	}
	$mysqli -> close();

?>