<?php

	include 'common.php';

	function update($data) { genericInsertOrUpdate("People", $data); }
    function delete($person_id) { genericDelete("People", 'person_id', $person_id); }

	function searchForNames() {
		$mysqli = openDB_Connection();

		$pattern = "'%".trim($_REQUEST['search'])."%'";

		$sql = "SELECT 
			person_id AS id, 
			CONCAT(name_first, ' ', name_last) AS value, 
			IFNULL(CONCAT(email_preferred, ', ', city, ' ', state), 'no other information available') AS info 
		FROM People 
		WHERE name_last LIKE $pattern OR name_first LIKE $pattern";
		$result = $mysqli->query($sql);
		if($result){
			while($row = $result->fetch_assoc()) { $myArray[] = $row; }
				echo json_encode($myArray);
		} else {
			echo "ERROR: Hush! Sorry $sql. ". $mysqli->error;
		}
		$mysqli -> close();
	}

?>