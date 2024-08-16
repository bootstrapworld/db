<?php

	include 'common.php';

	function update($data) { genericInsertOrUpdate("Organizations", $data); }
    function delete($org_id) { genericDelete("Organizations", 'org_id', $org_id); }
    
	function searchForNames() {
		$mysqli = openDB_Connection();

		$pattern = "'%".$_REQUEST['search']."%'";

		// Search for org names that match 
		$sql = "SELECT 
			org_id AS id, 
			name AS value,
			IFNULL(CONCAT(website, ' ', city, ' ', state), 'no other information available') AS info
		FROM Organizations 
		WHERE name LIKE $pattern";
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