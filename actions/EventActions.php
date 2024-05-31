<?php

	include 'common.php';

	function update($data) { genericInsertOrUpdate("Events", $data); }
	function delete($data) { genericDelete("Events", 'event_id', $data); }

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